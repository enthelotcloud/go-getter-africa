<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Project;

new #[Layout('layouts.guest.app')] class extends Component {
    use WithPagination;

    public $filter = ''; // '' means all, 'upcomming', 'soon', 'Past'

    public function setFilter($type)
    {
        $this->filter = $type;
        $this->resetPage();
    }

    #[Computed]
    public function projects()
    {
        return Project::query()
            ->where('status', 'active') // Only show active projects to the public
            ->when($this->filter, function ($query) {
                $query->where('type', $this->filter);
            })
            ->orderBy('project_date', 'desc')
            ->paginate(9);
    }
};
?>

<div class="py-12 bg-zinc-50 dark:bg-zinc-900 min-h-screen text-zinc-900 dark:text-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6 border-b border-zinc-200 dark:border-zinc-800 pb-6">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight">
                    Projects & Events
                </h1>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400 max-w-2xl">
                    Explore our timeline of past achievements and upcoming engagements.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button wire:click="setFilter('')"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ $filter === '' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:border-emerald-500' }}">
                    All
                </button>
                <button wire:click="setFilter('upcomming')"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ $filter === 'upcomming' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:border-emerald-500' }}">
                    Upcoming
                </button>
                <button wire:click="setFilter('soon')"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ $filter === 'soon' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:border-emerald-500' }}">
                    Soon
                </button>
                <button wire:click="setFilter('Past')"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ $filter === 'Past' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700 hover:border-emerald-500' }}">
                    Past
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse($this->projects as $project)
                <a href="/projects/{{ $project->slug }}" wire:navigate class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col group hover:border-amber-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">

                    <div class="relative overflow-hidden">
                        <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="{{ $project->name }}" class="h-64 w-full object-cover transition-transform duration-500 group-hover:scale-105">

                        @if($project->featured === 'yes')
                            <span class="absolute top-4 left-4 bg-amber-500 text-zinc-900 text-xs font-bold px-3 py-1.5 rounded-md shadow-lg">
                                Featured
                            </span>
                        @endif

                        <span class="absolute top-4 right-4 text-xs font-bold px-3 py-1.5 rounded-md shadow-lg backdrop-blur-md bg-zinc-900/70 text-white border border-zinc-700/50">
                            {{ ucfirst($project->type) }}
                        </span>
                    </div>

                    <div class="p-6 flex-grow flex flex-col">
                        <p class="text-sm text-emerald-600 dark:text-emerald-400 font-bold mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($project->project_date)->format('F d, Y') }}
                        </p>

                        <h3 class="font-extrabold text-2xl text-zinc-900 dark:text-zinc-100 mb-3 group-hover:text-amber-500 transition-colors">
                            {{ $project->name }}
                        </h3>

                        <p class="text-zinc-600 dark:text-zinc-400 text-base line-clamp-3 mb-4 flex-grow">
                            {{ $project->excerpt }}
                        </p>

                        <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-700/50 flex items-center text-amber-600 dark:text-amber-500 font-semibold group-hover:text-amber-500">
                            Read More
                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-20 text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 border-dashed">
                    <svg class="h-16 w-16 text-zinc-300 dark:text-zinc-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-200">No events found</h3>
                    <p class="mt-1 text-sm">Check back later for updates on our timeline.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $this->projects->links() }}
        </div>
    </div>
</div>
