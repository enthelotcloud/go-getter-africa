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

<div class="inline-search relative group"
     x-data="searchBar()"
     @keydown.escape.window="$refs.searchInput.blur()"
     @keydown.window.cmd.k.prevent="$refs.searchInput.focus()"
     @keydown.window.ctrl.k.prevent="$refs.searchInput.focus()">

    {{-- Search icon --}}
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
        <svg class="h-4 w-4 text-gray-500 group-focus-within:text-red-500 transition-colors"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>

    {{-- Input — widens on focus via Tailwind, no JS state --}}
    <input
        x-ref="searchInput"
        wire:model.live.debounce.300ms="query"
        @keydown.enter="if ($wire.query.length >= 2) { remember($wire.query); $refs.searchInput.blur(); }"
        type="text"
        autocomplete="off"
        placeholder="Search…"
        class="block w-44 xl:w-52 focus:w-72 xl:focus:w-80 py-2 pl-9 pr-8 rounded-full bg-white/5 border border-white/10 hover:border-white/20 text-gray-200 placeholder-gray-500 text-sm
               transition-[width,background-color,border-color,box-shadow] duration-300 ease-out
               focus:outline-none focus:bg-gray-900 focus:border-red-500/60 focus:ring-1 focus:ring-red-500/20">

    {{-- Loading spinner --}}
    <div wire:loading wire:target="query"
         class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none z-10">
        <svg class="animate-spin h-3.5 w-3.5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
    </div>

    {{-- Clear button --}}
    @if (strlen($query) > 0)
        <button type="button"
                wire:click="$set('query', '')"
                wire:loading.remove
                wire:target="query"
                @click="$refs.searchInput.focus()"
                class="absolute inset-y-0 right-2.5 flex items-center text-gray-500 hover:text-gray-300 z-10"
                aria-label="Clear search">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif

    {{-- Dropdown — hidden by CSS until something inside has focus --}}
    <div class="search-dropdown absolute top-full left-0 mt-2 min-w-full w-[340px] bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/60 overflow-hidden z-50"
         @mousedown.prevent>

        @if (strlen($query) >= 2)
            {{-- ===== LIVE RESULTS ===== --}}
            @if ($this->results->count() > 0)
                <ul class="max-h-80 overflow-y-auto divide-y divide-gray-700/50">
                    @foreach ($this->results as $nominee)
                        <li wire:key="gsr-{{ $nominee->id }}">
                            <a href="{{ route('polls.vote', $nominee->code) }}"
                               wire:navigate
                               @click="remember($wire.query)"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-700/50 transition-colors group/item">
                                @if ($nominee->profile_image)
                                    <img src="{{ asset('storage/' . $nominee->profile_image) }}"
                                         class="h-9 w-9 rounded-full object-cover border border-gray-600 shrink-0">
                                @else
                                    <div class="h-9 w-9 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-400 font-bold text-sm shrink-0">
                                        {{ substr($nominee->name, 0, 1) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-white truncate group-hover/item:text-red-400 transition-colors">
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
                    <a href="{{ route('polls') }}" wire:navigate
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

        @else
            {{-- ===== SEARCH HISTORY ===== --}}
            <template x-if="history.length > 0">
                <div>
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-700/50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            Recent searches
                        </span>
                        <button type="button" @click="clearAll()"
                                class="text-[10px] font-bold uppercase tracking-wider text-red-500 hover:text-red-400 transition-colors">
                            Clear all
                        </button>
                    </div>

                    <ul class="max-h-72 overflow-y-auto divide-y divide-gray-700/30">
                        <template x-for="(item, index) in history" :key="item">
                            <li class="group/hist flex items-center hover:bg-gray-700/50 transition-colors">
                                <button type="button"
                                        @click="$wire.set('query', item); remember(item); $refs.searchInput.focus()"
                                        class="flex items-center gap-3 flex-1 min-w-0 px-4 py-2.5 text-left">
                                    <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-300 truncate" x-text="item"></span>
                                </button>

                                <button type="button"
                                        @click.stop="remove(index)"
                                        class="mr-3 p-1 rounded text-gray-500 hover:text-red-400 hover:bg-red-500/10 opacity-0 group-hover/hist:opacity-100 focus:opacity-100 transition-all"
                                        title="Remove from history">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="history.length === 0">
                <div class="px-4 py-6 text-center">
                    <p class="text-sm text-gray-400">Start typing to search nominees.</p>
                    <p class="text-xs text-gray-500 mt-1.5">
                        <kbd class="px-1.5 py-0.5 bg-gray-900 border border-gray-700 rounded text-[10px] font-mono">⌘K</kbd>
                        to focus from anywhere
                    </p>
                </div>
            </template>
        @endif
    </div>
</div>

<script>
    function searchBar() {
        return {
            history: [],
            maxHistory: 8,
            storageKey: 'gga_search_history_v1',

            init() {
                this.loadHistory();
            },

            loadHistory() {
                try {
                    const raw = localStorage.getItem(this.storageKey);
                    this.history = raw ? JSON.parse(raw) : [];
                } catch (e) {
                    this.history = [];
                }
            },

            persist() {
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.history));
                } catch (e) { /* quota exceeded or private mode */ }
            },

            remember(term) {
                term = (term || '').trim();
                if (term.length < 2) return;
                this.history = [term, ...this.history.filter(h => h !== term)]
                    .slice(0, this.maxHistory);
                this.persist();
            },

            remove(index) {
                this.history.splice(index, 1);
                this.persist();
            },

            clearAll() {
                this.history = [];
                this.persist();
            },
        }
    }
</script>
