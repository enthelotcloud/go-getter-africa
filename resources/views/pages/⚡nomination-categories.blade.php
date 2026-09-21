<?php

use App\Models\NominationCategory;
use App\Models\Nomination;
use App\Models\Transaction;
use App\Models\TokenPackage;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout('layouts.guest.app')] class extends Component {

    // Modal & Selection State
    public $showModal = false;
    public $selectedNominee = null;

    // M-PESA Form State
    public $phone_number = '';
    public $amount = 50;
    public $costPerVote = 10;

    // Payment Processing State
    public $paymentStatus = 'idle'; // idle, initiating, waiting, success, failed
    public $paymentMessage = '';
    public $transactionId = null;

    public function rules()
    {
        return [
            'phone_number' => 'required|string|min:10',
            'amount' => 'required|numeric|min:' . $this->costPerVote,
        ];
    }

    public function mount()
    {
        // Find the base rate from active token packages
        $package = TokenPackage::where('is_active', true)->orderBy('price_kes', 'asc')->first();
        if ($package) {
            $this->costPerVote = $package->price_kes / max(1, $package->tokens);
        }

        if ($this->amount < $this->costPerVote) {
            $this->amount = $this->costPerVote;
        }
    }

    public function selectNominee($id)
    {
        $this->selectedNominee = Nomination::with('category')->find($id);
        $this->resetVote();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedNominee = null;
        $this->resetVote();
    }

    public function triggerMpesaVote(MpesaService $mpesa)
    {
        $this->validate();
        $this->paymentStatus = 'initiating';
        $this->paymentMessage = 'Sending STK Push...';

        $reference = 'VOTE_' . $this->selectedNominee->code;

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, $reference);

            if (isset($response['CheckoutRequestID'])) {
                $txn = Transaction::create([
                    'type' => 'stk_push',
                    'phone_number' => $this->phone_number,
                    'amount' => $this->amount,
                    'nomination_id' => $this->selectedNominee->id,
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
            $this->selectedNominee->refresh();
        } elseif ($txn->status === 'failed') {
            $this->paymentStatus = 'failed';
            $this->paymentMessage = 'Transaction failed or was cancelled. ' . $txn->result_desc;
        }
    }

    public function resetVote()
    {
        $this->paymentStatus = 'idle';
        $this->transactionId = null;
        $this->paymentMessage = '';
    }

    public function with(): array
    {
        $categories = NominationCategory::where('is_active', true)
            ->with(['nominations' => function ($query) {
                $query->where('is_active', true)
                      ->orderByDesc('total_votes')
                      ->limit(5);
            }])
            ->get()
            ->map(function ($category) {
                $category->total_category_votes = Nomination::where('nomination_category_id', $category->id)->sum('total_votes');
                return $category;
            });

        return [
            'categories' => $categories
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" wire:poll.10s>

    <!-- Header -->
    <div class="mb-10 flex justify-between items-end border-b border-gray-700 pb-5">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-lg shadow-green-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h1 class="text-3xl font-bold text-white">Live Polls</h1>
            </div>
            <p class="text-gray-400">Click on any nominee to vote instantly.</p>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($categories as $category)
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden flex flex-col h-full hover:border-gray-600 transition-colors">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>

                <div class="flex gap-2 mb-4">
                    <span class="px-3 py-1 bg-green-500/10 text-green-400 text-xs font-bold rounded-full border border-green-500/20">Active</span>
                </div>

                <h2 class="text-xl font-bold text-white mb-6">{{ $category->name }}</h2>

                <div class="space-y-4 flex-grow">
                    @forelse($category->nominations as $index => $nominee)
                        @php
                            $percentage = $category->total_category_votes > 0
                                ? round(($nominee->total_votes / $category->total_category_votes) * 100)
                                : 0;

                            $barColor = $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-green-500' : 'bg-gray-500');
                            $textColor = $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-green-500' : 'text-gray-400');
                        @endphp

                        <!-- Clickable Nominee Block -->
                        <div wire:click="selectNominee({{ $nominee->id }})" class="cursor-pointer group hover:bg-gray-700/50 p-2 -mx-2 rounded-xl transition-all border border-transparent hover:border-gray-600">
                            <div class="flex justify-between items-end mb-1.5 px-1">
                                <span class="text-sm font-semibold text-gray-200 group-hover:text-green-400 truncate pr-4 transition-colors">
                                    {{ $nominee->name }}
                                </span>
                                <span class="text-xs font-mono {{ $textColor }} whitespace-nowrap">{{ number_format($nominee->total_votes) }} votes</span>
                            </div>
                            <div class="w-full bg-gray-900 rounded-full h-6 relative overflow-hidden border border-gray-700 mx-1">
                                <div class="{{ $barColor }} h-6 rounded-full transition-all duration-1000 ease-out flex items-center px-3" style="width: {{ max($percentage, 10) }}%">
                                    <span class="text-xs font-bold text-white shadow-sm">{{ $percentage }}%</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">No nominees added yet.</div>
                    @endforelse
                </div>

                <div class="mt-8 pt-5 border-t border-gray-700 flex justify-between items-center">
                    <span class="text-sm font-mono text-gray-400">{{ number_format($category->total_category_votes) }} total votes</span>
                    <a href="{{ route('polls.category', $category->slug) }}" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold rounded-lg transition-colors border border-gray-600 hover:border-gray-500 shadow-lg">
                        Expand All
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Quick Vote Modal Overlay (Z-Index Fixed) -->
    @if($showModal && $selectedNominee)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background Blur Overlay -->
            <div class="absolute inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Panel (Elevated safely above background) -->
            <div class="relative z-10 w-full max-w-lg bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-700 bg-gray-900/80 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-white">Quick Vote</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="overflow-y-auto">
                    <!-- Nominee Summary -->
                    <div class="p-6 bg-gray-800/50 border-b border-gray-700 flex items-center gap-5">
                        @if($selectedNominee->profile_image)
                            <img src="{{ asset('storage/' . $selectedNominee->profile_image) }}" class="w-16 h-16 rounded-full object-cover border-2 border-gray-600 shadow-lg">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-900 border-2 border-gray-600 flex items-center justify-center text-gray-500 font-bold text-xl">
                                {{ substr($selectedNominee->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-xs font-mono text-yellow-500 font-bold mb-1">CODE: {{ $selectedNominee->code }}</div>
                            <h4 class="text-xl font-bold text-white leading-tight">{{ $selectedNominee->name }}</h4>
                            <p class="text-sm text-gray-400">{{ $selectedNominee->category->name ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Interactive M-PESA Terminal -->
                    <div class="p-6 bg-gray-800 min-h-[300px] flex flex-col justify-center"
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

                        <!-- State 1: IDLE (Form) -->
                        @if($paymentStatus === 'idle' || $paymentStatus === 'initiating')
                            <form wire:submit="triggerMpesaVote">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">🇰🇪</span></div>
                                            <input type="text" wire:model="phone_number" placeholder="0712345678" class="block w-full pl-10 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('phone_number') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-1">Amount to Support (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-bold sm:text-sm">KES</span>
                                            </div>
                                            <input type="number" wire:model.live="amount" x-bind:min="$wire.costPerVote" class="block w-full pl-14 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('amount') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror

                                        <!-- Dynamic Math via Alpine accessing $wire directly -->
                                        <div class="mt-3 flex items-center gap-2 px-4 py-2 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"></path></svg>
                                            <span>You are casting <strong class="font-mono text-lg" x-text="Math.floor($wire.amount / $wire.costPerVote) || 0"></strong> votes! <span class="text-gray-500 text-xs ml-1 block sm:inline">(<span x-text="$wire.costPerVote"></span> KES = 1 Vote)</span></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" wire:loading.attr="disabled" class="mt-6 w-full flex items-center justify-center px-6 py-4 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="triggerMpesaVote">Confirm Vote</span>
                                    <span wire:loading wire:target="triggerMpesaVote" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Initiating...
                                    </span>
                                </button>
                            </form>
                        @endif

                        <!-- State 2: WAITING FOR PIN -->
                        @if($paymentStatus === 'waiting')
                            <div class="flex flex-col items-center justify-center text-center animate-fade-in py-4">
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
                                <p class="text-gray-400 text-sm">Please enter your M-PESA PIN to confirm.</p>
                                <p class="text-green-500 text-xs mt-4 font-bold animate-pulse">Waiting for Safaricom...</p>
                            </div>
                        @endif

                        <!-- State 3: SUCCESS -->
                        @if($paymentStatus === 'success')
                            <div class="flex flex-col items-center justify-center text-center animate-fade-in py-4">
                                <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-5 border border-green-500/50 shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-3">Vote Confirmed!</h3>
                                <p class="text-gray-300 text-sm mb-6 bg-gray-900 p-4 rounded-xl border border-gray-700">
                                    Thanks for supporting {{ $selectedNominee->name }}!
                                </p>
                                <div class="flex gap-4 w-full">
                                    <button wire:click="resetVote" class="flex-1 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors border border-gray-600">
                                        Vote Again
                                    </button>
                                    <button wire:click="closeModal" class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-colors">
                                        Close
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- State 4: FAILED -->
                        @if($paymentStatus === 'failed')
                            <div class="flex flex-col items-center justify-center text-center animate-fade-in py-4">
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
    @endif
</div>
