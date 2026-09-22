<?php

use App\Models\Nomination;
use App\Models\Transaction;
use App\Models\TokenPackage;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.guest.app')] class extends Component {

    public Nomination $nomination;

    public bool $showModal = false;

    public string $phone_number = '';
    public float $amount = 50;
    public float $costPerVote = 10;

    public string $paymentStatus = 'idle';
    public string $paymentMessage = '';
    public ?int $transactionId = null;

    public function mount(Nomination $nomination): void
    {
        $this->nomination = $nomination->load('category');

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
    public function rank(): int
    {
        return Nomination::where('nomination_category_id', $this->nomination->nomination_category_id)
            ->where('is_active', true)
            ->where('total_votes', '>', $this->nomination->total_votes)
            ->count() + 1;
    }

    #[Computed]
    public function categoryTotal(): int
    {
        return (int) Nomination::where('nomination_category_id', $this->nomination->nomination_category_id)
            ->where('is_active', true)
            ->sum('total_votes');
    }

    #[Computed]
    public function percent(): float
    {
        $total = $this->categoryTotal;
        return $total > 0 ? round(($this->nomination->total_votes / $total) * 100, 1) : 0.0;
    }

    #[Computed]
    public function otherNominees()
    {
        return Nomination::where('nomination_category_id', $this->nomination->nomination_category_id)
            ->where('id', '!=', $this->nomination->id)
            ->where('is_active', true)
            ->orderByDesc('total_votes')
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function shareUrl(): string
    {
        return route('polls.vote', $this->nomination->code);
    }

    #[Computed]
    public function shareText(): string
    {
        return "Vote for {$this->nomination->name} in the {$this->nomination->category->name} category on Go Getter Africa!";
    }

    public function openVoteModal(): void
    {
        $this->resetVote();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetVote();
    }

    public function triggerMpesaVote(MpesaService $mpesa): void
    {
        $this->validate();
        $this->paymentStatus = 'initiating';
        $this->paymentMessage = 'Sending STK Push...';

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, 'VOTE_' . $this->nomination->code);

            if (isset($response['CheckoutRequestID'])) {
                $txn = Transaction::create([
                    'type'                => 'stk_push',
                    'phone_number'        => $this->phone_number,
                    'amount'              => $this->amount,
                    'nomination_id'       => $this->nomination->id,
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
            $this->nomination->refresh();
            unset($this->rank, $this->categoryTotal, $this->percent);
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

@php
    $encUrl  = urlencode($this->shareUrl);
    $encText = urlencode($this->shareText);
@endphp

<div>
<style>
    @media (max-width: 1023px) {
        .vote-form-panel.show-mobile { animation: vote-sheet-up 0.32s cubic-bezier(0.16, 1, 0.3, 1); }
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

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-10 pb-32 lg:pb-10">

    {{-- Breadcrumb --}}
    <a href="{{ route('polls.category', $nomination->category->slug) }}" wire:navigate
       class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-400 hover:text-yellow-500 mb-3 sm:mb-6 transition-colors">
        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to {{ $nomination->category->name }}
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">

        {{-- ═══════════ LEFT COLUMN ═══════════ --}}
        <div class="lg:col-span-2 space-y-3 sm:space-y-6">

            {{-- HERO --}}
            <div class="bg-gray-800 rounded-2xl p-4 sm:p-8 border border-gray-700 shadow-2xl">
                <div class="flex items-center gap-3 sm:gap-6">
                    @if ($nomination->profile_image)
                        <img src="{{ asset('storage/' . $nomination->profile_image) }}"
                             class="w-16 h-16 sm:w-32 sm:h-32 rounded-2xl sm:rounded-full object-cover border-2 sm:border-4 border-gray-700 shadow-xl shrink-0">
                    @else
                        <div class="w-16 h-16 sm:w-32 sm:h-32 rounded-2xl sm:rounded-full bg-gray-900 border-2 sm:border-4 border-gray-700 flex items-center justify-center text-gray-500 font-bold text-2xl sm:text-5xl shrink-0">
                            {{ substr($nomination->name, 0, 1) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="inline-block px-2 py-0.5 sm:px-3 sm:py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-[10px] sm:text-xs font-mono font-bold mb-1 sm:mb-2">
                            {{ $nomination->code }}
                        </div>
                        <h1 class="text-lg sm:text-4xl font-bold text-white leading-tight">
                            {{ $nomination->name }}
                        </h1>
                        <p class="text-xs sm:text-lg text-green-400 font-medium mt-0.5 sm:mt-1 truncate">
                            {{ $nomination->company_or_show ?? 'Independent Candidate' }}
                        </p>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="mt-4 sm:mt-6 grid grid-cols-3 gap-2 sm:gap-3">
                    <div class="rounded-xl bg-gray-900/60 border border-gray-700/60 p-2.5 sm:p-3 text-center">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Votes</div>
                        <div class="text-lg sm:text-2xl font-mono font-bold text-white leading-none">
                            {{ number_format((int) $nomination->total_votes) }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-gray-900/60 border border-gray-700/60 p-2.5 sm:p-3 text-center">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Rank</div>
                        <div class="text-lg sm:text-2xl font-mono font-bold text-yellow-500 leading-none">
                            #{{ $this->rank }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-gray-900/60 border border-gray-700/60 p-2.5 sm:p-3 text-center">
                        <div class="text-[9px] sm:text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Share</div>
                        <div class="text-lg sm:text-2xl font-mono font-bold text-green-400 leading-none">
                            {{ $this->percent }}<span class="text-xs sm:text-sm">%</span>
                        </div>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mt-3 sm:mt-4">
                    <div class="flex items-center justify-between text-[10px] sm:text-xs text-gray-500 mb-1.5">
                        <span class="truncate">{{ $nomination->category->name }}</span>
                        <span class="font-mono shrink-0 ml-2">{{ number_format((int) $nomination->total_votes) }} / {{ number_format($this->categoryTotal) }}</span>
                    </div>
                    <div class="w-full bg-gray-900 rounded-full h-2 sm:h-2.5 overflow-hidden border border-gray-700/50">
                        <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 to-yellow-400 transition-all duration-1000 ease-out"
                             style="width: {{ max($this->percent, 1) }}%"></div>
                    </div>
                </div>

                {{-- Price hint (mobile + desktop) --}}
                <p class="mt-4 text-center text-[10px] sm:text-xs text-gray-500">
                    From <span class="text-yellow-500 font-bold font-mono">{{ number_format($costPerVote, 2) }} KES</span> per vote · M-PESA
                </p>
            </div>

            {{-- BIO --}}
            @if ($nomination->bio)
                <div class="bg-gray-800 rounded-2xl p-4 sm:p-6 border border-gray-700">
                    <h2 class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-bold mb-2">About</h2>
                    <p class="text-xs sm:text-sm text-gray-400 leading-relaxed">{{ $nomination->bio }}</p>
                </div>
            @endif

            {{-- SOCIALS --}}
            @php
                $socials = collect([
                    'facebook_url'  => ['Facebook',  'M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z'],
                    'instagram_url' => ['Instagram', 'M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z'],
                    'twitter_url'   => ['X',         'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                    'tiktok_url'    => ['TikTok',    'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
                    'youtube_url'   => ['YouTube',   'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                    'website_url'   => ['Website',   'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z'],
                ]);
                $activeSocials = $socials->filter(fn ($_, $key) => !empty($nomination->$key));
            @endphp

            @if ($activeSocials->count() > 0)
                <div class="bg-gray-800 rounded-2xl p-4 sm:p-6 border border-gray-700">
                    <h2 class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-bold mb-3">Follow {{ $nomination->name }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeSocials as $key => [$label, $path])
                            <a href="{{ $nomination->$key }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-900 border border-gray-700 hover:border-gray-500 text-xs font-semibold text-gray-300 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- SHARE --}}
            <div class="bg-gray-800 rounded-2xl p-4 sm:p-6 border border-gray-700"
                 x-data="{
                     copied: false,
                     async shareNative() {
                         const data = { title: @js($nomination->name), text: @js($this->shareText), url: @js($this->shareUrl) };
                         if (navigator.share) { try { await navigator.share(data); } catch (e) {} }
                         else { this.copyLink(); }
                     },
                     copyLink() {
                         const url = @js($this->shareUrl);
                         if (navigator.clipboard && window.isSecureContext) {
                             navigator.clipboard.writeText(url).then(() => this.flashCopied());
                         } else {
                             const ta = document.createElement('textarea');
                             ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
                             document.body.appendChild(ta); ta.select();
                             document.execCommand('copy'); document.body.removeChild(ta);
                             this.flashCopied();
                         }
                     },
                     flashCopied() { this.copied = true; setTimeout(() => this.copied = false, 2000); }
                 }">
                <h3 class="text-sm sm:text-base font-bold text-white">Share {{ $nomination->name }}</h3>
                <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 mb-3">Every share counts.</p>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" x-on:click="shareNative()"
                            class="col-span-3 flex items-center justify-center gap-2 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold text-xs py-2.5 transition-colors">
                        <flux:icon.arrow-up-tray class="w-4 h-4" />
                        Share
                    </button>

                    <a href="https://wa.me/?text={{ $encText }}%20{{ $encUrl }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:bg-[#1eb955] text-white font-bold text-xs py-2.5 transition-colors">
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encUrl }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-[#1877F2] hover:bg-[#0d65d9] text-white font-bold text-xs py-2.5 transition-colors">
                        Facebook
                    </a>
                    <button type="button" x-on:click="copyLink()"
                            class="flex items-center justify-center gap-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-white font-bold text-xs py-2.5 transition-colors border border-gray-600">
                        <template x-if="!copied">
                            <span class="flex items-center gap-1.5">
                                <flux:icon.link class="w-3.5 h-3.5" />
                                Copy
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-1.5 text-green-400">
                                <flux:icon.check class="w-3.5 h-3.5" />
                                Copied
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════ RIGHT: FORM (sidebar on desktop, bottom sheet on mobile) ═══════════ --}}
        <div class="{{ $showModal ? 'block' : 'hidden' }} lg:block lg:col-span-1
                    fixed lg:static inset-x-0 bottom-0 lg:inset-auto
                    z-[100] lg:z-auto">

            <div class="vote-form-panel {{ $showModal ? 'show-mobile' : '' }}
                        lg:sticky lg:top-24
                        bg-gray-800 lg:rounded-2xl
                        rounded-t-3xl lg:border border-t border-x lg:border-gray-700 border-gray-700
                        shadow-2xl
                        max-h-[92vh] lg:max-h-none
                        overflow-y-auto lg:overflow-visible">

                {{-- Mobile grabber + close --}}
                <div class="lg:hidden pt-2.5 pb-1 flex justify-center relative shrink-0">
                    <div class="w-10 h-1 rounded-full bg-gray-600"></div>
                    <button type="button" wire:click="closeModal"
                            class="absolute right-3 top-1.5 p-1.5 text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Desktop header --}}
                <div class="hidden lg:block px-6 py-4 border-b border-gray-700 bg-gray-900/80 rounded-t-2xl">
                    <h3 class="text-base font-bold text-white">Cast your vote</h3>
                    <p class="text-xs text-gray-400 mt-0.5">M-PESA · instant confirmation</p>
                </div>

                {{-- Live stats strip --}}
                <div class="px-4 lg:px-6 py-3 lg:py-4 border-b border-gray-700/60 bg-gray-900/40">
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Total votes</div>
                            <div class="font-mono font-bold text-white text-sm lg:text-base">{{ number_format((int) $nomination->total_votes) }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Rank</div>
                            <div class="font-mono font-bold text-yellow-500 text-sm lg:text-base">#{{ $this->rank }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mb-0.5">Share</div>
                            <div class="font-mono font-bold text-green-400 text-sm lg:text-base">{{ $this->percent }}%</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6"
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
                                    <label class="block text-xs font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-xs">🇰🇪</span>
                                        </div>
                                        <input type="tel" wire:model="phone_number" placeholder="0712345678" inputmode="tel"
                                               class="block w-full pl-9 pr-3 py-2.5 bg-gray-900 border border-gray-600 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    @error('phone_number') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1">Amount (KES)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold text-xs">KES</span>
                                        </div>
                                        <input type="number" wire:model.live="amount" min="{{ $costPerVote }}" step="1" inputmode="numeric"
                                               class="block w-full pl-12 pr-3 py-2.5 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-base focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    @error('amount') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror

                                    {{-- Single-line preview --}}
                                    <div class="mt-3 flex items-center gap-2 px-3 py-2 bg-green-500/10 border border-green-500/20 rounded-lg text-xs text-green-400 whitespace-nowrap overflow-hidden">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                                        </svg>
                                        <span class="truncate">
                                            Casting
                                            <strong class="font-mono text-base" x-text="Math.max(0, Math.floor($wire.amount / $wire.costPerVote) || 0)"></strong>
                                            votes
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
                        <div class="flex flex-col items-center justify-center text-center py-4">
                            <div class="relative w-24 h-24 mb-4">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-gray-700"/>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="8" class="text-green-500 transition-all duration-1000 ease-linear"
                                            x-bind:stroke-dasharray="circumference"
                                            x-bind:stroke-dashoffset="strokeDashoffset"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-mono font-bold text-white" x-text="timer"></span>
                                </div>
                            </div>
                            <h3 class="text-base font-bold text-white mb-1">Check Your Phone</h3>
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
                        <div class="flex flex-col items-center justify-center text-center py-4">
                            <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mb-4 border border-green-500/50">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2">Vote Confirmed!</h3>
                            <p class="text-xs text-gray-300 mb-4 bg-gray-900 p-3 rounded-xl border border-gray-700">
                                Thanks for supporting <strong>{{ $nomination->name }}</strong>!
                            </p>
                            <button wire:click="resetVote"
                                    class="w-full px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-lg border border-gray-600">
                                Vote Again
                            </button>
                        </div>
                    @endif

                    {{-- FAILED --}}
                    @if ($paymentStatus === 'failed')
                        <div class="flex flex-col items-center justify-center text-center py-4">
                            <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-4 border border-red-500/50">
                                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-white mb-1.5">Vote Failed</h3>
                            <p class="text-red-400 text-xs mb-4">{{ $paymentMessage }}</p>
                            <button wire:click="resetVote"
                                    class="w-full px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-lg border border-gray-600">
                                Try Again
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- OTHER NOMINEES --}}
    @if ($this->otherNominees->count() > 0)
        <div class="mt-8 sm:mt-12 pt-6 sm:pt-10 border-t border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm sm:text-xl font-bold text-white">Others in {{ $nomination->category->name }}</h3>
                <a href="{{ route('polls.category', $nomination->category->slug) }}" wire:navigate
                   class="text-xs sm:text-sm font-semibold text-yellow-500 hover:text-yellow-400 transition-colors">
                    View all →
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4">
                @foreach ($this->otherNominees as $other)
                    <a href="{{ route('polls.vote', $other->code) }}" wire:navigate
                       wire:key="other-{{ $other->id }}"
                       class="group bg-gray-800/50 hover:bg-gray-800 rounded-xl p-3 sm:p-4 border border-gray-700/50 hover:border-gray-600 transition-all">
                        @if ($other->profile_image)
                            <img src="{{ asset('storage/' . $other->profile_image) }}"
                                 class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover border border-gray-600 mx-auto mb-2 sm:mb-3">
                        @else
                            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-500 font-bold text-base sm:text-xl mx-auto mb-2 sm:mb-3">
                                {{ substr($other->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="text-xs sm:text-sm font-bold text-white text-center truncate group-hover:text-yellow-500 transition-colors">
                            {{ $other->name }}
                        </div>
                        <div class="text-[10px] sm:text-xs text-gray-400 text-center font-mono mt-0.5">
                            {{ number_format((int) $other->total_votes) }} votes
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- MOBILE STICKY BAR (hidden when modal open) --}}
@if (! $showModal)
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-gray-900/95 backdrop-blur-xl border-t border-gray-800"
         style="padding-bottom: max(env(safe-area-inset-bottom), 0px);">
        <div class="px-3 py-2.5">
            <button type="button" wire:click="openVoteModal"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 active:bg-yellow-600 text-gray-900 font-bold text-sm transition-all shadow-lg shadow-yellow-500/30">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
                <span class="truncate">Vote for {{ $nomination->name }}</span>
            </button>
        </div>
    </div>
@endif
</div>
