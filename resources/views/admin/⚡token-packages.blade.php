<?php

use App\Models\TokenPackage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public ?int $packageId = null;
    public bool $isEditMode = false;
    public bool $showModal = false;

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    #[Validate('required|integer|min:1|max:100000')]
    public int $tokens = 10;

    #[Validate('required|numeric|min:0|max:1000000')]
    public float $price_kes = 0.0;

    public bool $is_active = true;

    #[Computed]
    public function packages()
    {
        return TokenPackage::latest()->paginate(10);
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset([
            'packageId', 'name', 'tokens', 'price_kes', 'is_active', 'isEditMode',
        ]);
        $this->tokens    = 10;
        $this->price_kes = 0.0;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $package = TokenPackage::findOrFail($id);

        $this->packageId = $package->id;
        $this->name      = $package->name;
        $this->tokens    = (int) $package->tokens;
        $this->price_kes = (float) $package->price_kes;
        $this->is_active = (bool) $package->is_active;

        $this->isEditMode = true;
        $this->showModal  = true;
    }

    public function save(): void
    {
        $this->validate();

        TokenPackage::updateOrCreate(
            ['id' => $this->packageId],
            [
                'name'      => $this->name,
                'tokens'    => $this->tokens,
                'price_kes' => $this->price_kes,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->isEditMode
            ? 'Package updated successfully.'
            : 'Package created successfully.');
    }

    public function delete(int $id): void
    {
        TokenPackage::findOrFail($id)->delete();
        session()->flash('message', 'Package deleted successfully.');
    }

    public function toggleActive(int $id): void
    {
        $package = TokenPackage::findOrFail($id);
        $package->update(['is_active' => ! $package->is_active]);
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Token Packages</h2>
            <p class="text-sm text-gray-400 mt-1">Manage token pricing and bundles for voters.</p>
        </div>
        <button wire:click="create"
                class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20 flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Package
        </button>
    </div>

    {{-- Flash --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="p-4 mb-6 text-sm text-green-900 bg-green-400 rounded-lg flex items-center justify-between border border-green-500 shadow-lg shadow-green-500/10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('message') }}
            </div>
            <button @click="show = false" class="text-green-900 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
        @if ($this->packages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Package</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Tokens</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Per Token</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($this->packages as $package)
                            <tr wire:key="pkg-{{ $package->id }}" class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm font-bold text-white">{{ $package->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 font-bold text-xs">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 7a1 1 0 112 0v4h2a1 1 0 110 2H9a1 1 0 01-1-1V7z"/>
                                        </svg>
                                        {{ number_format((int) $package->tokens) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-400 font-mono font-bold">
                                    KES {{ number_format((float) $package->price_kes, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-mono">
                                    @if ($package->tokens > 0)
                                        KES {{ number_format($package->price_kes / $package->tokens, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleActive({{ $package->id }})"
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full transition-colors {{ $package->is_active
                                                ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20'
                                                : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' }}">
                                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $package->id }})"
                                            class="text-yellow-500 hover:text-yellow-400 mr-4 transition-colors">Edit</button>
                                    <button wire:click="delete({{ $package->id }})"
                                            wire:confirm="Delete this package?"
                                            class="text-red-500 hover:text-red-400 transition-colors">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($this->packages->hasPages())
                <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                    {{ $this->packages->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 mb-6 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Packages Found</h3>
                <p class="text-gray-400 max-w-sm mb-6">Create your first token bundle so voters can start purchasing votes.</p>
                <button wire:click="create"
                        class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20">
                    + Add Package
                </button>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-10">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative w-full max-w-md bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/50">
                <form wire:submit="save">
                    <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/30 flex justify-between items-center rounded-t-xl">
                        <h3 class="text-lg font-bold text-white">{{ $isEditMode ? 'Edit Package' : 'Create Package' }}</h3>
                        <button type="button" @click="show = false" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Package Name</label>
                            <input type="text" wire:model="name" placeholder="e.g. Basic Pack"
                                   class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                            @error('name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Tokens</label>
                                <input type="number" wire:model.live="tokens" min="1" step="1"
                                       class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                @error('tokens') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300">Price (KES)</label>
                                <input type="number" wire:model.live="price_kes" min="0" step="0.01"
                                       class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                @error('price_kes') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Live preview --}}
                        @if ($tokens > 0 && $price_kes > 0)
                            <div class="rounded-lg bg-gray-900/50 border border-gray-700 px-4 py-3 text-xs text-gray-400 flex items-center justify-between">
                                <span>Cost per token</span>
                                <span class="font-mono font-bold text-green-400">
                                    KES {{ number_format($price_kes / $tokens, 4) }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center pt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium text-gray-300">Active (available for purchase)</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="show = false"
                                class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-300 hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-lg bg-yellow-500 px-5 py-2.5 text-sm font-semibold text-gray-900 hover:bg-yellow-400">
                            <span wire:loading.remove wire:target="save">Save Package</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
