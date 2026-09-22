<?php

use App\Models\Nomination;
use App\Models\Transaction;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.guest.portal')] class extends Component {
    use WithPagination, WithFileUploads;

    private const WITHDRAW_MAX      = 5000;
    private const WITHDRAW_MIN      = 10;
    private const WITHDRAW_COOLDOWN = 30;

    public bool $isAuthenticated = false;

    #[Validate('required|string')]
    public string $code = '';

    #[Validate('required|string')]
    public string $pin = '';

    public string $loginError = '';
    public ?int $nominationId = null;

    public string $activeTab = 'overview';

    public string $prof_name = '';
    public string $prof_company = '';
    public string $prof_bio = '';
    public string $prof_facebook = '';
    public string $prof_instagram = '';
    public string $prof_twitter = '';
    public string $prof_tiktok = '';
    public string $prof_youtube = '';
    public string $prof_website = '';
    public $prof_image = null;
    public ?string $existing_image = null;

    public string $withdraw_phone = '';
    public float $withdraw_amount = 500;
    public bool $withdrawDone = false;
    public string $withdrawReceipt = '';

    public function login(): void
    {
        $this->validate();
        $this->loginError = '';

        $nominee = Nomination::where('code', strtoupper($this->code))
            ->where('access_pin', $this->pin)
            ->whereNotNull('access_pin')
            ->first();

        if (! $nominee) {
            $this->loginError = 'Invalid Voting Code or PIN.';
            return;
        }

        $this->nominationId = $nominee->id;
        $this->isAuthenticated = true;
        $this->activeTab = 'overview';
        $this->hydrateProfileForm($nominee);
    }

    public function logout(): void
    {
        $this->reset();
    }

    protected function hydrateProfileForm(Nomination $nominee): void
    {
        $this->prof_name      = $nominee->name ?? '';
        $this->prof_company   = $nominee->company_or_show ?? '';
        $this->prof_bio       = $nominee->bio ?? '';
        $this->prof_facebook  = $nominee->facebook_url ?? '';
        $this->prof_instagram = $nominee->instagram_url ?? '';
        $this->prof_twitter   = $nominee->twitter_url ?? '';
        $this->prof_tiktok    = $nominee->tiktok_url ?? '';
        $this->prof_youtube   = $nominee->youtube_url ?? '';
        $this->prof_website   = $nominee->website_url ?? '';
        $this->existing_image = $nominee->profile_image;
        $this->prof_image = null;
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'profile', 'withdraw'], true)) return;
        $this->activeTab = $tab;
        $this->resetValidation();

        if ($tab === 'withdraw' && $this->nominee) {
            if (! $this->withdraw_phone && $this->nominee->last_payout_phone) {
                $this->withdraw_phone = $this->nominee->last_payout_phone;
            }
        }
    }

    protected function profileRules(): array
    {
        return [
            'prof_name'      => 'required|string|min:3|max:255',
            'prof_company'   => 'nullable|string|max:255',
            'prof_bio'       => 'nullable|string|max:1000',
            'prof_facebook'  => 'nullable|url|max:255',
            'prof_instagram' => 'nullable|url|max:255',
            'prof_twitter'   => 'nullable|url|max:255',
            'prof_tiktok'    => 'nullable|url|max:255',
            'prof_youtube'   => 'nullable|url|max:255',
            'prof_website'   => 'nullable|url|max:255',
            'prof_image'     => 'nullable|image|max:2048',
        ];
    }

    public function removeProfileImage(): void
    {
        $this->prof_image = null;
    }

    public function removeExistingImage(): void
    {
        $this->existing_image = null;
    }

    public function saveProfile(): void
    {
        $this->validate($this->profileRules());

        $nominee = Nomination::findOrFail($this->nominationId);
        $imagePath = $this->existing_image;

        if ($this->prof_image) {
            if ($this->existing_image) {
                Storage::disk('public')->delete($this->existing_image);
            }
            $imagePath = $this->prof_image->store('nominations', 'public');
        } elseif (! $this->existing_image && $nominee->profile_image) {
            Storage::disk('public')->delete($nominee->profile_image);
            $imagePath = null;
        }

        $nominee->update([
            'name'            => $this->prof_name,
            'company_or_show' => $this->prof_company ?: null,
            'bio'             => $this->prof_bio ?: null,
            'facebook_url'    => $this->prof_facebook ?: null,
            'instagram_url'   => $this->prof_instagram ?: null,
            'twitter_url'     => $this->prof_twitter ?: null,
            'tiktok_url'      => $this->prof_tiktok ?: null,
            'youtube_url'     => $this->prof_youtube ?: null,
            'website_url'     => $this->prof_website ?: null,
            'profile_image'   => $imagePath,
        ]);

        $this->existing_image = $imagePath;
        $this->prof_image = null;

        unset($this->nominee);
        session()->flash('profile_saved', 'Profile updated successfully.');
    }

    protected function withdrawRules(): array
    {
        $max = min(self::WITHDRAW_MAX, (float) ($this->nominee?->kes_balance ?? 0));

        return [
            'withdraw_phone'  => 'required|string|min:10|max:15',
            'withdraw_amount' => 'required|numeric|min:' . self::WITHDRAW_MIN . '|max:' . $max,
        ];
    }

    public function requestWithdrawal(MpesaService $mpesa): void
    {
        if (! $this->nominee) return;

        $this->withdrawDone = false;
        $this->resetValidation();

        $nominee = Nomination::find($this->nominationId);
        if (! $nominee) {
            $this->addError('withdraw_amount', 'Account not found.');
            return;
        }

        $this->validate($this->withdrawRules());

        $last = Transaction::where('nomination_id', $nominee->id)
            ->where('type', 'b2c_withdrawal')
            ->whereIn('status', ['pending', 'completed'])
            ->latest()
            ->first();

        if ($last && $last->created_at->gt(now()->subMinutes(self::WITHDRAW_COOLDOWN))) {
            $mins = now()->diffInMinutes($last->created_at->addMinutes(self::WITHDRAW_COOLDOWN), false);
            $this->addError('withdraw_amount',
                "You can withdraw again in about {$mins} minute" . ($mins === 1 ? '' : 's') . '.');
            return;
        }

        if ($this->withdraw_amount > $nominee->kes_balance) {
            $this->addError('withdraw_amount', 'Insufficient balance.');
            return;
        }

        try {
            $response = $mpesa->withdrawB2C(
                $this->withdraw_phone,
                $this->withdraw_amount,
                'Nominee self-withdrawal – ' . $nominee->code
            );

            if (! isset($response['ConversationID'])) {
                $this->addError('withdraw_amount', 'M-PESA could not initiate the payout. Try again shortly.');
                return;
            }

            DB::transaction(function () use ($nominee, $response) {
                $locked = Nomination::lockForUpdate()->find($nominee->id);

                if ($locked->kes_balance < $this->withdraw_amount) {
                    throw new \RuntimeException('Balance changed, please try again.');
                }

                Transaction::create([
                    'type'                => 'b2c_withdrawal',
                    'phone_number'        => $this->withdraw_phone,
                    'amount'              => $this->withdraw_amount,
                    'nomination_id'       => $locked->id,
                    'merchant_request_id' => $response['OriginatorConversationID'] ?? null,
                    'checkout_request_id' => $response['ConversationID'],
                    'status'              => 'pending',
                ]);

                $locked->decrement('kes_balance', $this->withdraw_amount);
                $locked->update(['last_payout_phone' => $this->withdraw_phone]);
            });

            $this->withdrawDone = true;
            $this->withdrawReceipt = $response['ConversationID'];
            $this->withdraw_amount = min(500, (float) $nominee->fresh()->kes_balance);

            unset($this->nominee, $this->lastWithdrawal, $this->canWithdraw, $this->nextWithdrawalAt, $this->withdrawalHistory);

        } catch (\RuntimeException $e) {
            $this->addError('withdraw_amount', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Nominee withdrawal failed', ['nominee' => $nominee->id, 'error' => $e->getMessage()]);
            $this->addError('withdraw_amount', 'System error. Please try again or contact support.');
        }
    }

    #[Computed]
    public function nominee(): ?Nomination
    {
        if (! $this->nominationId) return null;
        return Nomination::with('category')->find($this->nominationId);
    }

    #[Computed]
    public function transactions()
    {
        if (! $this->nominationId) return collect();

        return Transaction::where('nomination_id', $this->nominationId)
            ->where('status', 'completed')
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function withdrawalHistory()
    {
        if (! $this->nominationId) return collect();

        return Transaction::where('nomination_id', $this->nominationId)
            ->where('type', 'b2c_withdrawal')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function lastWithdrawal(): ?Transaction
    {
        if (! $this->nominationId) return null;

        return Transaction::where('nomination_id', $this->nominationId)
            ->where('type', 'b2c_withdrawal')
            ->whereIn('status', ['pending', 'completed'])
            ->latest()
            ->first();
    }

    #[Computed]
    public function nextWithdrawalAt(): ?\Carbon\CarbonInterface
    {
        $last = $this->lastWithdrawal;
        return $last ? $last->created_at->addMinutes(self::WITHDRAW_COOLDOWN) : null;
    }

    #[Computed]
    public function canWithdraw(): bool
    {
        $next = $this->nextWithdrawalAt;
        return ! $next || $next->isPast();
    }

    #[Computed]
    public function voteUrl(): string
    {
        $code = $this->nominee?->code;
        return $code ? route('polls.vote', $code) : '';
    }

    public function maskPhone(?string $phone): string
    {
        if (! $phone) return '—';
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) < 8) return str_repeat('•', max(3, strlen($digits)));

        return substr($digits, 0, 4) . '***' . substr($digits, -3);
    }

    public function withdrawalCap(): int
    {
        return self::WITHDRAW_MAX;
    }

    public function cooldownMinutes(): int
    {
        return self::WITHDRAW_COOLDOWN;
    }
};
?>

@php
    $shareUrl  = $isAuthenticated ? $this->voteUrl : '';
    $shareText = $isAuthenticated
        ? 'Vote for ' . ($this->nominee?->name ?? '') . ' on Go Getter Africa!'
        : '';

    $tabs = [
        'overview' => ['Overview', 'chart-bar'],
        'profile'  => ['Profile',  'user-circle'],
        'withdraw' => ['Withdraw', 'banknotes'],
    ];
@endphp

<div class="min-h-screen bg-gradient-to-b from-gray-900 to-gray-950 text-white">

    {{-- ═══════════════ LOGIN ═══════════════ --}}
    @if (! $isAuthenticated)
        <div class="min-h-screen flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-sm">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-red-500/10 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-red-500/20">
                        <flux:icon.lock-closed class="w-7 h-7 text-red-500" />
                    </div>
                    <h1 class="text-2xl font-bold text-white">Nominee Portal</h1>
                    <p class="text-xs text-gray-400 mt-1.5">Sign in to manage your campaign.</p>
                </div>

                <div class="bg-gray-800/60 backdrop-blur rounded-2xl border border-gray-700 p-5 shadow-2xl">
                    <form wire:submit="login" class="space-y-4">
                        @if ($loginError)
                            <div class="p-2.5 bg-red-500/10 border border-red-500/20 rounded-lg text-xs text-red-400 text-center font-medium">
                                {{ $loginError }}
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Voting Code</label>
                            <input type="text" wire:model="code" placeholder="NOM-FX123"
                                   class="block w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-white text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">PIN</label>
                            <input type="password" wire:model="pin" placeholder="••••"
                                   class="block w-full px-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-white text-sm font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-all">
                            <span wire:loading.remove wire:target="login">Sign In</span>
                            <span wire:loading wire:target="login">Verifying…</span>
                        </button>
                    </form>
                </div>

                <p class="text-[10px] text-gray-600 text-center mt-6">
                    <a href="/" class="hover:text-gray-400">← Back to site</a>
                </p>
            </div>
        </div>

    {{-- ═══════════════ AUTHENTICATED ═══════════════ --}}
    @else

        {{-- Sticky header --}}
        <header class="sticky top-0 z-40 bg-gray-900/80 backdrop-blur-xl border-b border-gray-800">
            <div class="px-4 py-3 flex items-center gap-3 max-w-5xl mx-auto">
                @if ($this->nominee?->profile_image)
                    <img src="{{ asset('storage/' . $this->nominee->profile_image) }}"
                         class="w-9 h-9 rounded-full object-cover border border-gray-700 shrink-0">
                @else
                    <div class="w-9 h-9 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-gray-400 font-bold text-sm shrink-0">
                        {{ substr($this->nominee->name ?? '?', 0, 1) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-white truncate leading-tight">
                        {{ $this->nominee->name ?? '' }}
                    </div>
                    <div class="text-[10px] text-gray-500 truncate leading-tight">
                        <span class="font-mono text-yellow-500">{{ $this->nominee->code ?? '' }}</span>
                        · {{ $this->nominee->category->name ?? '' }}
                    </div>
                </div>

                <button wire:click="logout"
                        class="shrink-0 p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors"
                        title="Sign out">
                    <flux:icon.arrow-right-start-on-rectangle class="w-4 h-4" />
                </button>
            </div>

            {{-- ═════ DESKTOP TABS — directly under the header ═════ --}}
            <div class="hidden lg:block border-t border-gray-800/60">
                <nav class="max-w-5xl mx-auto px-4 flex gap-1" aria-label="Tabs">
                    @foreach ($tabs as $key => [$label, $icon])
                        <button wire:click="setTab('{{ $key }}')" type="button"
                                @class([
                                    'inline-flex items-center gap-2 px-4 py-3 text-sm font-bold border-b-2 transition-colors',
                                    'border-red-500 text-white' => $activeTab === $key,
                                    'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-600' => $activeTab !== $key,
                                ])>
                            <flux:icon :name="$icon" class="w-4 h-4" />
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </header>

        {{-- Flash --}}
        @if (session()->has('profile_saved'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="max-w-5xl mx-auto px-4 mt-3">
                <div class="p-3 text-xs text-green-100 bg-green-600/90 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon.check-circle class="w-4 h-4 shrink-0" />
                        {{ session('profile_saved') }}
                    </div>
                    <button @click="show = false" class="text-green-200 hover:text-white">
                        <flux:icon.x-mark class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        @endif

        {{-- Main content --}}
        <main class="px-4 pt-4 pb-32 lg:pb-12 max-w-5xl mx-auto">

            {{-- ═══════ TAB: OVERVIEW ═══════ --}}
            @if ($activeTab === 'overview')

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-gray-800/60 rounded-2xl p-3.5 border border-gray-700/60">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg bg-yellow-500/10 flex items-center justify-center shrink-0">
                                <flux:icon.trophy class="w-3.5 h-3.5 text-yellow-500" />
                            </div>
                            <span class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Votes</span>
                        </div>
                        <div class="text-2xl font-mono font-bold text-white leading-none">
                            {{ number_format((int) ($this->nominee->total_votes ?? 0)) }}
                        </div>
                    </div>

                    <div class="bg-gray-800/60 rounded-2xl p-3.5 border border-gray-700/60">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                                <flux:icon.wallet class="w-3.5 h-3.5 text-green-500" />
                            </div>
                            <span class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Balance</span>
                        </div>
                        <div class="text-2xl font-mono font-bold text-green-400 leading-none">
                            <span class="text-sm text-gray-500">KES</span>
                            {{ number_format((float) ($this->nominee->kes_balance ?? 0), 0) }}
                        </div>
                    </div>
                </div>

                @if ($shareUrl)
                    <div class="bg-gradient-to-br from-red-900/40 to-gray-800/60 rounded-2xl p-4 border border-red-500/20 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-red-500/20 border border-red-500/30 flex items-center justify-center shrink-0">
                                <flux:icon.link class="w-4 h-4 text-red-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] uppercase tracking-wider text-red-300/80 font-bold">Your vote link</div>
                                <div class="text-xs font-mono text-gray-300 truncate">{{ $shareUrl }}</div>
                            </div>

                            <button type="button"
                                    x-data="{
                                        copied: false,
                                        async share() {
                                            const data = {
                                                title: @js($this->nominee->name),
                                                text: @js($shareText),
                                                url: @js($shareUrl),
                                            };
                                            if (navigator.share) {
                                                try { await navigator.share(data); }
                                                catch (e) {}
                                            } else {
                                                await this.copy();
                                            }
                                        },
                                        async copy() {
                                            try {
                                                await navigator.clipboard.writeText(@js($shareUrl));
                                                this.copied = true;
                                                setTimeout(() => this.copied = false, 1800);
                                            } catch (e) {}
                                        }
                                    }"
                                    x-on:click="share()"
                                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-colors">
                                <template x-if="!copied">
                                    <span class="inline-flex items-center gap-1.5">
                                        <flux:icon.arrow-up-tray class="w-3.5 h-3.5" />
                                        Share
                                    </span>
                                </template>
                                <template x-if="copied">
                                    <span class="inline-flex items-center gap-1.5 text-green-200">
                                        <flux:icon.check class="w-3.5 h-3.5" />
                                        Copied
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="bg-gray-800/60 rounded-2xl border border-gray-700/60 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-white">Recent activity</h3>
                        <span class="text-[10px] text-gray-500">Numbers masked</span>
                    </div>

                    @if ($this->transactions->count() > 0)
                        <ul class="divide-y divide-gray-700/40">
                            @foreach ($this->transactions as $txn)
                                <li wire:key="txn-{{ $txn->id }}" class="px-4 py-3 flex items-center gap-3">
                                    <div @class([
                                        'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                                        'bg-green-500/10 text-green-500' => $txn->type === 'stk_push',
                                        'bg-blue-500/10 text-blue-400' => $txn->type !== 'stk_push',
                                    ])>
                                        @if ($txn->type === 'stk_push')
                                            <flux:icon.arrow-trending-up class="w-3.5 h-3.5" />
                                        @else
                                            <flux:icon.arrow-down-tray class="w-3.5 h-3.5" />
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-semibold text-white truncate">
                                            @if ($txn->type === 'stk_push')
                                                Vote from {{ $this->maskPhone($txn->phone_number) }}
                                            @else
                                                {{ Str::headline($txn->type) }}
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-gray-500 truncate">
                                            {{ $txn->created_at->format('M d, H:i') }}
                                            @if ($txn->receipt_number)
                                                · <span class="font-mono">{{ $txn->receipt_number }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div @class([
                                        'text-sm font-mono font-bold shrink-0',
                                        'text-green-400' => $txn->type === 'stk_push',
                                        'text-red-400' => $txn->type !== 'stk_push',
                                    ])>
                                        {{ $txn->type === 'stk_push' ? '+' : '−' }}{{ number_format($txn->amount, 0) }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if ($this->transactions->hasPages())
                            <div class="p-3 border-t border-gray-700/60">
                                {{ $this->transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="py-12 text-center">
                            <flux:icon.document-text class="w-8 h-8 text-gray-600 mx-auto mb-2" />
                            <p class="text-xs text-gray-500">No activity yet.</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ═══════ TAB: PROFILE ═══════ --}}
            @if ($activeTab === 'profile')
                <form wire:submit="saveProfile" class="space-y-4">

                    <div class="bg-gray-800/60 rounded-2xl border border-gray-700/60 p-4">
                        <label class="block text-xs font-medium text-gray-400 mb-3">Profile Photo</label>
                        <div class="flex items-center gap-4">
                            @if ($prof_image)
                                <div class="relative shrink-0">
                                    <img src="{{ $prof_image->temporaryUrl() }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-yellow-500">
                                    <button type="button" wire:click="removeProfileImage"
                                            class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full p-1">
                                        <flux:icon.x-mark class="w-3 h-3" />
                                    </button>
                                </div>
                            @elseif ($existing_image)
                                <div class="relative group shrink-0">
                                    <img src="{{ asset('storage/' . $existing_image) }}" class="w-16 h-16 rounded-2xl object-cover border border-gray-600">
                                    <button type="button" wire:click="removeExistingImage"
                                            class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <flux:icon.x-mark class="w-3 h-3" />
                                    </button>
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-500 font-bold text-2xl shrink-0">
                                    {{ substr($prof_name ?: '?', 0, 1) }}
                                </div>
                            @endif

                            <label for="profile-image-input"
                                   class="flex-1 flex items-center justify-center gap-2 h-16 border-2 border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-900/50 hover:bg-gray-800 hover:border-yellow-500 transition-all">
                                <flux:icon.photo class="w-4 h-4 text-gray-400" />
                                <span class="text-xs text-gray-400">
                                    <span class="font-semibold text-yellow-500">Upload</span> · max 2MB
                                </span>
                                <input id="profile-image-input" type="file" wire:model="prof_image" class="hidden" accept="image/*">
                            </label>
                        </div>
                        @error('prof_image') <span class="text-[11px] text-red-400 mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-gray-800/60 rounded-2xl border border-gray-700/60 p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Display Name *</label>
                            <input type="text" wire:model="prof_name"
                                   class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                            @error('prof_name') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Company / Show</label>
                            <input type="text" wire:model="prof_company" placeholder="e.g. Citizen TV"
                                   class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                            @error('prof_company') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Bio</label>
                            <textarea wire:model.live="prof_bio" rows="3" maxlength="1000"
                                      placeholder="Tell voters why they should support you…"
                                      class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white text-sm px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                            <div class="flex justify-between mt-1">
                                @error('prof_bio') <span class="text-[11px] text-red-400">{{ $message }}</span> @enderror
                                <span class="text-[10px] text-gray-500 ml-auto">{{ strlen($prof_bio) }}/1000</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800/60 rounded-2xl border border-gray-700/60 p-4">
                        <h4 class="text-[10px] font-bold text-gray-400 mb-3 uppercase tracking-wider">Social Links</h4>
                        <div class="space-y-2.5">
                            @foreach ([
                                'prof_facebook'  => 'Facebook',
                                'prof_instagram' => 'Instagram',
                                'prof_twitter'   => 'Twitter / X',
                                'prof_tiktok'    => 'TikTok',
                                'prof_youtube'   => 'YouTube',
                                'prof_website'   => 'Website',
                            ] as $field => $label)
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">{{ $label }}</label>
                                    <input type="url" wire:model="{{ $field }}" placeholder="https://"
                                           class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 text-xs px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    @error($field) <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 py-3 bg-yellow-500 hover:bg-yellow-400 text-gray-900 text-sm font-bold rounded-xl transition-colors disabled:opacity-60">
                        <flux:icon.check class="w-4 h-4" wire:loading.remove wire:target="saveProfile" />
                        <span wire:loading.remove wire:target="saveProfile">Save Changes</span>
                        <span wire:loading wire:target="saveProfile">Saving…</span>
                    </button>
                </form>
            @endif

            {{-- ═══════ TAB: WITHDRAW ═══════ --}}
            @if ($activeTab === 'withdraw')
                @php
                    $balance = (float) ($this->nominee->kes_balance ?? 0);
                    $canWithdraw = $this->canWithdraw;
                    $nextAt = $this->nextWithdrawalAt;
                @endphp

                <div class="bg-gradient-to-br from-green-900/40 via-green-800/20 to-gray-800/60 rounded-2xl border border-green-500/20 p-4 mb-4">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="min-w-0">
                            <div class="text-[10px] uppercase tracking-widest text-green-400 font-bold mb-1">Available</div>
                            <div class="text-3xl font-mono font-bold text-white leading-none">
                                <span class="text-sm text-gray-400">KES</span>
                                {{ number_format($balance, 0) }}
                            </div>
                            <div class="text-[10px] text-gray-500 mt-1 font-mono">
                                {{ number_format($balance, 2) }} exact
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-green-500/20 border border-green-500/30 flex items-center justify-center shrink-0">
                            <flux:icon.arrow-down-tray class="w-5 h-5 text-green-400" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-black/20 px-2.5 py-1.5">
                            <div class="text-[9px] uppercase text-gray-500 font-bold">Max per payout</div>
                            <div class="text-xs font-mono font-bold text-white">KES {{ number_format($this->withdrawalCap()) }}</div>
                        </div>
                        <div class="rounded-lg bg-black/20 px-2.5 py-1.5">
                            <div class="text-[9px] uppercase text-gray-500 font-bold">Cooldown</div>
                            <div class="text-xs font-mono font-bold text-white">{{ $this->cooldownMinutes() }} min</div>
                        </div>
                    </div>

                    @if ($balance > $this->withdrawalCap())
                        <div class="mt-3 text-[11px] text-gray-300 bg-black/30 rounded-lg p-2.5 flex gap-2">
                            <flux:icon.information-circle class="w-3.5 h-3.5 shrink-0 mt-0.5 text-yellow-500" />
                            <span>
                                Withdraw in chunks of KES {{ number_format($this->withdrawalCap()) }},
                                {{ $this->cooldownMinutes() }} min apart. Or
                                <a href="https://wa.me/254710878056" target="_blank" class="text-yellow-500 underline">contact support</a>
                                for a bulk payout.
                            </span>
                        </div>
                    @endif
                </div>

                @if ($withdrawDone)
                    <div class="bg-gray-800/60 rounded-2xl border border-green-500/30 p-6 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-green-500/15 border border-green-500/40 flex items-center justify-center mb-3">
                            <flux:icon.check-circle class="w-7 h-7 text-green-500" />
                        </div>
                        <h3 class="text-base font-bold text-white mb-1">Withdrawal Submitted</h3>
                        <p class="text-xs text-gray-400 mb-3">Funds arrive on your phone within a few minutes.</p>
                        @if ($withdrawReceipt)
                            <div class="text-[10px] font-mono text-gray-500 bg-gray-900/50 rounded-lg px-2.5 py-1.5 inline-block mb-3">
                                Ref: {{ $withdrawReceipt }}
                            </div>
                        @endif
                        <button wire:click="$set('withdrawDone', false)"
                                class="w-full py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-lg border border-gray-600">
                            Close
                        </button>
                    </div>
                @else
                    <form wire:submit="requestWithdrawal" class="bg-gray-800/60 rounded-2xl border border-gray-700/60 p-4 space-y-4">
                        <h3 class="text-sm font-bold text-white">Request withdrawal</h3>

                        @if (! $canWithdraw && $nextAt)
                            <div class="rounded-xl bg-yellow-500/10 border border-yellow-500/20 p-3 text-xs text-yellow-400 flex gap-2"
                                 x-data="{
                                     target: {{ $nextAt->timestamp * 1000 }},
                                     remaining: '',
                                     tick() {
                                         const diff = Math.max(0, this.target - Date.now());
                                         const m = Math.floor(diff / 60000);
                                         const s = Math.floor((diff % 60000) / 1000);
                                         this.remaining = m + 'm ' + String(s).padStart(2, '0') + 's';
                                         if (diff <= 0) location.reload();
                                     },
                                     init() { this.tick(); setInterval(() => this.tick(), 1000); }
                                 }">
                                <flux:icon.clock class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                                <div>
                                    <div class="font-bold">Cooldown active</div>
                                    <div class="text-[10px] opacity-80 mt-0.5">
                                        Next withdrawal in <span class="font-mono font-bold" x-text="remaining"></span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @error('withdraw_amount')
                            <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-3 text-xs text-red-400 flex items-start gap-2">
                                <flux:icon.exclamation-triangle class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">M-PESA Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-xs">🇰🇪</span>
                                </div>
                                <input type="tel" wire:model="withdraw_phone" placeholder="0712345678"
                                       class="block w-full pl-9 pr-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-white text-sm focus:ring-green-500 focus:border-green-500"
                                       {{ ! $canWithdraw ? 'disabled' : '' }}>
                            </div>
                            @error('withdraw_phone') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1">Amount (KES)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-xs font-bold">KES</span>
                                </div>
                                <input type="number" wire:model.live="withdraw_amount"
                                       min="10" max="{{ min($this->withdrawalCap(), $balance) }}" step="1"
                                       class="block w-full pl-12 pr-3 py-2.5 bg-gray-900 border border-gray-600 rounded-lg text-white text-base font-mono focus:ring-green-500 focus:border-green-500"
                                       {{ ! $canWithdraw ? 'disabled' : '' }}>
                            </div>
                            <div class="flex justify-between items-center mt-1.5 text-[10px] text-gray-500">
                                <span>Min 10 · Max {{ number_format(min($this->withdrawalCap(), $balance)) }}</span>
                                <button type="button" wire:click="$set('withdraw_amount', {{ min($this->withdrawalCap(), (int) $balance) }})"
                                        class="text-yellow-500 hover:text-yellow-400 font-bold">Use max</button>
                            </div>
                        </div>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                {{ (! $canWithdraw || $balance < 10) ? 'disabled' : '' }}
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="requestWithdrawal" class="inline-flex items-center gap-2">
                                <flux:icon.arrow-down-tray class="w-4 h-4" />
                                @if ($balance < 10)
                                    Balance too low
                                @elseif (! $canWithdraw)
                                    Cooldown active
                                @else
                                    Withdraw
                                @endif
                            </span>
                            <span wire:loading wire:target="requestWithdrawal" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Processing…
                            </span>
                        </button>
                    </form>
                @endif

                <div class="bg-gray-800/60 rounded-2xl border border-gray-700/60 overflow-hidden mt-4">
                    <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-white">Recent withdrawals</h3>
                        <flux:icon.clock class="w-3.5 h-3.5 text-gray-500" />
                    </div>

                    @if ($this->withdrawalHistory->count() > 0)
                        <ul class="divide-y divide-gray-700/40">
                            @foreach ($this->withdrawalHistory as $w)
                                <li wire:key="wd-{{ $w->id }}" class="px-4 py-3 flex items-center gap-3">
                                    <div @class([
                                        'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                                        'bg-green-500/10 text-green-500' => $w->status === 'completed',
                                        'bg-red-500/10 text-red-500'     => $w->status === 'failed',
                                        'bg-yellow-500/10 text-yellow-500' => $w->status === 'pending',
                                    ])>
                                        @if ($w->status === 'completed')
                                            <flux:icon.check-circle class="w-3.5 h-3.5" />
                                        @elseif ($w->status === 'failed')
                                            <flux:icon.x-circle class="w-3.5 h-3.5" />
                                        @else
                                            <flux:icon.clock class="w-3.5 h-3.5" />
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-semibold text-white truncate">
                                            To {{ $this->maskPhone($w->phone_number) }}
                                        </div>
                                        <div class="text-[10px] text-gray-500 truncate">
                                            {{ $w->created_at->diffForHumans() }} · {{ $w->status }}
                                        </div>
                                    </div>

                                    <div class="text-sm font-mono font-bold text-gray-300 shrink-0">
                                        −{{ number_format($w->amount, 0) }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="py-10 text-center">
                            <flux:icon.clock class="w-7 h-7 text-gray-600 mx-auto mb-2" />
                            <p class="text-xs text-gray-500">No withdrawals yet.</p>
                        </div>
                    @endif
                </div>
            @endif
        </main>

        {{-- ═══════════════ MOBILE FLOATING TAB BAR ═══════════════ --}}
        <nav class="lg:hidden fixed left-1/2 -translate-x-1/2 z-50
                    flex items-center gap-1 p-1.5
                    bg-gray-900/85 backdrop-blur-2xl
                    border border-gray-700/60
                    rounded-full
                    shadow-2xl shadow-black/60"
             style="bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));"
             aria-label="Primary">

            @foreach ($tabs as $key => [$label, $icon])
                <button wire:click="setTab('{{ $key }}')" type="button"
                        @class([
                            'relative flex items-center justify-center rounded-full transition-all duration-300 ease-out',
                            'gap-2 pl-3.5 pr-4 py-2.5 bg-red-600 text-white shadow-lg shadow-red-600/30' => $activeTab === $key,
                            'px-3.5 py-2.5 text-gray-400 hover:text-white active:scale-95' => $activeTab !== $key,
                        ])
                        aria-label="{{ $label }}"
                        @if ($activeTab === $key) aria-current="page" @endif>
                    <flux:icon :name="$icon" class="w-5 h-5 shrink-0" />
                    @if ($activeTab === $key)
                        <span class="text-xs font-bold whitespace-nowrap">{{ $label }}</span>
                    @endif
                </button>
            @endforeach
        </nav>
    @endif
</div>
