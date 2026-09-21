<?php

use App\Models\NominationCategory;
use App\Models\Nomination;
use App\Models\Transaction;
use App\Models\TokenPackage;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.guest.app')] class extends Component {

    public NominationCategory $category;

    // Modal & selection
    public bool $showModal = false;
    public ?int $selectedNomineeId = null;

    // M-PESA form
    public string $phone_number = '';
    public float $amount = 50;
    public float $costPerVote = 10;

    // Payment state
    public string $paymentStatus = 'idle'; // idle, initiating, waiting, success, failed
    public string $paymentMessage = '';
    public ?int $transactionId = null;

    public function mount(NominationCategory $category): void
    {
        $this->category = $category;

        $package = TokenPackage::where('is_active', true)->orderBy('price_kes', 'asc')->first();
        if ($package && $package->tokens > 0) {
            $this->costPerVote = round($package->price_kes / $package->tokens, 2);
        }

        if ($this->amount < $this->costPerVote) {
            $this->amount = $this->costPerVote;
        }
    }

    protected function rules(): array
    {
        return [
            'phone_number' => 'required|string|min:10',
            'amount'       => 'required|numeric|min:' . $this->costPerVote,
        ];
    }

    #[Computed]
    public function nominations()
    {
        return Nomination::where('nomination_category_id', $this->category->id)
            ->where('is_active', true)
            ->orderByDesc('total_votes')
            ->get();
    }

    #[Computed]
    public function categoryTotalVotes(): int
    {
        return (int) $this->nominations->sum('total_votes');
    }

    #[Computed]
    public function selectedNominee(): ?Nomination
    {
        if (! $this->selectedNomineeId) return null;

        return Nomination::with('category')->find($this->selectedNomineeId);
    }

    public function selectNominee(int $id): void
    {
        $this->selectedNomineeId = $id;
        $this->resetVote();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedNomineeId = null;
        $this->resetVote();
    }

    public function triggerMpesaVote(MpesaService $mpesa): void
    {
        if (! $this->selectedNominee) return;

        $this->validate();
        $this->paymentStatus = 'initiating';
        $this->paymentMessage = 'Sending STK Push...';

        $reference = 'VOTE_' . $this->selectedNominee->code;

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, $reference);

            if (isset($response['CheckoutRequestID'])) {
                $txn = Transaction::create([
                    'type'                => 'stk_push',
                    'phone_number'        => $this->phone_number,
                    'amount'              => $this->amount,
                    'nomination_id'       => $this->selectedNominee->id,
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'status'              => 'pending',
                ]);

                $this->transactionId = $txn->id;
                $this->paymentStatus = 'waiting';
                $this->paymentMessage = 'Establishing payment... Please enter your PIN.';
            } else {
                $this->paymentStatus = 'failed';
                $this->paymentMessage = 'Safaricom Error: Could not initiate STK Push.';
            }
        } catch (\Throwable $e) {
            $this->paymentStatus = 'failed';
            $this->paymentMessage = 'System Error: ' . $e->getMessage();
        }
    }

    public function checkPaymentStatus(): void
    {
        if (! $this->transactionId) return;

        $txn = Transaction::find($this->transactionId);
        if (! $txn) return;

        if ($txn->status === 'completed') {
            $this->paymentStatus = 'success';
            $this->paymentMessage = '';
            unset($this->nominations, $this->categoryTotalVotes);
        } elseif ($txn->status === 'failed') {
            $this->paymentStatus = 'failed';
            $this->paymentMessage = 'Transaction failed or was cancelled. ' . ($txn->result_desc ?? '');
        } else {
            $this->paymentMessage = 'Still waiting for Safaricom confirmation...';
        }
    }

    public function resetVote(): void
    {
        $this->paymentStatus = 'idle';
        $this->transactionId = null;
        $this->paymentMessage = '';
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">

    {{-- Breadcrumb --}}
    <a href="{{ route('polls') }}" wire:navigate
       class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-yellow-500 mb-6 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Polls
    </a>

    {{-- Category header --}}
    <div class="bg-gray-800 rounded-2xl p-6 sm:p-8 border border-gray-700 shadow-2xl mb-8 flex flex-col sm:flex-row items-start sm:items-center gap-6">
        @if ($category->thumbnail)
            <img src="{{ asset('storage/' . $category->thumbnail) }}"
                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover border border-gray-600 shadow-lg shrink-0">
        @else
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-gray-900 border border-gray-700 flex items-center justify-center shrink-0">
                <svg class="w-10 h-10 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                </svg>
            </div>
        @endif

        <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ $category->name }}</h1>
            <p class="text-gray-400 text-sm max-w-2xl">
                {{ $category->description ?? $category->excerpt }}
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-500/10 border border-green-500/20 text-green-400 font-bold">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                    </span>
                    Voting Live
                </span>
                <span class="text-gray-400 font-mono">{{ number_format($this->categoryTotalVotes) }} total votes</span>
                <span class="text-gray-500">•</span>
                <span class="text-gray-400">{{ $this->nominations->count() }} nominees</span>
            </div>
        </div>
    </div>

    {{-- Ranked list --}}
    <div class="space-y-3">
        @forelse ($this->nominations as $index => $nominee)
            @php
                $percent = $this->categoryTotalVotes > 0
                    ? round(($nominee->total_votes / $this->categoryTotalVotes) * 100, 1)
                    : 0;

                $rank = match (true) {
                    $index === 0 => ['badge' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30', 'bar' => 'bg-yellow-500'],
                    $index === 1 => ['badge' => 'bg-gray-300/20 text-gray-200 border-gray-300/30',     'bar' => 'bg-gray-300'],
                    $index === 2 => ['badge' => 'bg-orange-600/20 text-orange-400 border-orange-600/30','bar' => 'bg-orange-500'],
                    default      => ['badge' => 'bg-gray-900 text-gray-500 border-gray-700',           'bar' => 'bg-gray-500'],
                };
            @endphp

            <div wire:key="nom-{{ $nominee->id }}"
                 class="bg-gray-800/50 hover:bg-gray-800 rounded-xl p-4 border border-gray-700/50 hover:border-gray-600 transition-all shadow-lg">

                <div class="flex items-center justify-between gap-4">

                    {{-- Left: rank, avatar, name --}}
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-base border shrink-0 {{ $rank['badge'] }}">
                            #{{ $index + 1 }}
                        </div>

                        @if ($nominee->profile_image)
                            <img src="{{ asset('storage/' . $nominee->profile_image) }}"
                                 class="w-12 h-12 rounded-full object-cover border border-gray-600 shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-500 font-bold shrink-0">
                                {{ substr($nominee->name, 0, 1) }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-white truncate">
                                {{ $nominee->name }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-400 truncate">
                                {{ $nominee->company_or_show ?? 'Independent' }}
                            </p>
                        </div>
                    </div>

                    {{-- Right: votes, details, vote --}}
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <div class="text-right hidden sm:block">
                            <div class="text-xl sm:text-2xl font-mono font-bold text-white">
                                {{ number_format((int) $nominee->total_votes) }}
                            </div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-wider">
                                {{ $percent }}%
                            </div>
                        </div>

                        {{-- Details: link to single nominee page, stops propagation to row click --}}
                        <a href="{{ route('polls.vote', $nominee->code) }}"
                           wire:navigate
                           wire:click.stop
                           class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg border border-gray-600 bg-gray-900/50 hover:bg-gray-700 text-xs font-bold text-gray-300 hover:text-white transition-colors"
                           title="View profile">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="hidden md:inline">Details</span>
                        </a>

                        {{-- Vote button: opens quick-vote modal --}}
                        <button type="button"
                                wire:click.stop="selectNominee({{ $nominee->id }})"
                                class="px-4 sm:px-6 py-2.5 sm:py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-red-600/20">
                            Vote
                        </button>
                    </div>
                </div>

                {{-- Percentage bar --}}
                <div class="mt-3 w-full bg-gray-900 rounded-full h-2 relative overflow-hidden border border-gray-700/50">
                    <div class="{{ $rank['bar'] }} h-full rounded-full transition-all duration-1000 ease-out"
                         style="width: {{ max($percent, 1) }}%"></div>
                </div>
            </div>
        @empty
            <div class="bg-gray-800/50 rounded-xl border border-gray-700/50 py-20 text-center">
                <svg class="mx-auto w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-lg font-bold text-white mb-1">No Nominees Yet</h3>
                <p class="text-sm text-gray-400">Nominees for this category will appear here once added.</p>
            </div>
        @endforelse
    </div>

    {{-- ============= QUICK VOTE MODAL ============= --}}
    @if ($showModal && $this->selectedNominee)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
             role="dialog" aria-modal="true" aria-labelledby="modal-title">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity"
                 wire:click="closeModal"></div>

            {{-- Panel --}}
            <div class="relative z-10 w-full max-w-lg bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-700 bg-gray-900/80 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-white">Quick Vote</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="overflow-y-auto">

                    {{-- Nominee summary --}}
                    <div class="p-6 bg-gray-800/50 border-b border-gray-700 flex items-center gap-5">
                        @if ($this->selectedNominee->profile_image)
                            <img src="{{ asset('storage/' . $this->selectedNominee->profile_image) }}"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-gray-600 shadow-lg shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-900 border-2 border-gray-600 flex items-center justify-center text-gray-500 font-bold text-xl shrink-0">
                                {{ substr($this->selectedNominee->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-mono text-yellow-500 font-bold mb-1">CODE: {{ $this->selectedNominee->code }}</div>
                            <h4 class="text-xl font-bold text-white leading-tight truncate">{{ $this->selectedNominee->name }}</h4>
                            <p class="text-sm text-gray-400 truncate">{{ $this->selectedNominee->category->name ?? '' }}</p>
                        </div>
                        <a href="{{ route('polls.vote', $this->selectedNominee->code) }}"
                           wire:navigate
                           class="hidden sm:inline-flex text-xs font-bold text-yellow-500 hover:text-yellow-400 underline shrink-0">
                            View profile →
                        </a>
                    </div>

                    {{-- Terminal --}}
                    <div class="p-6 bg-gray-800 min-h-[300px] flex flex-col justify-center"
                         x-data="{
                             totalTime: 90,
                             timer: 90,
                             interval: null,
                             circumference: 2 * Math.PI * 45,
                             get strokeDashoffset() {
                                 return this.circumference - (this.timer / this.totalTime) * this.circumference;
                             }
                         }"
                         x-init="$watch('$wire.paymentStatus', value => {
                             if (value === 'waiting') {
                                 timer = totalTime;
                                 clearInterval(interval);
                                 interval = setInterval(() => {
                                     if (timer > 0) timer--;
                                     else {
                                         clearInterval(interval);
                                         $wire.set('paymentStatus', 'failed');
                                         $wire.set('paymentMessage', 'Transaction is still pending. If you already paid, click Check Status.');
                                     }
                                 }, 1000);
                             } else {
                                 clearInterval(interval);
                             }
                         })">

                        @if ($paymentStatus === 'waiting')
                            <div wire:poll.3s="checkPaymentStatus"></div>
                        @endif

                        {{-- State 1: FORM --}}
                        @if ($paymentStatus === 'idle' || $paymentStatus === 'initiating')
                            <form wire:submit="triggerMpesaVote">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">🇰🇪</span>
                                            </div>
                                            <input type="tel" wire:model="phone_number" placeholder="0712345678"
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                                   {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('phone_number') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-1">Amount to Support (KES)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 font-bold sm:text-sm">KES</span>
                                            </div>
                                            <input type="number" wire:model.live="amount" min="{{ $costPerVote }}" step="1"
                                                   class="block w-full pl-14 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                                   {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                        </div>
                                        @error('amount') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror

                                        <div class="mt-3 flex items-center gap-2 px-4 py-2.5 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                                            </svg>
                                            <span>
                                                You are casting
                                                <strong class="font-mono text-lg" x-text="Math.max(0, Math.floor($wire.amount / $wire.costPerVote) || 0)"></strong>
                                                votes!
                                                <span class="text-gray-500 text-xs ml-1 block sm:inline">
                                                    ({{ number_format($costPerVote, 2) }} KES = 1 Vote)
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" wire:loading.attr="disabled"
                                        class="mt-6 w-full flex items-center justify-center px-6 py-4 rounded-xl shadow-lg text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="triggerMpesaVote">Confirm Vote</span>
                                    <span wire:loading wire:target="triggerMpesaVote" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        Initiating...
                                    </span>
                                </button>
                            </form>
                        @endif

                        {{-- State 2: WAITING --}}
                        @if ($paymentStatus === 'waiting')
                            <div class="flex flex-col items-center justify-center text-center py-4">
                                <div class="relative w-32 h-32 mb-6">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-gray-700"/>
                                        <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-green-500 transition-all duration-1000 ease-linear"
                                                x-bind:stroke-dasharray="circumference"
                                                x-bind:stroke-dashoffset="strokeDashoffset"
                                                stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-3xl font-mono font-bold text-white" x-text="timer"></span>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-white mb-2">Check Your Phone</h3>
                                <p class="text-gray-400 text-sm">Please enter your M-PESA PIN to confirm.</p>
                                <p class="text-green-500 text-xs mt-4 font-bold animate-pulse">Waiting for Safaricom...</p>

                                <button wire:click="checkPaymentStatus" wire:loading.attr="disabled"
                                        class="mt-6 text-xs font-bold text-yellow-500 hover:text-yellow-400 underline disabled:opacity-50">
                                    Already paid? Check status
                                </button>
                            </div>
                        @endif

                        {{-- State 3: SUCCESS --}}
                        @if ($paymentStatus === 'success')
                            <div class="flex flex-col items-center justify-center text-center py-4">
                                <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-5 border border-green-500/50 shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white mb-3">Vote Confirmed!</h3>
                                <p class="text-gray-300 text-sm mb-6 bg-gray-900 p-4 rounded-xl border border-gray-700">
                                    Thanks for supporting <strong>{{ $this->selectedNominee->name }}</strong>!
                                </p>
                                <div class="flex gap-4 w-full">
                                    <button wire:click="resetVote"
                                            class="flex-1 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors border border-gray-600">
                                        Vote Again
                                    </button>
                                    <button wire:click="closeModal"
                                            class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-colors">
                                        Close
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- State 4: FAILED --}}
                        @if ($paymentStatus === 'failed')
                            <div class="flex flex-col items-center justify-center text-center py-4">
                                <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-5 border border-red-500/50 shadow-[0_0_30px_rgba(239,68,68,0.3)]">
                                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white mb-2">Vote Failed</h3>
                                <p class="text-red-400 text-sm mb-6">{{ $paymentMessage }}</p>
                                <button wire:click="resetVote"
                                        class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors w-full border border-gray-600">
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
