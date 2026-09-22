<?php

use App\Models\Transaction;
use App\Models\Nomination;
use App\Models\Vote;
use Livewire\Component;

new class extends Component {

    public function with(): array
    {
        // 1. Total Revenue (All successful incoming money from voters/users)
        $totalRevenue = Transaction::where('type', 'stk_push')
            ->where('status', 'completed')
            ->sum('amount');

        // 2. Pending Commissions (Money currently sitting in nominees' accounts)
        $pendingCommissions = Nomination::sum('kes_balance');

        // 3. Paid Out Commissions (Money successfully sent via B2C)
        $paidOutCommissions = Transaction::where('type', 'b2c_withdrawal')
            ->where('status', 'completed')
            ->sum('amount');

        // 4. Net Profit (Revenue minus all nominee earnings, both pending and paid)
        $netProfit = $totalRevenue - ($pendingCommissions + $paidOutCommissions);

        // 5. Total Votes Cast Platform-Wide
        $totalVotes = Nomination::sum('total_votes');

        // Recent Financial Activity (Mix of incoming votes and outgoing payouts)
        $recentTransactions = Transaction::with(['nomination'])
            ->whereIn('status', ['completed', 'pending'])
            ->latest()
            ->take(6)
            ->get();

        // Top Performing Nominees
        $topNominees = Nomination::with('category')
            ->orderByDesc('total_votes')
            ->take(5)
            ->get();

        return [
            'totalRevenue' => $totalRevenue,
            'pendingCommissions' => $pendingCommissions,
            'paidOutCommissions' => $paidOutCommissions,
            'netProfit' => $netProfit,
            'totalVotes' => $totalVotes,
            'recentTransactions' => $recentTransactions,
            'topNominees' => $topNominees,
        ];
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200" wire:poll.15s>

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white tracking-tight">System Overview</h2>
        <p class="text-sm text-gray-400 mt-1">Real-time financial metrics, payout liabilities, and voting activity.</p>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Total Revenue -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl -mr-8 -mt-8 transition-all group-hover:bg-blue-500/20"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1 uppercase tracking-wider">Total Revenue</p>
                    <h3 class="text-3xl font-mono font-bold text-white">
                        <span class="text-lg text-gray-500">KES</span> {{ number_format($totalRevenue) }}
                    </h3>
                </div>
                <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-green-500/10 rounded-full blur-2xl -mr-8 -mt-8 transition-all group-hover:bg-green-500/20"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1 uppercase tracking-wider">Net Profit (40%)</p>
                    <h3 class="text-3xl font-mono font-bold text-green-400">
                        <span class="text-lg text-green-700">KES</span> {{ number_format($netProfit) }}
                    </h3>
                </div>
                <div class="p-3 bg-green-500/10 rounded-xl border border-green-500/20">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        <!-- Pending Commissions -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-yellow-500/10 rounded-full blur-2xl -mr-8 -mt-8 transition-all group-hover:bg-yellow-500/20"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1 uppercase tracking-wider">Pending Payouts</p>
                    <h3 class="text-3xl font-mono font-bold text-yellow-500">
                        <span class="text-lg text-yellow-700">KES</span> {{ number_format($pendingCommissions) }}
                    </h3>
                </div>
                <div class="p-3 bg-yellow-500/10 rounded-xl border border-yellow-500/20">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Paid Out Commissions -->
        <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -mr-8 -mt-8 transition-all group-hover:bg-purple-500/20"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1 uppercase tracking-wider">Total Paid Out</p>
                    <h3 class="text-3xl font-mono font-bold text-white">
                        <span class="text-lg text-gray-500">KES</span> {{ number_format($paidOutCommissions) }}
                    </h3>
                </div>
                <div class="p-3 bg-purple-500/10 rounded-xl border border-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- Left Column: Top Nominees -->
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Top Performing Nominees</h3>
                    <div class="text-sm text-gray-400 font-mono">{{ number_format($totalVotes) }} Total Platform Votes</div>
                </div>

                @if($topNominees->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900/30">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Nominee</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Votes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($topNominees as $index => $nominee)
                                    <tr class="hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-inner
                                                {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-500 border border-yellow-500/30' :
                                                  ($index === 1 ? 'bg-gray-300/20 text-gray-300 border border-gray-300/30' :
                                                  ($index === 2 ? 'bg-orange-600/20 text-orange-500 border border-orange-600/30' : 'bg-gray-900 text-gray-500 border border-gray-700')) }}">
                                                #{{ $index + 1 }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @if($nominee->profile_image)
                                                    <img src="{{ asset('storage/' . $nominee->profile_image) }}" class="w-8 h-8 rounded-full object-cover border border-gray-600">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-gray-700 border border-gray-600 flex items-center justify-center text-xs font-bold text-gray-400">
                                                        {{ substr($nominee->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-bold text-white">{{ $nominee->name }}</div>
                                                    <div class="text-xs text-yellow-500 font-mono">{{ $nominee->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ $nominee->category->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-lg font-mono font-bold text-green-400">{{ number_format($nominee->total_votes) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400 text-sm">No voting activity yet.</div>
                @endif
            </div>
        </div>

        <!-- Right Column: Recent Transactions (The Ledger) -->
        <div class="xl:col-span-1 space-y-6">
            <div class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Live Ledger</h3>
                    <a href="{{ route('admin.payouts') }}" class="text-xs font-bold text-blue-400 hover:text-blue-300">View All &rarr;</a>
                </div>

                @if($recentTransactions->count() > 0)
                    <ul class="divide-y divide-gray-700">
                        @foreach($recentTransactions as $txn)
                            <li class="p-5 hover:bg-gray-700/30 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-2">
                                        @if($txn->type === 'stk_push')
                                            <span class="flex h-2 w-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                                            <span class="text-sm font-bold text-white">Vote Cast</span>
                                        @else
                                            <span class="flex h-2 w-2 rounded-full bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.8)]"></span>
                                            <span class="text-sm font-bold text-white">B2C Payout</span>
                                        @endif
                                    </div>
                                    <div class="text-sm font-mono font-bold {{ $txn->type === 'stk_push' ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $txn->type === 'stk_push' ? '+' : '-' }} KES {{ number_format($txn->amount) }}
                                    </div>
                                </div>

                                <div class="flex justify-between items-end">
                                    <div>
                                        <div class="text-xs text-gray-400 mb-0.5">For: <span class="text-gray-300">{{ $txn->nomination->name ?? 'System' }}</span></div>
                                        <div class="text-xs font-mono text-gray-500">{{ $txn->phone_number }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">{{ $txn->created_at->diffForHumans() }}</div>
                                        <div class="text-[10px] uppercase font-bold {{ $txn->status === 'completed' ? 'text-green-500' : 'text-yellow-500' }}">
                                            {{ $txn->status }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-8 text-center text-gray-400 text-sm">No transactions yet.</div>
                @endif
            </div>
        </div>

    </div>
</div>
