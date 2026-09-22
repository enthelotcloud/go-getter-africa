<?php

use App\Models\Nomination;
use App\Models\Transaction;
use App\Services\MpesaService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public $search = '';

    // Modals
    public $showPayoutModal = false;

    // Payout State
    public $nominationId = null;
    public $nomineeName = '';
    public $maxAmount = 0;
    public $lastUsedPhone = null;
    public $usePreviousPhone = false;

    #[Validate('required|string|min:10')]
    public $phone = '';

    #[Validate('required|numeric|min:10')]
    public $amount = 0;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openPayoutModal($id)
    {
        $this->resetValidation();
        $nominee = Nomination::findOrFail($id);

        $this->nominationId = $nominee->id;
        $this->nomineeName = $nominee->name;
        $this->maxAmount = $nominee->kes_balance;
        $this->amount = $nominee->kes_balance;
        $this->lastUsedPhone = $nominee->last_payout_phone;

        if ($this->lastUsedPhone) {
            $this->usePreviousPhone = true;
            $this->phone = $this->lastUsedPhone;
        } else {
            $this->usePreviousPhone = false;
            $this->phone = '';
        }

        $this->showPayoutModal = true;
    }

    public function updatedUsePreviousPhone($value)
    {
        if ($value && $this->lastUsedPhone) {
            $this->phone = $this->lastUsedPhone;
        } else {
            $this->phone = '';
        }
    }

    public function processPayout(MpesaService $mpesa)
    {
        $this->validate([
            'phone' => 'required|string|min:10',
            'amount' => 'required|numeric|min:10|max:' . $this->maxAmount,
        ]);

        $nominee = Nomination::findOrFail($this->nominationId);

        try {
            // Sends request directly to Safaricom
            $response = $mpesa->withdrawB2C($this->phone, $this->amount, 'Payout for ' . $this->nomineeName);

            if (isset($response['ConversationID'])) {
                DB::transaction(function () use ($nominee, $response) {

                    Transaction::create([
                        'type' => 'b2c_withdrawal',
                        'phone_number' => $this->phone,
                        'amount' => $this->amount,
                        'nomination_id' => $nominee->id,
                        'merchant_request_id' => $response['OriginatorConversationID'] ?? null,
                        'checkout_request_id' => $response['ConversationID'],
                        'status' => 'pending'
                    ]);

                    $nominee->decrement('kes_balance', $this->amount);
                    $nominee->update(['last_payout_phone' => $this->phone]);
                });

                $this->showPayoutModal = false;
                session()->flash('message', 'Payout to ' . $this->nomineeName . ' initiated successfully. Awaiting Safaricom confirmation.');
            } else {
                $this->addError('mpesa', 'Failed to initiate B2C. Check Safaricom configuration.');
            }
        } catch (\Exception $e) {
            $this->addError('mpesa', 'System Error: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'nominations' => Nomination::where('kes_balance', '>', 0)
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('code', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('kes_balance')
                ->paginate(8, ['*'], 'pendingPage'),

            'logs' => Transaction::where('type', 'b2c_withdrawal')
                ->with('nomination')
                ->latest()
                ->paginate(5, ['*'], 'logsPage')
        ];
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">

    <!-- Clean Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white tracking-tight">Payout Manager</h2>
        <p class="text-sm text-gray-400 mt-1">Settle commission balances directly to nominees' M-PESA accounts. Ensure your Safaricom B2C account has sufficient working funds.</p>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="p-4 mb-6 text-sm text-green-900 bg-green-400 rounded-lg flex items-center justify-between border border-green-500 shadow-lg shadow-green-500/10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
            <button @click="show = false" class="text-green-900 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Pending Payouts -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-700 bg-gray-900/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Pending Commissions</h3>
                    <div class="relative w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search nominees..." class="block w-full pl-9 pr-3 py-1.5 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                @if($nominations->count() > 0)
                    <ul class="divide-y divide-gray-700">
                        @foreach ($nominations as $nominee)
                            <li class="p-5 hover:bg-gray-700/30 transition-colors flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if($nominee->profile_image)
                                        <img src="{{ asset('storage/' . $nominee->profile_image) }}" class="h-12 w-12 rounded-full object-cover border border-gray-600">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-400 font-bold">
                                            {{ substr($nominee->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-bold text-white flex items-center gap-2">
                                            {{ $nominee->name }}
                                            <span class="px-2 py-0.5 bg-gray-900 border border-gray-700 rounded text-[10px] text-yellow-500 font-mono">{{ $nominee->code }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            Last Payout: <span class="font-mono">{{ $nominee->last_payout_phone ?? 'Never' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5">Owed</div>
                                        <div class="text-lg font-mono font-bold text-green-400">KES {{ number_format($nominee->kes_balance, 2) }}</div>
                                    </div>
                                    <button wire:click="openPayoutModal({{ $nominee->id }})" class="px-4 py-2 bg-gray-100 hover:bg-white text-gray-900 font-bold rounded-lg transition-colors shadow-lg shadow-white/10 text-sm">
                                        Pay
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($nominations->hasPages())
                        <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                            {{ $nominations->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-12 px-4 text-center">
                        <svg class="h-10 w-10 text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" /></svg>
                        <p class="text-sm text-gray-400">No nominees have pending commissions.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Payout Logs -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-700 bg-gray-900/50">
                    <h3 class="text-lg font-bold text-white">Recent Logs</h3>
                </div>

                @if($logs->count() > 0)
                    <ul class="divide-y divide-gray-700">
                        @foreach ($logs as $log)
                            <li class="p-4 hover:bg-gray-700/30 transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="text-sm font-bold text-white truncate pr-2">{{ $log->nomination->name ?? 'Unknown' }}</div>
                                    <div class="text-xs font-mono font-bold text-red-400">- KES {{ number_format($log->amount) }}</div>
                                </div>
                                <div class="flex justify-between items-center text-xs text-gray-500">
                                    <span class="font-mono">{{ $log->phone_number }}</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2 text-[10px] uppercase font-bold {{ $log->status === 'completed' ? 'text-green-500' : ($log->status === 'failed' ? 'text-red-500' : 'text-yellow-500') }}">
                                    {{ $log->status }} • {{ $log->receipt_number ?? 'Pending Webhook' }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($logs->hasPages())
                        <div class="p-3 border-t border-gray-700 bg-gray-900/30">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @else
                    <div class="py-8 px-4 text-center">
                        <p class="text-sm text-gray-500">No recent payouts.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payout Modal -->
    <div x-data x-show="$wire.showPayoutModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity" wire:click="$set('showPayoutModal', false)"></div>
        <div class="relative z-10 w-full max-w-md bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700 bg-gray-900/80 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-white">Process Payout</h3>
                <button type="button" wire:click="$set('showPayoutModal', false)" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form wire:submit="processPayout" class="p-6">
                @error('mpesa')
                    <div class="mb-5 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-sm text-red-400 flex items-start gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $message }}
                    </div>
                @enderror

                <div class="mb-6 p-4 bg-gray-900 rounded-xl border border-gray-700 flex justify-between items-center">
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-1">Paying</div>
                        <div class="text-base font-bold text-white">{{ $nomineeName }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-1">Max Available</div>
                        <div class="text-lg font-mono font-bold text-green-400">KES {{ number_format($maxAmount, 2) }}</div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">M-PESA Phone Number</label>

                        @if($lastUsedPhone)
                            <label class="flex items-center gap-3 p-3 border border-gray-700 rounded-xl bg-gray-900/50 cursor-pointer mb-3 hover:border-gray-500 transition-colors">
                                <input type="checkbox" wire:model.live="usePreviousPhone" class="w-4 h-4 text-green-500 bg-gray-800 border-gray-600 rounded focus:ring-green-500 focus:ring-2">
                                <div>
                                    <div class="text-sm font-bold text-white">Use previous number</div>
                                    <div class="text-xs font-mono text-gray-400">{{ $lastUsedPhone }}</div>
                                </div>
                            </label>
                        @endif

                        <div class="relative {{ $usePreviousPhone ? 'opacity-50 pointer-events-none' : '' }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-gray-500 sm:text-sm">🇰🇪</span></div>
                            <input type="text" wire:model="phone" placeholder="0712345678" class="block w-full pl-10 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        </div>
                        @error('phone') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Amount to Send (KES)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold sm:text-sm">KES</span>
                            </div>
                            <input type="number" wire:model="amount" max="{{ $maxAmount }}" class="block w-full pl-14 pr-3 py-3 bg-gray-900 border border-gray-600 rounded-xl text-white font-mono text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        </div>
                        @error('amount') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" wire:click="$set('showPayoutModal', false)" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-xl transition-colors border border-gray-600">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="flex-1 flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-green-600/20 disabled:opacity-50">
                        <span wire:loading.remove wire:target="processPayout">Send Funds</span>
                        <span wire:loading wire:target="processPayout" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
