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

    // M-PESA Form State
    public string $phone_number = '';
    public float $amount = 50;
    public float $costPerVote = 10;

    // Payment State
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
    public function rank(): ?int
    {
        $rank = Nomination::where('nomination_category_id', $this->nomination->nomination_category_id)
            ->where('is_active', true)
            ->where('total_votes', '>', $this->nomination->total_votes)
            ->count();

        return $rank + 1;
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
        return $total > 0
            ? round(($this->nomination->total_votes / $total) * 100, 1)
            : 0.0;
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

    public function triggerMpesaVote(MpesaService $mpesa): void
    {
        $this->validate();
        $this->paymentStatus = 'initiating';
        $this->paymentMessage = 'Sending STK Push...';

        $reference = 'VOTE_' . $this->nomination->code;

        try {
            $response = $mpesa->stkPush($this->phone_number, $this->amount, $reference);

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
    $shareUrl  = $this->shareUrl;
    $shareText = $this->shareText;

    $encUrl  = urlencode($shareUrl);
    $encText = urlencode($shareText);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">

    {{-- Breadcrumb --}}
    <a href="{{ route('polls.category', $nomination->category->slug) }}" wire:navigate
       class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-yellow-500 mb-6 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to {{ $nomination->category->name }}
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

        {{-- ===== LEFT: Profile ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Hero card --}}
            <div class="bg-gray-800 rounded-2xl p-6 sm:p-8 border border-gray-700 shadow-2xl">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8">
                    @if ($nomination->profile_image)
                        <img src="{{ asset('storage/' . $nomination->profile_image) }}"
                             class="w-32 h-32 sm:w-40 sm:h-40 rounded-full object-cover border-4 border-gray-700 shadow-xl shrink-0">
                    @else
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-gray-900 border-4 border-gray-700 flex items-center justify-center text-gray-500 font-bold text-5xl shrink-0">
                            {{ substr($nomination->name, 0, 1) }}
                        </div>
                    @endif

                    <div class="text-center sm:text-left flex-grow min-w-0">
                        <div class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-xs font-mono font-bold mb-3">
                            CODE: {{ $nomination->code }}
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">{{ $nomination->name }}</h1>
                        <p class="text-base sm:text-lg text-green-400 font-medium mb-4">
                            {{ $nomination->company_or_show ?? 'Independent Candidate' }}
                        </p>

                        @if ($nomination->bio)
                            <p class="text-gray-400 text-sm leading-relaxed">{{ $nomination->bio }}</p>
                        @endif
                    </div>
                </div>

                {{-- Social links (if present) --}}
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
                    <div class="mt-6 pt-6 border-t border-gray-700 flex flex-wrap gap-2">
                        @foreach ($activeSocials as $key => [$label, $path])
                            <a href="{{ $nomination->$key }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-900 border border-gray-700 hover:border-gray-500 text-xs font-semibold text-gray-300 hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="{{ $path }}"/>
                                </svg>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ===== SHARE SECTION ===== --}}
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl"
                 x-data="{
                     copied: false,
                     async shareNative() {
                         const data = {
                             title: @js($nomination->name),
                             text: @js($shareText),
                             url: @js($shareUrl),
                         };
                         if (navigator.share) {
                             try {
                                 await navigator.share(data);
                             } catch (e) {
                                 // User cancelled — do nothing
                             }
                         } else {
                             this.copyLink();
                         }
                     },
                     copyLink() {
                         const url = @js($shareUrl);
                         if (navigator.clipboard && window.isSecureContext) {
                             navigator.clipboard.writeText(url).then(() => this.flashCopied());
                         } else {
                             // Fallback for non-HTTPS / older browsers
                             const ta = document.createElement('textarea');
                             ta.value = url;
                             ta.style.position = 'fixed';
                             ta.style.opacity = '0';
                             document.body.appendChild(ta);
                             ta.select();
                             document.execCommand('copy');
                             document.body.removeChild(ta);
                             this.flashCopied();
                         }
                     },
                     flashCopied() {
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2000);
                     }
                 }">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-white">Share {{ $nomination->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Help them win by spreading the word.</p>
                    </div>
                    <span class="hidden sm:inline-flex text-[10px] font-bold uppercase tracking-wider text-gray-500">
                        Every share counts
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">

                    {{-- Native share (device) — highlighted --}}
                    <button type="button" x-on:click="shareNative()"
                            class="col-span-2 sm:col-span-1 flex items-center justify-center gap-2 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold text-sm py-3 px-4 transition-colors shadow-lg shadow-yellow-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Share
                    </button>

                    {{-- WhatsApp --}}
                    <a href="https://wa.me/?text={{ $encText }}%20{{ $encUrl }}"
                       target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:bg-[#1eb955] text-white font-bold text-sm py-3 px-4 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        WhatsApp
                    </a>

                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $encUrl }}"
                       target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-[#1877F2] hover:bg-[#0d65d9] text-white font-bold text-sm py-3 px-4 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                        </svg>
                        Facebook
                    </a>

                    {{-- X / Twitter --}}
                    <a href="https://twitter.com/intent/tweet?text={{ $encText }}&url={{ $encUrl }}"
                       target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-black hover:bg-gray-800 text-white font-bold text-sm py-3 px-4 transition-colors border border-gray-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        X
                    </a>

                    {{-- Copy link --}}
                    <button type="button" x-on:click="copyLink()"
                            class="flex items-center justify-center gap-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-white font-bold text-sm py-3 px-4 transition-colors border border-gray-600">
                        <template x-if="!copied">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copy
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-2 text-green-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Copied
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Voting Terminal ===== --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl overflow-hidden sticky top-24">

                {{-- Vote count --}}
                <div class="bg-gray-900/80 p-6 border-b border-gray-700 text-center">
                    <div class="text-4xl font-mono font-bold text-white mb-1">
                        {{ number_format((int) $nomination->total_votes) }}
                    </div>
                    <div class="text-sm text-gray-500 uppercase tracking-widest font-bold">Total Votes</div>
                    <div class="mt-3 flex items-center justify-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 font-bold">
                            Rank #{{ $this->rank }}
                        </span>
                        <span class="text-gray-400 font-mono">{{ $this->percent }}% of category</span>
                    </div>
                </div>

                {{-- Terminal --}}
                <div class="p-6 min-h-[340px] flex flex-col justify-center"
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
                            <h3 class="text-lg font-bold text-white mb-4">Support {{ $nomination->name }}</h3>

                            <div class="space-y-4">
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
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Amount (KES)</label>
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
                                            Casting
                                            <strong class="font-mono text-lg" x-text="Math.max(0, Math.floor($wire.amount / $wire.costPerVote) || 0)"></strong>
                                            votes!
                                            <span class="text-gray-500 text-xs ml-1 block sm:inline">
                                                ({{ number_format($costPerVote, 2) }} KES / vote)
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" wire:loading.attr="disabled"
                                    class="mt-6 w-full flex items-center justify-center px-6 py-4 rounded-xl shadow-lg text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="triggerMpesaVote">Vote via M-PESA</span>
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
                        <div class="flex flex-col items-center justify-center text-center">
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
                            <p class="text-gray-400 text-sm">Enter your M-PESA PIN to confirm.</p>
                            <p class="text-green-500 text-xs mt-4 font-bold animate-pulse">Waiting for Safaricom...</p>

                            <button wire:click="checkPaymentStatus" wire:loading.attr="disabled"
                                    class="mt-6 text-xs font-bold text-yellow-500 hover:text-yellow-400 underline disabled:opacity-50">
                                Already paid? Check status
                            </button>
                        </div>
                    @endif

                    {{-- State 3: SUCCESS --}}
                    @if ($paymentStatus === 'success')
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mb-5 border border-green-500/50 shadow-[0_0_30px_rgba(34,197,94,0.3)]">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">Vote Confirmed!</h3>
                            <p class="text-gray-300 text-sm mb-6 bg-gray-900 p-4 rounded-xl border border-gray-700">
                                Thanks for supporting <strong>{{ $nomination->name }}</strong>!
                            </p>
                            <button wire:click="resetVote"
                                    class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition-colors w-full border border-gray-600">
                                Vote Again
                            </button>
                        </div>
                    @endif

                    {{-- State 4: FAILED --}}
                    @if ($paymentStatus === 'failed')
                        <div class="flex flex-col items-center justify-center text-center">
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

    {{-- Other nominees strip --}}
    @if ($this->otherNominees->count() > 0)
        <div class="mt-12 pt-10 border-t border-gray-700">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-white">Others in {{ $nomination->category->name }}</h3>
                <a href="{{ route('polls.category', $nomination->category->slug) }}" wire:navigate
                   class="text-sm font-semibold text-yellow-500 hover:text-yellow-400 transition-colors">
                    View all →
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($this->otherNominees as $other)
                    <a href="{{ route('polls.vote', $other->code) }}" wire:navigate
                       wire:key="other-{{ $other->id }}"
                       class="group bg-gray-800/50 hover:bg-gray-800 rounded-xl p-4 border border-gray-700/50 hover:border-gray-600 transition-all">
                        @if ($other->profile_image)
                            <img src="{{ asset('storage/' . $other->profile_image) }}"
                                 class="w-16 h-16 rounded-full object-cover border border-gray-600 mx-auto mb-3 group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-500 font-bold text-xl mx-auto mb-3 group-hover:scale-105 transition-transform">
                                {{ substr($other->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="text-sm font-bold text-white text-center truncate group-hover:text-yellow-500 transition-colors">
                            {{ $other->name }}
                        </div>
                        <div class="text-xs text-gray-400 text-center font-mono mt-1">
                            {{ number_format((int) $other->total_votes) }} votes
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
