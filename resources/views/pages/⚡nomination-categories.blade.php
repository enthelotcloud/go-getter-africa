<?php

use App\Models\NominationCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;

// Fixed: Attribute goes between 'new' and 'class'
new #[Layout('layouts.guest.app')] class extends Component {

    public function with(): array
    {
        $categories = NominationCategory::where('is_active', true)
            ->with(['nominations' => function ($query) {
                $query->where('is_active', true)
                      ->orderByDesc('total_votes')
                      ->limit(5);
            }])
            ->get()
            ->map(function ($category) {
                $category->total_category_votes = \App\Models\Nomination::where('nomination_category_id', $category->id)->sum('total_votes');
                return $category;
            });

        return [
            'categories' => $categories
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" wire:poll.10s>
    <div class="mb-10 flex justify-between items-end border-b border-gray-700 pb-5">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow-lg shadow-green-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h1 class="text-3xl font-bold text-white">Live Polls</h1>
            </div>
            <p class="text-gray-400">All available categories and current standings. Updates automatically.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($categories as $category)
            <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-xl relative overflow-hidden flex flex-col h-full hover:border-gray-600 transition-colors">
                <!-- Decorative background glow -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>

                <div class="flex gap-2 mb-4">
                    <span class="px-3 py-1 bg-green-500/10 text-green-400 text-xs font-bold rounded-full border border-green-500/20">Active</span>
                </div>

                <h2 class="text-xl font-bold text-white mb-6">{{ $category->name }}</h2>

                <div class="space-y-5 flex-grow">
                    @forelse($category->nominations as $index => $nominee)
                        @php
                            $percentage = $category->total_category_votes > 0
                                ? round(($nominee->total_votes / $category->total_category_votes) * 100)
                                : 0;

                            // Color logic: 1st place Gold, 2nd Green, rest Gray
                            $barColor = $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-green-500' : 'bg-gray-500');
                            $textColor = $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-green-500' : 'text-gray-400');
                        @endphp

                        <div>
                            <div class="flex justify-between items-end mb-1.5">
                                <span class="text-sm font-semibold text-gray-200 truncate pr-4">{{ $nominee->name }}</span>
                                <span class="text-xs font-mono text-gray-400 whitespace-nowrap">{{ number_format($nominee->total_votes) }} votes</span>
                            </div>
                            <div class="w-full bg-gray-900 rounded-full h-6 relative overflow-hidden border border-gray-700">
                                <div class="{{ $barColor }} h-6 rounded-full transition-all duration-1000 ease-out flex items-center px-3" style="width: {{ max($percentage, 10) }}%">
                                    <span class="text-xs font-bold text-white shadow-sm">{{ $percentage }}%</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">No nominees added yet.</div>
                    @endforelse
                </div>

                <div class="mt-8 pt-5 border-t border-gray-700 flex justify-between items-center">
                    <span class="text-sm font-mono text-gray-400">{{ number_format($category->total_category_votes) }} total votes</span>
                    <a href="{{ route('polls.category', $category->slug) }}" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold rounded-lg transition-colors border border-gray-600 hover:border-gray-500 shadow-lg">
                        Expand All
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
