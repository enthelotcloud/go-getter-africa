<?php

use App\Models\NominationCategory;
use App\Models\Nomination;
use Livewire\Component;
use Livewire\Attributes\Layout;

// Fixed: Attribute goes between 'new' and 'class'
new #[Layout('layouts.guest.app')] class extends Component {

    public NominationCategory $category;

    public function mount(NominationCategory $category)
    {
        $this->category = $category;
    }

    public function with(): array
    {
        $nominations = Nomination::where('nomination_category_id', $this->category->id)
            ->where('is_active', true)
            ->orderByDesc('total_votes')
            ->get();

        return [
            'nominations' => $nominations
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" wire:poll.10s>

    <!-- Breadcrumb & Header -->
    <a href="{{ route('polls') }}" class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-yellow-500 mb-6 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Polls
    </a>

    <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 shadow-2xl mb-8 flex items-center gap-6">
        @if($category->thumbnail)
            <img src="{{ asset('storage/' . $category->thumbnail) }}" class="w-24 h-24 rounded-xl object-cover border border-gray-600 shadow-lg">
        @else
            <div class="w-24 h-24 rounded-xl bg-gray-900 border border-gray-700 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
            </div>
        @endif

        <div>
            <h1 class="text-3xl font-bold text-white mb-2">{{ $category->name }}</h1>
            <p class="text-gray-400 text-sm max-w-2xl">{{ $category->description ?? $category->excerpt }}</p>
        </div>
    </div>

    <!-- Ranked Nominees List -->
    <div class="space-y-4">
        @foreach($nominations as $index => $nominee)
            <div class="bg-gray-800/50 hover:bg-gray-800 rounded-xl p-4 border border-gray-700/50 hover:border-gray-600 transition-all flex items-center justify-between group shadow-lg">
                <div class="flex items-center gap-5">
                    <!-- Rank Badge -->
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg shadow-inner
                        {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-500 border border-yellow-500/30' :
                          ($index === 1 ? 'bg-gray-300/20 text-gray-300 border border-gray-300/30' :
                          ($index === 2 ? 'bg-orange-600/20 text-orange-500 border border-orange-600/30' : 'bg-gray-900 text-gray-500 border border-gray-700')) }}">
                        #{{ $index + 1 }}
                    </div>

                    @if($nominee->profile_image)
                        <img src="{{ asset('storage/' . $nominee->profile_image) }}" class="w-14 h-14 rounded-full object-cover border border-gray-600">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gray-900 border border-gray-700 flex items-center justify-center text-gray-500 font-bold">
                            {{ substr($nominee->name, 0, 1) }}
                        </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-yellow-500 transition-colors">{{ $nominee->name }}</h3>
                        <p class="text-sm text-gray-400">{{ $nominee->company_or_show ?? 'Independent' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right hidden sm:block">
                        <div class="text-2xl font-mono font-bold text-white">{{ number_format($nominee->total_votes) }}</div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider">Votes</div>
                    </div>
                    <a href="{{ route('polls.vote', $nominee->code) }}" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors shadow-lg shadow-red-600/20">
                        Vote
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
