<?php

use App\Models\Nomination;
use App\Models\Transaction;
use App\Models\TokenPackage;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.guest.app')] class extends Component {

    public Nomination $nomination;
    public $phone_number = '';
    public $amount = 50;

    // Dynamic rate loaded from db
    public $costPerVote = 10;

    public $paymentStatus = 'idle';
    public $paymentMessage = '';
    public $transactionId = null;

    public function rules()
    {
        return [
            'phone_number' => 'required|string|min:10',
            'amount' => 'required|numeric|min:' . $this->costPerVote,
        ];
    }

    public function mount(Nomination $nomination)
    {
        $this->nomination = $nomination->load('category');

        // Find the base rate from active token packages
        $package = TokenPackage::where('is_active', true)->orderBy('price_kes', 'asc')->first();
        if ($package) {
            $this->costPerVote = $package->price_kes / max(1, $package->tokens);
        }

        // Ensure default amount is at least 1 vote
        if ($this->amount < $this->costPerVote) {
            $this->amount = $this->costPerVote;
        }
    }

    public function triggerMpesaVote(MpesaService $mpesa)
    {
        $this->validate();
        $this->paymentStatus = 'initiating';
        $this->paymentMessage = 'Sending STK Push...';

        $reference = 'VOTE_' . $this->nomination->code;

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, $reference);

            if (isset($response['CheckoutRequestID'])) {
                $txn = Transaction::create([
                    'type' => 'stk_push',
                    'phone_number' => $this->phone_number,
                    'amount' => $this->amount,
                    'nomination_id' => $this->nomination->id,
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'status' => 'pending'
                ]);

                $this->transactionId = $txn->id;
                $this->paymentStatus = 'waiting';
                $this->paymentMessage = 'Establishing payment... Please enter your PIN.';
            } else {
                $this->paymentStatus = 'failed';
                $this->paymentMessage = 'Safaricom Error: Check your M-PESA configuration.';
            }
        } catch (\Exception $e) {
            $this->paymentStatus = 'failed';
            $this->paymentMessage = 'System Error: ' . $e->getMessage();
        }
    }

    public function checkPaymentStatus()
    {
        if (!$this->transactionId || $this->paymentStatus !== 'waiting') return;

        $txn = Transaction::find($this->transactionId);

        if ($txn->status === 'completed') {
            $this->paymentStatus = 'success';
            $this->nomination->refresh();
        } elseif ($txn->status === 'failed') {
            $this->paymentStatus = 'failed';
            $this->paymentMessage = 'Transaction failed or was cancelled. ' . $txn->result_desc;
        }
    }

    public function resetVote()
    {
        $this->paymentStatus = 'idle';
        $this->transactionId = null;
    }

    public function with(): array
    {
        return [];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <a href="{{ route('polls.category', $nomination->category->slug) }}" class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-yellow-500 mb-6 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to {{ $nomination->category->name }}
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 shadow-2xl flex flex-col sm:flex-row items-center sm:items-start gap-8">
                @if($nomination->profile_image)
                    <img src="{{ asset('storage/' . $nomination->profile_image) }}" class="w-40 h-40 rounded-full object-cover border-4 border-gray-700 shadow-xl shrink-0">
                @else
                    <div class="w-40 h-40 rounded-full bg-gray-900 border-4 border-gray-700 flex items-center justify-center text-gray-500 font-bold text-4xl shrink-0">
                        {{ substr($nomination->name, 0, 1) }}
                    </div>
                @endif

                <div class="text-center sm:text-left flex-grow">
                    <div class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-xs font-mono font-bold mb-3">
                        CODE: {{ $nomination->code }}
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-2">{{ $nomination->name }}</h1>
                    <p class="text-lg text-green-400 font-medium mb-4">{{ $nomination->company_or_show ?? 'Independent Candidate' }}</p>

                    @if($nomination->bio)
                        <div class="prose prose-invert max-w-none text-gray-400 text-sm">
                            <p>{{ $nomination->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($nomination->facebook_url || $nomination->instagram_url || $nomination->twitter_url)
                <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl flex justify-center gap-4">
                    @if($nomination->facebook_url) <a href="{{ $nomination->facebook_url }}" target="_blank" class="p-3 bg-gray-900 rounded-xl text-gray-400 hover:text-blue-500 hover:bg-gray-700 transition-all"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a> @endif
                    @if($nomination->instagram_url) <a href="{{ $nomination->instagram_url }}" target="_blank" class="p-3 bg-gray-900 rounded-xl text-gray-400 hover:text-pink-500 hover:bg-gray-700 transition-all"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a> @endif
                    @if($nomination->twitter_url) <a href="{{ $nomination->twitter_url }}" target="_blank" class="p-3 bg-gray-900 rounded-xl text-gray-400 hover:text-blue-400 hover:bg-gray-700 transition-all"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723 10.054 10.054 0 01-3.127 1.184 4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a> @endif
                </div>
            @endif
        </div>

        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl overflow-hidden sticky top-6">
                <div class="bg-gray-900/80 p-6 border-b border-gray-700 text-center">
                    <div class="text-4xl font-mono font-bold text-white mb-1">
                        {{ number_format($nomination->total_votes) }}
                    </div>
                    <div class="text-sm text-gray-500 uppercase tracking-widest font-bold">Total Votes</div>
                </div>

                <div class="p-6 min-h-[350px] flex flex-col justify-center"
                     x-data="{
                         timer: 30,
                         interval: null,
                         circumference: 2 * Math.PI * 45,
                         get strokeDashoffset() {
                             return this.circumference - (this.timer / 30) * this.circumference;
                         }
                     }"
                     x-init="$watch('$wire.paymentStatus', value => {
                         if(value === 'waiting') {
                             timer = 30;
                             interval = setInterval(() => {
                                 if(timer > 0) timer--;
                                 else { clearInterval(interval); $wire.set('paymentStatus', 'failed'); $wire.set('paymentMessage', 'Transaction timed out. Please try again.'); }
                             }, 1000);
                         } else { clearInterval(interval); }
                     })">

                    @if($paymentStatus === 'waiting')
                        <div wire:poll.3s="checkPaymentStatus"></div>
                    @endif

                    @if($paymentStatus === 'idle' || $paymentStatus === 'initiating')
                        <div>
                            <h3 class="text-lg font-bold text-white mb-4">Support {{ $nomination->name }}</h3>
                            <form wire:submit="triggerMpesaVote">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">🇰🇪</span></div>
                                            <input type="text" wire:model="phone_number" placeholder="0712345678" class="block w-full pl-10 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('phone_number') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- DYNAMIC PRICE CALCULATOR -->
                                    <div x-data="{ inputAmount: @entangle('amount'), costPerVote: @entangle('costPerVote') }">
                                        <label class="block text-sm font-medium text-gray-400 mb-1">Amount to Support (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-bold sm:text-sm">KES</span>
                                            </div>
                                            <input type="number" wire:model.live="amount" x-model="inputAmount" x-bind:min="costPerVote" class="block w-full pl-14 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('amount') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror

                                        <div class="mt-3 flex items-center gap-2 px-4 py-2 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"></path></svg>
                                            <span>You are casting <strong class="font-mono text-lg" x-text="Math.floor(inputAmount / costPerVote) || 0"></strong> votes! <span class="text-gray-500 text-xs ml-1">(<span x-text="costPerVote"></span> KES = 1 Vote)</span></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" wire:loading.attr="disabled" class="mt-6 w-full flex items-center justify-center px-6 py-4 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="triggerMpesaVote">Vote via M-PESA</span>
                                    <span wire:loading wire:target="triggerMpesaVote" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Initiating...
                                    </span>
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($paymentStatus === 'waiting')
                        <div class="flex flex-col items-center justify-center text-center animate-fade-in">
                            <div class="relative w-32 h-32 mb-6">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-gray-700"></circle>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-green-500 transition-all duration-1000 ease-linear"
                                            x-bind:stroke-dasharray="circumference"
                                            x-bind:stroke-dashoffset="strokeDashoffset"
                                            stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-3xl font-mono font-bold text-white" x-text="timer"></span>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Check Your Phone</h3>
                            <p class="text-gray-400 text-sm">Please enter your M-PESA PIN to confirm the vote for {{ $nomination->name }}.</p>
                            <p class="text-green-500 text-xs mt-4 font-bold animate-pulse">Establishing payment...</p>
                        </div>
                    @endif

                    @if($paymentStatus === 'success')
                        <div class="flex flex-col items-center justify-center text-center animate-fade-in">
                            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-5 border border-green-500/50 shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">Vote Confirmed!</h3>
                            <p class="text-gray-300 text-sm mb-6 bg-gray-900 p-4 rounded-xl border border-gray-700">
                                Thanks for voting for us! We appreciate you and will keep offering the best services.
                            </p>
                            <button wire:click="resetVote" class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors w-full border border-gray-600">
                                Vote Again
                            </button>
                        </div>
                    @endif

                    @if($paymentStatus === 'failed')
                        <div class="flex flex-col items-center justify-center text-center animate-fade-in">
                            <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-5 border border-red-500/50 shadow-[0_0_30px_rgba(239,68,68,0.3)]">
                                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Vote Failed</h3>
                            <p class="text-red-400 text-sm mb-6">{{ $paymentMessage }}</p>
                            <button wire:click="resetVote" class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors w-full border border-gray-600">
                                Try Again
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
