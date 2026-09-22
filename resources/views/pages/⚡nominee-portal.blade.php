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
use Illuminate\Support\Str;

new #[Layout('layouts.guest.app')] class extends Component {
    use WithPagination, WithFileUploads;

    // ─── Withdrawal rules ─────────────────────────────
    private const WITHDRAW_MAX      = 5000;    // KES per single withdrawal
    private const WITHDRAW_MIN      = 10;      // KES minimum
    private const WITHDRAW_COOLDOWN = 30;      // minutes between withdrawals

    // ─── Login state ──────────────────────────────────
    public bool $isAuthenticated = false;

    #[Validate('required|string')]
    public string $code = '';

    #[Validate('required|string')]
    public string $pin = '';

    public string $loginError = '';

    public ?int $nominationId = null;

    // ─── Tab state ────────────────────────────────────
    public string $activeTab = 'overview'; // overview | profile | withdraw

    // ─── Profile edit form ────────────────────────────
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

    // ─── Withdraw form ────────────────────────────────
    public string $withdraw_phone = '';
    public float $withdraw_amount = 500;
    public bool $withdrawDone = false;
    public string $withdrawReceipt = '';

    // ─────────────────────────────────────────────────
    // AUTH
    // ─────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────
    // TABS
    // ─────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'profile', 'withdraw'], true)) return;
        $this->activeTab = $tab;
        $this->resetValidation();

        if ($tab === 'withdraw' && $this->nominee) {
            // Pre-fill from last used phone on first open
            if (! $this->withdraw_phone && $this->nominee->last_payout_phone) {
                $this->withdraw_phone = $this->nominee->last_payout_phone;
            }
        }
    }

    // ─────────────────────────────────────────────────
    // PROFILE
    // ─────────────────────────────────────────────────

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
            'name'             => $this->prof_name,
            'company_or_show'  => $this->prof_company ?: null,
            'bio'              => $this->prof_bio ?: null,
            'facebook_url'     => $this->prof_facebook ?: null,
            'instagram_url'    => $this->prof_instagram ?: null,
            'twitter_url'      => $this->prof_twitter ?: null,
            'tiktok_url'       => $this->prof_tiktok ?: null,
            'youtube_url'      => $this->prof_youtube ?: null,
            'website_url'      => $this->prof_website ?: null,
            'profile_image'    => $imagePath,
        ]);

        $this->existing_image = $imagePath;
        $this->prof_image = null;

        $this->dispatch('$refresh');
        session()->flash('profile_saved', 'Profile updated successfully.');
    }

    // ─────────────────────────────────────────────────
    // WITHDRAW
    // ─────────────────────────────────────────────────

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

        // ── Fresh pull: never trust component state for money ──
        $nominee = Nomination::find($this->nominationId);
        if (! $nominee) {
            $this->addError('withdraw_amount', 'Account not found.');
            return;
        }

        $this->validate($this->withdrawRules());

        // ── Cooldown check (server-side, cannot be bypassed) ──
        $last = Transaction::where('nomination_id', $nominee->id)
            ->where('type', 'b2c_withdrawal')
            ->whereIn('status', ['pending', 'completed'])
            ->latest()
            ->first();

        if ($last && $last->created_at->gt(now()->subMinutes(self::WITHDRAW_COOLDOWN))) {
            $mins = now()->diffInMinutes($last->created_at->addMinutes(self::WITHDRAW_COOLDOWN), false);
            $this->addError('withdraw_amount',
                "You can withdraw again in about {$mins} minute" . ($mins === 1 ? '' : 's') .
                ". Please wait before requesting another payout.");
            return;
        }

        // ── Balance check against fresh DB value ──
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

                // Re-verify inside the lock — no race possible now
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
            unset($this->nominee, $this->lastWithdrawal, $this->canWithdraw, $this->nextWithdrawalAt);

        } catch (\RuntimeException $e) {
            $this->addError('withdraw_amount', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Nominee withdrawal failed', ['nominee' => $nominee->id, 'error' => $e->getMessage()]);
            $this->addError('withdraw_amount', 'System error. Please try again or contact support.');
        }
    }

    // ─────────────────────────────────────────────────
    // COMPUTED
    // ─────────────────────────────────────────────────

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
    public function nextWithdrawalAt(): ?\Carbon\Carbon
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

    // ─────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────

    /** Kenyan-law compliant phone mask: 0712***678 */
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
    $maskPhone = fn (?string $p) => $this->maskPhone($p);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ═══════════════ LOGIN ═══════════════ --}}
    @if (! $isAuthenticated)
        <div class="max-w-md mx-auto mt-10">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20 shadow-[0_0_30px_rgba(239,68,68,0.1)]">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white">Nominee Portal</h1>
                <p class="text-gray-400 mt-2">Enter your Voting Code and PIN to manage your campaign.</p>
            </div>

            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl p-8">
                <form wire:submit="login" class="space-y-5">
                    @if ($loginError)
                        <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-400 text-center font-medium">
                            {{ $loginError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Voting Code</label>
                        <input type="text" wire:model="code" placeholder="e.g. NOM-FX123"
                               class="block w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono uppercase focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Access PIN</label>
                        <input type="password" wire:model="pin" placeholder="••••"
                               class="block w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full mt-4 flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-600/20">
                        <span wire:loading.remove wire:target="login">Access Dashboard</span>
                        <span wire:loading wire:target="login">Verifying...</span>
                    </button>
                </form>
            </div>
        </div>

    {{-- ═══════════════ AUTHENTICATED ═══════════════ --}}
    @else

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8 border-b border-gray-700 pb-6">
            <div class="flex items-center gap-4">
                @if ($this->nominee?->profile_image)
                    <img src="{{ asset('storage/' . $this->nominee->profile_image) }}"
                         class="h-14 w-14 rounded-full object-cover border-2 border-gray-600">
                @else
                    <div class="h-14 w-14 rounded-full bg-gray-800 border-2 border-gray-600 flex items-center justify-center text-gray-400 font-bold text-xl">
                        {{ substr($this->nominee->name ?? '?', 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold text-white truncate">{{ $this->nominee->name ?? '' }}</h1>
                    <p class="text-sm text-gray-400">
                        Code <span class="font-mono text-yellow-500">{{ $this->nominee->code ?? '' }}</span>
                        • {{ $this->nominee->category->name ?? '' }}
                    </p>
                </div>
            </div>
            <button wire:click="logout"
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-bold border border-gray-600 transition-colors self-start sm:self-auto">
                Log Out
            </button>
        </div>

        {{-- Flash messages --}}
        @if (session()->has('profile_saved'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="p-4 mb-6 text-sm text-green-900 bg-green-400 rounded-lg flex items-center justify-between border border-green-500">
                <span>{{ session('profile_saved') }}</span>
                <button @click="show = false" class="text-green-900 hover:text-green-800">×</button>
            </div>
        @endif

        {{-- ═══ TABS ═══ --}}
        <div class="mb-6 border-b border-gray-700">
            <nav class="flex gap-1 -mb-px overflow-x-auto" aria-label="Tabs">
                @php
                    $tabs = [
                        'overview' => ['Overview', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3'],
                        'profile'  => ['Edit Profile', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        'withdraw' => ['Withdraw', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp

                @foreach ($tabs as $key => [$label, $icon])
                    <button wire:click="setTab('{{ $key }}')" type="button"
                            class="group inline-flex items-center gap-2 px-4 py-3 text-sm font-bold whitespace-nowrap border-b-2 transition-colors
                                {{ $activeTab === $key
                                    ? 'border-red-500 text-white'
                                    : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-600' }}">
                        <svg class="w-4 h-4 {{ $activeTab === $key ? 'text-red-500' : 'text-gray-500 group-hover:text-gray-400' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/>
                        </svg>
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- ═══════════════ TAB: OVERVIEW ═══════════════ --}}
        @if ($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20 shrink-0">
                        <svg class="w-7 h-7 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Total Votes</div>
                        <div class="text-3xl font-mono font-bold text-white">{{ number_format((int) ($this->nominee->total_votes ?? 0)) }}</div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-green-500/10 flex items-center justify-center border border-green-500/20 shrink-0">
                        <span class="text-xl font-bold text-green-500">KES</span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Commission Balance</div>
                        <div class="text-3xl font-mono font-bold text-green-400">
                            {{ number_format((float) ($this->nominee->kes_balance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Financial Activity</h3>
                    <span class="text-xs text-gray-500">Voter numbers masked per Kenyan data protection rules</span>
                </div>

                @if ($this->transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900/30">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Voter</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach ($this->transactions as $txn)
                                    <tr class="hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ $txn->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($txn->type === 'stk_push')
                                                <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs font-bold rounded border border-green-500/20">Vote Received</span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-xs font-bold rounded border border-blue-500/20">{{ $txn->type }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 font-mono">
                                            {{ $maskPhone($txn->phone_number) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold {{ $txn->type === 'stk_push' ? 'text-green-400' : 'text-red-400' }}">
                                            {{ $txn->type === 'stk_push' ? '+' : '−' }} {{ number_format($txn->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($this->transactions->hasPages())
                        <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                            {{ $this->transactions->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center text-gray-400 text-sm">
                        No financial activity recorded yet.
                    </div>
                @endif
            </div>
        @endif

        {{-- ═══════════════ TAB: PROFILE ═══════════════ --}}
        @if ($activeTab === 'profile')
            <form wire:submit="saveProfile" class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/50">
                    <h3 class="text-lg font-bold text-white">Edit Your Public Profile</h3>
                    <p class="text-xs text-gray-400 mt-1">This is what voters see on your voting page. Keep it fresh.</p>
                </div>

                <div class="p-6 space-y-8">

                    {{-- Photo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Profile Photo</label>
                        <div class="flex items-center gap-5">
                            @if ($prof_image)
                                <div class="relative group shrink-0">
                                    <img src="{{ $prof_image->temporaryUrl() }}"
                                         class="w-24 h-24 rounded-full object-cover border-2 border-yellow-500">
                                    <button type="button" wire:click="removeProfileImage"
                                            class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @elseif ($existing_image)
                                <div class="relative group shrink-0">
                                    <img src="{{ asset('storage/' . $existing_image) }}"
                                         class="w-24 h-24 rounded-full object-cover border border-gray-600">
                                    <button type="button" wire:click="removeExistingImage"
                                            class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="w-24 h-24 rounded-full bg-gray-900 border border-gray-600 flex items-center justify-center text-gray-500 font-bold text-3xl shrink-0">
                                    {{ substr($prof_name ?: '?', 0, 1) }}
                                </div>
                            @endif

                            <div class="flex-1">
                                <label for="profile-image-input"
                                       class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-900 hover:bg-gray-700/50 hover:border-yellow-500 transition-all">
                                    <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">
                                        <span class="font-semibold text-yellow-500">Upload image</span> — max 2MB
                                    </p>
                                    <input id="profile-image-input" type="file" wire:model="prof_image" class="hidden" accept="image/*">
                                </label>
                                <div wire:loading wire:target="prof_image" class="text-xs text-yellow-500 mt-2">Uploading…</div>
                                @error('prof_image') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Name & Company --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 border-t border-gray-700 pt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Display Name *</label>
                            <input type="text" wire:model="prof_name"
                                   class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                            @error('prof_name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Company / Show</label>
                            <input type="text" wire:model="prof_company" placeholder="e.g. Citizen TV"
                                   class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                            @error('prof_company') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div class="border-t border-gray-700 pt-6">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Bio</label>
                        <textarea wire:model="prof_bio" rows="4" maxlength="1000"
                                  placeholder="Tell voters who you are and why they should support you…"
                                  class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5"></textarea>
                        <div class="flex justify-between mt-1">
                            @error('prof_bio') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                            <span class="text-xs text-gray-500 ml-auto" x-data="{len: {{ strlen($prof_bio) }}}"
                                  x-init="$watch('$wire.prof_bio', v => len = v.length)" x-text="len + ' / 1000'"></span>
                        </div>
                    </div>

                    {{-- Socials --}}
                    <div class="border-t border-gray-700 pt-6">
                        <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wider">Social Links</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ([
                                'prof_facebook'  => 'Facebook URL',
                                'prof_instagram' => 'Instagram URL',
                                'prof_twitter'   => 'Twitter / X URL',
                                'prof_tiktok'    => 'TikTok URL',
                                'prof_youtube'   => 'YouTube URL',
                                'prof_website'   => 'Website URL',
                            ] as $field => $placeholder)
                                <div>
                                    <input type="url" wire:model="{{ $field }}" placeholder="{{ $placeholder }}"
                                           class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                    @error($field) <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-lg bg-yellow-500 hover:bg-yellow-400 px-6 py-2.5 text-sm font-bold text-gray-900 disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveProfile">Save Changes</span>
                        <span wire:loading wire:target="saveProfile">Saving…</span>
                    </button>
                </div>
            </form>
        @endif

        {{-- ═══════════════ TAB: WITHDRAW ═══════════════ --}}
        @if ($activeTab === 'withdraw')
            @php
                $balance = (float) ($this->nominee->kes_balance ?? 0);
                $canWithdraw = $this->canWithdraw;
                $nextAt = $this->nextWithdrawalAt;
                $last = $this->lastWithdrawal;
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Withdraw card --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Balance + rules --}}
                    <div class="bg-gradient-to-br from-green-900/40 to-gray-800 rounded-2xl border border-green-500/20 p-6 shadow-xl">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="text-xs uppercase tracking-widest text-green-400 font-bold mb-1">Available to withdraw</div>
                                <div class="text-4xl font-mono font-bold text-white">
                                    KES {{ number_format($balance, 2) }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1"/>
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-lg bg-black/20 px-3 py-2">
                                <div class="text-gray-400">Max per withdrawal</div>
                                <div class="font-mono font-bold text-white">KES {{ number_format($this->withdrawalCap()) }}</div>
                            </div>
                            <div class="rounded-lg bg-black/20 px-3 py-2">
                                <div class="text-gray-400">Cooldown between requests</div>
                                <div class="font-mono font-bold text-white">{{ $this->cooldownMinutes() }} min</div>
                            </div>
                        </div>

                        @if ($balance > $this->withdrawalCap())
                            <div class="mt-4 text-xs text-gray-300 bg-black/20 rounded-lg p-3 flex gap-2">
                                <svg class="w-4 h-4 shrink-0 mt-0.5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>
                                    Your balance exceeds the KES {{ number_format($this->withdrawalCap()) }} per-withdrawal cap.
                                    You can withdraw {{ floor($balance / $this->withdrawalCap()) }} × KES {{ number_format($this->withdrawalCap()) }}
                                    (waiting {{ $this->cooldownMinutes() }} min between each), or contact
                                    <a href="https://wa.me/254710878056" target="_blank" class="text-yellow-500 underline">support</a>
                                    to arrange a bulk payout from our side.
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Form --}}
                    @if ($withdrawDone)
                        <div class="bg-gray-800 rounded-2xl border border-green-500/30 p-6 text-center">
                            <div class="w-16 h-16 mx-auto rounded-full bg-green-500/15 border border-green-500/40 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Withdrawal Submitted</h3>
                            <p class="text-sm text-gray-400 mb-4">
                                M-PESA is processing your request. You'll receive funds on your phone within a few minutes.
                            </p>
                            @if ($withdrawReceipt)
                                <div class="text-xs font-mono text-gray-500 bg-gray-900/50 rounded-lg px-3 py-2 inline-block mb-4">
                                    Ref: {{ $withdrawReceipt }}
                                </div>
                            @endif
                            <div class="text-xs text-gray-500 mb-6">
                                You can request another withdrawal in {{ $this->cooldownMinutes() }} minutes.
                            </div>
                            <button wire:click="$set('withdrawDone', false)"
                                    class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold rounded-lg border border-gray-600">
                                Close
                            </button>
                        </div>
                    @else
                        <form wire:submit="requestWithdrawal" class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl p-6 space-y-5">
                            <h3 class="text-lg font-bold text-white">Request Withdrawal</h3>

                            @if (! $canWithdraw && $nextAt)
                                <div class="rounded-xl bg-yellow-500/10 border border-yellow-500/20 p-4 text-sm text-yellow-400 flex gap-3"
                                     x-data="{
                                         target: {{ $nextAt->timestamp * 1000 }},
                                         remaining: '',
                                         tick() {
                                             const now = Date.now();
                                             const diff = Math.max(0, this.target - now);
                                             const m = Math.floor(diff / 60000);
                                             const s = Math.floor((diff % 60000) / 1000);
                                             this.remaining = m + 'm ' + String(s).padStart(2, '0') + 's';
                                         },
                                         init() { this.tick(); setInterval(() => this.tick(), 1000); }
                                     }">
                                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <div class="font-bold">Cooldown active</div>
                                        <div class="text-xs opacity-80 mt-0.5">
                                            Next withdrawal available in <span class="font-mono font-bold" x-text="remaining"></span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @error('withdraw_amount')
                                <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-4 text-sm text-red-400">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">M-PESA Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">🇰🇪</span>
                                    </div>
                                    <input type="tel" wire:model="withdraw_phone" placeholder="0712345678"
                                           class="block w-full pl-10 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                           {{ ! $canWithdraw ? 'disabled' : '' }}>
                                </div>
                                @error('withdraw_phone') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Amount (KES)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-bold sm:text-sm">KES</span>
                                    </div>
                                    <input type="number" wire:model.live="withdraw_amount"
                                           min="{{ 10 }}" max="{{ min($this->withdrawalCap(), $balance) }}" step="1"
                                           class="block w-full pl-14 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                           {{ ! $canWithdraw ? 'disabled' : '' }}>
                                </div>
                                <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                    <span>Min KES 10 · Max KES {{ number_format(min($this->withdrawalCap(), $balance)) }}</span>
                                    <button type="button" wire:click="$set('withdraw_amount', {{ min($this->withdrawalCap(), (int) $balance) }})"
                                            class="text-yellow-500 hover:text-yellow-400 font-bold">Use max</button>
                                </div>
                            </div>

                            <div class="rounded-lg bg-gray-900/50 border border-gray-700 px-4 py-3 text-xs text-gray-400 flex items-center justify-between">
                                <span>You'll receive on your phone</span>
                                <span class="font-mono font-bold text-green-400 text-base">
                                    KES {{ number_format(max(0, (float) $withdraw_amount), 2) }}
                                </span>
                            </div>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    {{ (! $canWithdraw || $balance < 10) ? 'disabled' : '' }}
                                    class="w-full flex items-center justify-center px-6 py-4 rounded-xl shadow-lg text-base font-bold text-white bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                <span wire:loading.remove wire:target="requestWithdrawal">
                                    @if ($balance < 10)
                                        Balance too low
                                    @elseif (! $canWithdraw)
                                        Cooldown active
                                    @else
                                        Withdraw to M-PESA
                                    @endif
                                </span>
                                <span wire:loading wire:target="requestWithdrawal" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Processing…
                                </span>
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Recent withdrawals --}}
                <div class="lg:col-span-1">
                    <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden sticky top-24">
                        <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50">
                            <h3 class="text-base font-bold text-white">Recent Withdrawals</h3>
                        </div>

                        @if ($this->withdrawalHistory->count() > 0)
                            <ul class="divide-y divide-gray-700 max-h-[500px] overflow-y-auto">
                                @foreach ($this->withdrawalHistory as $w)
                                    <li class="p-4 hover:bg-gray-700/30 transition-colors">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="text-sm font-mono font-bold text-white">
                                                KES {{ number_format($w->amount, 2) }}
                                            </div>
                                            <span class="text-[10px] uppercase font-bold
                                                {{ $w->status === 'completed' ? 'text-green-500' : ($w->status === 'failed' ? 'text-red-500' : 'text-yellow-500') }}">
                                                {{ $w->status }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span class="font-mono">{{ $maskPhone($w->phone_number) }}</span>
                                            <span>{{ $w->created_at->diffForHumans() }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="py-10 px-4 text-center text-sm text-gray-500">
                                No withdrawals yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
