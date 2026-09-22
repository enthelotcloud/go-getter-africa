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

    public bool $showModal = false;
    public ?int $selectedNomineeId = null;

    public string $phone_number = '';
    public float $amount = 50;
    public float $costPerVote = 10;

    public string $paymentStatus = 'idle';
    public string $paymentMessage = '';
    public ?int $transactionId = null;

    public function mount(): void
    {
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
    public function categories()
    {
        return NominationCategory::where('is_active', true)
            ->with(['nominations' => function ($query) {
                $query->where('is_active', true)
                      ->orderByDesc('total_votes')
                      ->limit(5);
            }])
            ->get()
            ->map(function ($category) {
                $category->total_category_votes = (int) Nomination::where('nomination_category_id', $category->id)
                    ->where('is_active', true)
                    ->sum('total_votes');
                return $category;
            });
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

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, 'VOTE_' . $this->selectedNominee->code);

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
            unset($this->categories);
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

<div>
<style>
    @media (max-width: 1023px) {
        .vote-sheet-enter { animation: vote-sheet-up 0.32s cubic-bezier(0.16, 1, 0.3, 1); }
    }
    @keyframes vote-sheet-up {
        from { transform: translateY(32px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
    .vote-backdrop { animation: fade-in 0.2s ease-out; }
</style>

{{-- Mobile backdrop --}}
@if ($showModal)
    <div class="lg:hidden fixed inset-0 z-[99] bg-gray-900/90 backdrop-blur-sm vote-backdrop"
         wire:click="closeModal"></div>
@endif

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-10">

    {{-- Header --}}
    <div class="mb-4 sm:mb-8">
        <div class="flex items-center gap-2 sm:gap-3 mb-1.5 sm:mb-2">
            <h1 class="text-2xl sm:text-4xl font-bold text-white tracking-tight leading-tight">
                Live Polls
            </h1>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-green-500/10 border border-green-500/20 text-green-400 text-[10px] sm:text-xs font-bold">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                </span>
                Live
            </span>
        </div>
        <p class="text-xs sm:text-sm text-gray-400">
            Tap a nominee for their profile, or hit <span class="text-yellow-500 font-bold">Vote</span> to cast your vote right here.
        </p>
    </div>

    {{-- Categories Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-5">
        @forelse ($this->categories as $category)
            <div wire:key="cat-{{ $category->id }}"
                 class="bg-gray-800/60 rounded-2xl border border-gray-700/60 shadow-xl overflow-hidden flex flex-col">

                {{-- Card Header --}}
                <div class="p-3.5 sm:p-5 border-b border-gray-700/60 flex items-center gap-3">
                    @if ($category->thumbnail)
                        <img src="{{ asset('storage/' . $category->thumbnail) }}"
                             class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl object-cover border border-gray-600 shrink-0">
                    @else
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gray-900 border border-gray-700 flex items-center justify-center shrink-0">
                            <flux:icon.trophy class="w-5 h-5 text-gray-500" />
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm sm:text-base font-bold text-white leading-tight truncate">
                            {{ $category->name }}
                        </h2>
                        <div class="flex items-center gap-2 mt-1 text-[10px] sm:text-xs">
                            <span class="text-gray-400 font-mono">
                                {{ number_format($category->total_category_votes) }} votes
                            </span>
                            <span class="text-gray-600">·</span>
                            <span class="text-gray-400">
                                {{ $category->nominations->count() }}{{ $category->nominations->count() >= 5 ? '+' : '' }} top
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Nominee List --}}
                <div class="p-2 sm:p-3 flex-1 space-y-1">
                    @forelse ($category->nominations as $index => $nominee)
                        @php
                            $percent = $category->total_category_votes > 0
                                ? round(($nominee->total_votes / $category->total_category_votes) * 100, 1)
                                : 0;

                            $rank = match (true) {
                                $index === 0 => ['badge' => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30', 'bar' => 'bg-yellow-500'],
                                $index === 1 => ['badge' => 'bg-gray-300/15 text-gray-200 border-gray-300/30',       'bar' => 'bg-gray-300'],
                                $index === 2 => ['badge' => 'bg-orange-600/15 text-orange-400 border-orange-600/30', 'bar' => 'bg-orange-500'],
                                default      => ['badge' => 'bg-gray-900 text-gray-500 border-gray-700',             'bar' => 'bg-gray-500'],
                            };
                        @endphp

                        <div wire:key="cat-{{ $category->id }}-nom-{{ $nominee->id }}"
                             class="flex items-center gap-2 sm:gap-2.5 p-1.5 sm:p-2 rounded-lg hover:bg-gray-700/30 transition-colors">

                            {{-- Tappable left: opens the profile --}}
                            <a href="{{ route('polls.vote', $nominee->code) }}"
                               wire:navigate
                               class="flex items-center gap-2 sm:gap-2.5 min-w-0 flex-1 group">

                                {{-- Rank badge --}}
                                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center font-bold text-[10px] border shrink-0 {{ $rank['badge'] }}">
                                    {{ $index + 1 }}
                                </div>

                                {{-- Avatar only when present --}}
                                @if ($nominee->profile_image)
                                    <img src="{{ asset('storage/' . $nominee->profile_image) }}"
                                         class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border border-gray-600 shrink-0">
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs sm:text-sm font-semibold text-white truncate group-hover:text-yellow-500 transition-colors">
                                            {{ $nominee->name }}
                                        </span>
                                        <span class="text-[10px] sm:text-xs font-mono text-gray-400 shrink-0 tabular-nums">
                                            {{ number_format((int) $nominee->total_votes) }}
                                        </span>
                                    </div>

                                    <div class="mt-1.5 w-full bg-gray-900 rounded-full h-1 overflow-hidden">
                                        <div class="{{ $rank['bar'] }} h-full rounded-full transition-all duration-1000 ease-out"
                                             style="width: {{ max($percent, 1) }}%"></div>
                                    </div>
                                </div>
                            </a>

                            {{-- Vote button --}}
                            <button type="button"
                                    wire:click="selectNominee({{ $nominee->id }})"
                                    class="shrink-0 inline-flex items-center justify-center gap-1 px-2.5 sm:px-3 py-1 sm:py-1.5 bg-yellow-500 hover:bg-yellow-400 active:bg-yellow-600 text-gray-900 text-[10px] sm:text-xs font-bold rounded-full transition-colors">
                                <flux:icon.hand-thumb-up class="w-3 h-3" />
                                Vote
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-gray-500">
                            No nominees yet.
                        </div>
                    @endforelse
                </div>

                {{-- Card Footer --}}
                <div class="p-2.5 sm:p-3 border-t border-gray-700/60">
                    <a href="{{ route('polls.category', $category->slug) }}" wire:navigate
                       class="w-full flex items-center justify-center gap-1.5 py-2 sm:py-2.5 rounded-lg bg-gray-900 hover:bg-gray-700 text-xs sm:text-sm font-bold text-gray-200 hover:text-white border border-gray-700 hover:border-gray-600 transition-colors">
                        View all
                        <flux:icon.arrow-right class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-gray-800/60 rounded-2xl border border-gray-700/60 py-16 text-center px-4">
                <flux:icon.trophy class="w-12 h-12 text-gray-600 mx-auto mb-3" />
                <h3 class="text-base font-bold text-white mb-1">No Categories Yet</h3>
                <p class="text-xs text-gray-400">Poll categories will appear here once they're live.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- ═══ QUICK VOTE MODAL ═══ --}}
@if ($showModal && $this->selectedNominee)
    <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-6"
         role="dialog" aria-modal="true">

        <div class="relative z-10 w-full sm:max-w-lg bg-gray-800 border border-gray-700 shadow-2xl overflow-hidden flex flex-col
                    max-h-[92vh] sm:max-h-[90vh]
                    rounded-t-3xl sm:rounded-2xl
                    {{ $showModal ? 'vote-sheet-enter' : '' }}">

            {{-- Grabber (mobile) --}}
            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                <div class="w-10 h-1 rounded-full bg-gray-600"></div>
            </div>

            {{-- Header --}}
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-700 bg-gray-900/80 flex justify-between items-center shrink-0">
                <h3 class="text-base sm:text-lg font-bold text-white">Quick Vote</h3>
                <button type="button" wire:click="closeModal"
                        class="text-gray-400 hover:text-white p-1 -mr-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1">

                {{-- Nominee summary --}}
                <div class="p-4 sm:p-6 bg-gray-800/50 border-b border-gray-700 flex items-center gap-3 sm:gap-5">
                    @if ($this->selectedNominee->profile_image)
                        <img src="{{ asset('storage/' . $this->selectedNominee->profile_image) }}"
                             class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover border-2 border-gray-600 shrink-0">
                    @else
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-900 border-2 border-gray-600 flex items-center justify-center text-gray-500 font-bold text-lg shrink-0">
                            {{ substr($this->selectedNominee->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="text-[10px] sm:text-xs font-mono text-yellow-500 font-bold mb-0.5">
                            {{ $this->selectedNominee->code }}
                        </div>
                        <h4 class="text-base sm:text-xl font-bold text-white leading-tight truncate">
                            {{ $this->selectedNominee->name }}
                        </h4>
                        <p class="text-xs sm:text-sm text-gray-400 truncate">
                            {{ $this->selectedNominee->category->name ?? '' }}
                        </p>
                    </div>
                    <a href="{{ route('polls.vote', $this->selectedNominee->code) }}"
                       wire:navigate
                       class="hidden sm:inline-flex text-xs font-bold text-yellow-500 hover:text-yellow-400 underline shrink-0">
                        View profile →
                    </a>
                </div>

                {{-- Terminal --}}
                <div class="p-4 sm:p-6 bg-gray-800 min-h-[280px] sm:min-h-[300px] flex flex-col justify-center"
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
                         } else { clearInterval(interval); }
                     })">

                    @if ($paymentStatus === 'waiting')
                        <div wire:poll.3s="checkPaymentStatus"></div>
                    @endif

                    {{-- FORM --}}
                    @if ($paymentStatus === 'idle' || $paymentStatus === 'initiating')
                        <form wire:submit="triggerMpesaVote">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-xs">🇰🇪</span>
                                        </div>
                                        <input type="tel" wire:model="phone_number" placeholder="0712345678" inputmode="tel"
                                               class="block w-full pl-9 pr-3 py-2.5 sm:py-3 bg-gray-900 border border-gray-600 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                    </div>
                                    @error('phone_number') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-400 mb-1">Amount (KES)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold text-xs">KES</span>
                                        </div>
                                        <input type="number" wire:model.live="amount" min="{{ $costPerVote }}" step="1" inputmode="numeric"
                                               class="block w-full pl-12 pr-3 py-2.5 sm:py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-base focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               {{ $paymentStatus === 'initiating' ? 'disabled' : '' }}>
                                    </div>
                                    @error('amount') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror

                                    <div class="mt-3 flex items-center gap-2 px-3 py-2 bg-green-500/10 border border-green-500/20 rounded-lg text-xs text-green-400 whitespace-nowrap overflow-hidden">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                                        </svg>
                                        <span class="truncate">
                                            Casting <strong class="font-mono text-base" x-text="Math.max(0, Math.floor($wire.amount / $wire.costPerVote) || 0)"></strong> votes
                                            <span class="text-gray-500">· {{ number_format($costPerVote, 2) }} KES/vote</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" wire:loading.attr="disabled"
                                    class="mt-5 w-full flex items-center justify-center px-6 py-3.5 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 active:bg-green-800 transition-all disabled:opacity-50">
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

                    {{-- WAITING --}}
                    @if ($paymentStatus === 'waiting')
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="relative w-24 h-24 sm:w-32 sm:h-32 mb-4">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-gray-700"/>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-green-500 transition-all duration-1000 ease-linear"
                                            x-bind:stroke-dasharray="circumference"
                                            x-bind:stroke-dashoffset="strokeDashoffset"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl sm:text-3xl font-mono font-bold text-white" x-text="timer"></span>
                                </div>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white mb-1">Check Your Phone</h3>
                            <p class="text-xs text-gray-400">Enter your M-PESA PIN to confirm.</p>
                            <p class="text-green-500 text-[11px] mt-3 font-bold animate-pulse">Waiting for Safaricom...</p>
                            <button wire:click="checkPaymentStatus" wire:loading.attr="disabled"
                                    class="mt-4 text-xs font-bold text-yellow-500 hover:text-yellow-400 underline">
                                Already paid? Check status
                            </button>
                        </div>
                    @endif

                    {{-- SUCCESS --}}
                    @if ($paymentStatus === 'success')
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-4 border border-green-500/50">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-white mb-2">Vote Confirmed!</h3>
                            <p class="text-xs text-gray-300 mb-5 bg-gray-900 p-3 rounded-xl border border-gray-700">
                                Thanks for supporting <strong>{{ $this->selectedNominee->name }}</strong>!
                            </p>
                            <div class="flex gap-2.5 w-full">
                                <button wire:click="resetVote"
                                        class="flex-1 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-lg border border-gray-600">
                                    Vote Again
                                </button>
                                <button wire:click="closeModal"
                                        class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg">
                                    Close
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- FAILED --}}
                    @if ($paymentStatus === 'failed')
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-4 border border-red-500/50">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white mb-1.5">Vote Failed</h3>
                            <p class="text-red-400 text-xs mb-5">{{ $paymentMessage }}</p>
                            <button wire:click="resetVote"
                                    class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-lg w-full border border-gray-600">
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
