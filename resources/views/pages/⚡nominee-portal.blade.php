<?php

use App\Models\Nomination;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

new #[Layout('layouts.guest.app')] class extends Component {
    use WithPagination;

    // Login State
    public $isAuthenticated = false;

    #[Validate('required|string')]
    public $code = '';

    #[Validate('required|string')]
    public $pin = '';

    public $loginError = '';

    // Authenticated Data
    public $nominationId = null;

    public function login()
    {
        $this->validate();
        $this->loginError = '';

        // Find nominee by unique code and exact PIN
        $nominee = Nomination::where('code', strtoupper($this->code))
            ->where('access_pin', $this->pin)
            ->whereNotNull('access_pin')
            ->first();

        if ($nominee) {
            $this->nominationId = $nominee->id;
            $this->isAuthenticated = true;
        } else {
            $this->loginError = 'Invalid Voting Code or PIN.';
        }
    }

    public function logout()
    {
        $this->isAuthenticated = false;
        $this->nominationId = null;
        $this->reset(['code', 'pin']);
    }

    public function with(): array
    {
        if (!$this->isAuthenticated) {
            return [];
        }

        $nominee = Nomination::with('category')->find($this->nominationId);

        // Get all financial activity (Guest votes bringing in KES, and B2C Payouts taking KES out)
        $transactions = Transaction::where('nomination_id', $this->nominationId)
            ->where('status', 'completed')
            ->latest()
            ->paginate(10);

        return [
            'nominee' => $nominee,
            'transactions' => $transactions
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if(!$isAuthenticated)
        <!-- LOGIN SCREEN -->
        <div class="max-w-md mx-auto mt-10">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20 shadow-[0_0_30px_rgba(239,68,68,0.1)]">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h1 class="text-3xl font-bold text-white">Nominee Portal</h1>
                <p class="text-gray-400 mt-2">Enter your Voting Code and PIN to view your real-time earnings and votes.</p>
            </div>

            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl p-8">
                <form wire:submit="login" class="space-y-5">
                    @if($loginError)
                        <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-400 text-center font-medium">
                            {{ $loginError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Voting Code</label>
                        <input type="text" wire:model="code" placeholder="e.g. NOM-FX123" class="block w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono uppercase focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Access PIN</label>
                        <input type="password" wire:model="pin" placeholder="••••" class="block w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="w-full mt-4 flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-red-600/20">
                        <span wire:loading.remove wire:target="login">Access Dashboard</span>
                        <span wire:loading wire:target="login">Verifying...</span>
                    </button>
                </form>
            </div>
        </div>

    @else
        <!-- AUTHENTICATED DASHBOARD -->
        <div>
            <div class="flex justify-between items-center mb-8 border-b border-gray-700 pb-6">
                <div class="flex items-center gap-4">
                    @if($nominee->profile_image)
                        <img src="{{ asset('storage/' . $nominee->profile_image) }}" class="h-14 w-14 rounded-full object-cover border-2 border-gray-600">
                    @else
                        <div class="h-14 w-14 rounded-full bg-gray-800 border-2 border-gray-600 flex items-center justify-center text-gray-400 font-bold text-xl">
                            {{ substr($nominee->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $nominee->name }}</h1>
                        <p class="text-sm text-gray-400">Code: <span class="font-mono text-yellow-500">{{ $nominee->code }}</span> • {{ $nominee->category->name ?? '' }}</p>
                    </div>
                </div>
                <button wire:click="logout" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-bold border border-gray-600 transition-colors">
                    Log Out
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20 shrink-0">
                        <svg class="w-7 h-7 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Total Votes</div>
                        <div class="text-3xl font-mono font-bold text-white">{{ number_format($nominee->total_votes) }}</div>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl flex items-center gap-5">
                    <div class="w-14 h-14 rounded-full bg-green-500/10 flex items-center justify-center border border-green-500/20 shrink-0">
                        <span class="text-xl font-bold text-green-500">KES</span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400 uppercase tracking-wider font-bold mb-1">Unpaid Commission</div>
                        <div class="text-3xl font-mono font-bold text-green-400">{{ number_format($nominee->kes_balance, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Ledger/Transactions -->
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/50">
                    <h3 class="text-lg font-bold text-white">Financial Activity</h3>
                </div>

                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900/30">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Details</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount (KES)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach($transactions as $txn)
                                    <tr class="hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ $txn->created_at->format('M d, Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($txn->type === 'stk_push')
                                                <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs font-bold rounded border border-green-500/20">Vote Received</span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-xs font-bold rounded border border-blue-500/20">Payout Sent</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 font-mono">
                                            {{ $txn->phone_number }}
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $txn->receipt_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold {{ $txn->type === 'stk_push' ? 'text-green-400' : 'text-red-400' }}">
                                            {{ $txn->type === 'stk_push' ? '+' : '-' }} {{ number_format($txn->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                        <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-8 text-center text-gray-400 text-sm">
                        No financial activity recorded yet.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
