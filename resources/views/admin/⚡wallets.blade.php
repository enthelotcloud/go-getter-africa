<?php

use App\Models\Wallet;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function wallets()
    {
        return Wallet::with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest('updated_at')
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_tokens'      => (int) Wallet::sum('token_balance'),
            'total_commission'  => (float) Wallet::sum('kes_balance'),
            'wallet_count'      => (int) Wallet::count(),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">User Wallets</h2>
            <p class="text-sm text-gray-400 mt-1">Monitor token balances and nominee commission earnings.</p>
        </div>

        {{-- Search --}}
        <div class="w-full sm:w-72 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Search by name or email..."
                   class="block w-full pl-10 pr-10 py-2.5 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500 transition-colors">
            @if ($search)
                <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 flex items-center gap-4">
            <div class="h-11 w-11 rounded-lg bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider">Tokens in circulation</div>
                <div class="text-xl font-bold text-white font-mono">{{ number_format($this->stats['total_tokens']) }}</div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 flex items-center gap-4">
            <div class="h-11 w-11 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider">Total commissions earned</div>
                <div class="text-xl font-bold text-green-400 font-mono">KES {{ number_format($this->stats['total_commission'], 2) }}</div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 flex items-center gap-4">
            <div class="h-11 w-11 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider">Active wallets</div>
                <div class="text-xl font-bold text-white font-mono">{{ number_format($this->stats['wallet_count']) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
        @if ($this->wallets->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">User Account</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Token Balance</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Commissions</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($this->wallets as $wallet)
                            <tr wire:key="wallet-{{ $wallet->id }}" class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if ($wallet->user?->profile_photo_url ?? false)
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-600"
                                                 src="{{ $wallet->user->profile_photo_url }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 font-bold uppercase border border-gray-600">
                                                {{ substr($wallet->user->name ?? 'U', 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-white">{{ $wallet->user->name ?? 'Deleted User' }}</div>
                                            <div class="text-xs text-gray-400">{{ $wallet->user->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($wallet->user)
                                        @php
                                            $roleStyles = match ($wallet->user->role) {
                                                'admin'   => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'staff'   => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'nominee' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                                default   => 'bg-gray-700 text-gray-300 border-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $roleStyles }}">
                                            {{ ucfirst($wallet->user->role) }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-xs">Unknown</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/20">
                                        <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                                        </svg>
                                        <span class="text-yellow-500 font-bold font-mono text-sm">
                                            {{ number_format((int) $wallet->token_balance) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ((float) $wallet->kes_balance > 0)
                                        <div class="text-sm text-green-400 font-mono font-bold">
                                            KES {{ number_format((float) $wallet->kes_balance, 2) }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500 font-mono">—</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-gray-400">
                                    {{ $wallet->updated_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($this->wallets->hasPages())
                <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                    {{ $this->wallets->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 mb-6 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">
                    {{ $search ? 'No Matches Found' : 'No Wallets Yet' }}
                </h3>
                <p class="text-gray-400 max-w-sm">
                    @if ($search)
                        No user matches "<span class="text-yellow-500 font-mono">{{ $search }}</span>".
                        Try a different name or email.
                    @else
                        Wallets are created automatically when users register or make their first transaction.
                    @endif
                </p>
                @if ($search)
                    <button wire:click="$set('search', '')"
                            class="mt-6 px-5 py-2.5 bg-gray-700 text-gray-200 font-semibold rounded-lg hover:bg-gray-600 transition-colors">
                        Clear search
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
