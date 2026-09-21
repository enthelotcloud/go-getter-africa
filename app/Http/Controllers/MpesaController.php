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
     * 1. Logged-In User Buying Tokens
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

        // Trigger STK Push
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
     * 2. Guest User Voting Directly (No Tokens)
     */
    public function guestVote(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'nomination_id' => 'required|exists:nominations,id',
            'amount' => 'required|numeric|min:10' // Minimum KES per vote
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

            return response()->json(['success' => true, 'message' => 'STK Push sent. Please enter your PIN.']);
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

        // Find the pending transaction
        $transaction = Transaction::where('checkout_request_id', $checkoutRequestID)->first();

        // Prevent double processing if Daraja sends the callback twice
        if (!$transaction || $transaction->status !== 'pending') {
            return response()->json(['status' => 'already processed or not found']);
        }

        if ($resultCode == 0) {
            // Payment Successful
            $meta = collect($data['CallbackMetadata']['Item']);
            $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? 'UNKNOWN';

            // Wrap in DB transaction so everything succeeds or everything rolls back
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
                    $commission = $transaction->amount * $this->commissionRate;

                    // 1. Cast the Vote
                    Vote::create([
                        'guest_phone' => $transaction->phone_number,
                        'nomination_id' => $nomination->id,
                        'nomination_category_id' => $nomination->nomination_category_id,
                        'tokens_spent' => 0, // Direct KES vote
                        'commission_earned_kes' => $commission,
                        'transaction_id' => $transaction->id
                    ]);

                    // Increment public total cache
                    $nomination->increment('total_votes');

                    // 2. Pay the Nominee's Wallet (If the nominee has a registered User account)
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
                            'description' => "Guest vote commission from {$transaction->phone_number}",
                            'transaction_id' => $transaction->id
                        ]);
                    }
                }
            });
        } else {
            // Payment Failed (User cancelled, insufficient funds, etc.)
            $transaction->update([
                'status' => 'failed',
                'result_desc' => $resultDesc
            ]);
        }

        // Always return success to Safaricom so they stop retrying
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    // B2C Callbacks (For when you implement withdrawals)
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
