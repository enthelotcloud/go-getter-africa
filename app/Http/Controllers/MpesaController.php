<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\MpesaService;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Vote;
use App\Models\Nomination;
use App\Models\TokenPackage;

class MpesaController extends Controller
{
    protected MpesaService $mpesa;

    // The percentage of a guest vote payment that goes to the nominee's wallet
    protected $commissionRate = 0.60; // 60%

    public function __construct(MpesaService $mpesa)
    {
        $this->mpesa = $mpesa;
    }

    /**
     * Helper to dynamically calculate the base cost of 1 vote
     * based on your lowest active Token Package.
     */
    protected function getBaseCostPerVote()
    {
        $package = TokenPackage::where('is_active', true)->orderBy('price_kes', 'asc')->first();
        // If a package is 50 KES for 5 tokens, rate is 10 KES per vote. Defaults to 10 if no package exists.
        return $package ? ($package->price_kes / max(1, $package->tokens)) : 10;
    }

    /**
     * 1. Logged-In User Buying Tokens (Uses explicit Package ID)
     */
    public function buyTokens(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'token_package_id' => 'required|exists:token_packages,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $package = TokenPackage::findOrFail($request->token_package_id);
        $reference = 'Tokens_' . $request->user_id;

        $response = $this->mpesa->stkPush($request->phone_number, $package->price_kes, $reference);

        if (isset($response['CheckoutRequestID'])) {
            Transaction::create([
                'user_id' => $request->user_id,
                'type' => 'stk_push',
                'phone_number' => $request->phone_number,
                'amount' => $package->price_kes,
                'tokens_bought' => $package->tokens,
                'merchant_request_id' => $response['MerchantRequestID'],
                'checkout_request_id' => $response['CheckoutRequestID'],
                'status' => 'pending'
            ]);

            return response()->json(['success' => true, 'message' => 'STK Push sent. Please check your phone.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to initiate STK Push.'], 500);
    }

    /**
     * 2. Guest User Voting Directly (Calculates from amount entered)
     */
    public function guestVote(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'nomination_id' => 'required|exists:nominations,id',
            'amount' => 'required|numeric|min:' . $this->getBaseCostPerVote()
        ]);

        $nomination = Nomination::findOrFail($request->nomination_id);
        $reference = 'Vote_' . $nomination->code;

        $response = $this->mpesa->stkPush($request->phone_number, $request->amount, $reference);

        if (isset($response['CheckoutRequestID'])) {
            Transaction::create([
                'type' => 'stk_push',
                'phone_number' => $request->phone_number,
                'amount' => $request->amount,
                'nomination_id' => $nomination->id,
                'merchant_request_id' => $response['MerchantRequestID'],
                'checkout_request_id' => $response['CheckoutRequestID'],
                'status' => 'pending'
            ]);

            return response()->json(['success' => true, 'message' => 'STK Push sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to initiate STK Push.'], 500);
    }

    /**
     * 3. Safaricom STK Webhook Callback
     */
    public function stkCallback(Request $request)
    {
        Log::info('STK Callback received:', $request->all());

        $data = $request->json('Body.stkCallback');
        $checkoutRequestID = $data['CheckoutRequestID'] ?? null;
        $resultCode = $data['ResultCode'] ?? 1;
        $resultDesc = $data['ResultDesc'] ?? 'Failed';

        if (!$checkoutRequestID) return response()->json(['status' => 'ignored']);

        $transaction = Transaction::where('checkout_request_id', $checkoutRequestID)->first();

        if (!$transaction || $transaction->status !== 'pending') {
            return response()->json(['status' => 'already processed or not found']);
        }

        if ($resultCode == 0) {
            $meta = collect($data['CallbackMetadata']['Item']);
            $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? 'UNKNOWN';

            DB::transaction(function () use ($transaction, $receipt, $resultDesc) {
                $transaction->update([
                    'status' => 'completed',
                    'receipt_number' => $receipt,
                    'result_desc' => $resultDesc
                ]);

                // Scenario A: Logged-In User Bought Tokens
                if ($transaction->user_id && $transaction->tokens_bought) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $transaction->user_id],
                        ['token_balance' => 0, 'kes_balance' => 0]
                    );

                    $wallet->increment('token_balance', $transaction->tokens_bought);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'deposit_tokens',
                        'amount' => $transaction->tokens_bought,
                        'currency' => 'TOKENS',
                        'description' => "Purchased via M-PESA Receipt: {$receipt}",
                        'transaction_id' => $transaction->id
                    ]);
                }

                // Scenario B: Guest User Voted Directly
                elseif ($transaction->nomination_id) {
                    $nomination = Nomination::with('category', 'user')->find($transaction->nomination_id);

                    // --- DYNAMIC PACKAGE MULTIPLIER ---
                    $costPerVote = $this->getBaseCostPerVote();
                    $votesEarned = max(1, floor($transaction->amount / $costPerVote));
                    $commission = $transaction->amount * $this->commissionRate;

                    // 1. Cast the Vote
                    Vote::create([
                        'guest_phone' => $transaction->phone_number,
                        'nomination_id' => $nomination->id,
                        'nomination_category_id' => $nomination->nomination_category_id,
                        'tokens_spent' => $votesEarned, // Saves dynamic amount
                        'commission_earned_kes' => $commission,
                        'transaction_id' => $transaction->id
                    ]);

                    // Increment public cache
                    $nomination->increment('total_votes', $votesEarned);

                    // 2. Pay the Nominee's Wallet
                    if ($nomination->user_id) {
                        $nomineeWallet = Wallet::firstOrCreate(
                            ['user_id' => $nomination->user_id],
                            ['token_balance' => 0, 'kes_balance' => 0]
                        );

                        $nomineeWallet->increment('kes_balance', $commission);

                        WalletTransaction::create([
                            'wallet_id' => $nomineeWallet->id,
                            'type' => 'commission_earned',
                            'amount' => $commission,
                            'currency' => 'KES',
                            'description' => "Guest vote commission ({$votesEarned} votes) from {$transaction->phone_number}",
                            'transaction_id' => $transaction->id
                        ]);
                    }
                }
            });
        } else {
            $transaction->update([
                'status' => 'failed',
                'result_desc' => $resultDesc
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function b2cResult(Request $request)
    {
        Log::info('B2C Result:', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function b2cTimeout(Request $request)
    {
        Log::info('B2C Timeout:', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
