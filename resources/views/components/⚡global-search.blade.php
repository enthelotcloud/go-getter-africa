<?php

use App\Models\Nomination;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $query = '';

    #[Computed]
    public function results()
    {
        if (mb_strlen($this->query) < 2) {
            return collect();
        }

        return Nomination::with('category')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->query . '%')
                  ->orWhere('code', 'like', '%' . $this->query . '%');
            })
            ->orderByDesc('total_votes')
            ->limit(6)
            ->get();
    }
};
?>

<div class="relative w-full max-w-xs xl:max-w-sm"
     x-data="{ open: false }"
     @click.outside="open = false"
     @focusin="open = true"
     @keydown.escape.window="open = false">

    {{-- Input --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <input
            wire:model.live.debounce.300ms="query"
            @focus="open = true"
            type="text"
            autocomplete="off"
            class="block w-full pl-10 pr-10 py-2 border border-gray-700 rounded-full leading-5 bg-gray-900/50 text-gray-300 placeholder-gray-500 focus:outline-none focus:bg-gray-900 focus:border-red-500 focus:ring-1 focus:ring-red-500 sm:text-sm transition-colors"
            placeholder="Search nominees...">

        {{-- Loading spinner --}}
        <div wire:loading wire:target="query"
             class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <svg class="animate-spin h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>

        {{-- Clear button --}}
        @if (strlen($query) > 0)
            <button type="button" wire:click="$set('query', '')"
                    wire:loading.remove wire:target="query"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- Dropdown --}}
    @if (strlen($query) >= 2)
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="absolute z-50 mt-2 w-full bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/50 overflow-hidden">

            @if ($this->results->count() > 0)
                <ul class="max-h-80 overflow-y-auto divide-y divide-gray-700/50">
                    @foreach ($this->results as $nominee)
                        <li wire:key="gsr-{{ $nominee->id }}">
                            <a href="{{ route('polls.vote', $nominee->code) }}"
                               @click="open = false"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-700/50 transition-colors group">
                                @if ($nominee->profile_image)
                                    <img src="{{ asset('storage/' . $nominee->profile_image) }}"
                                         class="h-10 w-10 rounded-full object-cover border border-gray-600 shrink-0">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-400 font-bold shrink-0">
                                        {{ substr($nominee->name, 0, 1) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-white truncate group-hover:text-red-400 transition-colors">
                                        {{ $nominee->name }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">
                                        {{ $nominee->category->name ?? 'Nominee' }}
                                    </p>
                                </div>

                                <div class="text-xs font-mono font-bold text-green-500 shrink-0">
                                    {{ number_format((int) $nominee->total_votes) }}
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="px-4 py-2 border-t border-gray-700/50 bg-gray-900/30 text-center">
                    <a href="{{ route('polls') }}"
                       @click="open = false"
                       class="text-xs font-semibold text-gray-400 hover:text-red-400 transition-colors">
                        Browse all categories →
                    </a>
                </div>
            @else
                <div class="px-4 py-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400">
                        No nominees found for "<span class="text-gray-200 font-medium">{{ $query }}</span>"
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>
