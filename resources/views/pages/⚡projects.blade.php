<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use App\Models\Project;

new #[Layout('layouts.guest.app')]
#[Title('Projects & Events - Go-Getter Brand Africa')]
class extends Component {
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

<div class="py-16 bg-[#050A15] min-h-screen text-slate-300 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Section (Matched to Screenshots) -->
        <div class="mb-10">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase">Projects</span>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-end gap-6 border-b border-slate-800/60 pb-8">
                <div class="w-full md:w-2/3">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
                        Timeline of <span class="text-red-600">Impact.</span>
                    </h1>
                    <p class="text-slate-400 max-w-2xl text-lg">
                        Explore our history of past achievements and upcoming engagements driving economic and social empowerment across the continent.
                    </p>
                </div>

                <!-- Filters (Matched to brand colors) -->
                <div class="flex flex-wrap gap-3 w-full md:w-auto">
                    <button wire:click="setFilter('')"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 {{ $filter === '' ? 'bg-red-600 text-white shadow-lg shadow-red-900/20' : 'bg-transparent text-slate-400 border border-slate-700 hover:border-red-500 hover:text-white' }}">
                        All
                    </button>
                    <button wire:click="setFilter('upcomming')"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 {{ $filter === 'upcomming' ? 'bg-red-600 text-white shadow-lg shadow-red-900/20' : 'bg-transparent text-slate-400 border border-slate-700 hover:border-red-500 hover:text-white' }}">
                        Upcoming
                    </button>
                    <button wire:click="setFilter('soon')"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 {{ $filter === 'soon' ? 'bg-red-600 text-white shadow-lg shadow-red-900/20' : 'bg-transparent text-slate-400 border border-slate-700 hover:border-red-500 hover:text-white' }}">
                        Soon
                    </button>
                    <button wire:click="setFilter('Past')"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 {{ $filter === 'Past' ? 'bg-red-600 text-white shadow-lg shadow-red-900/20' : 'bg-transparent text-slate-400 border border-slate-700 hover:border-red-500 hover:text-white' }}">
                        Past
                    </button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse($this->projects as $project)
                <a href="/projects/{{ $project->slug }}" wire:navigate class="bg-[#0B1121] rounded-2xl border border-slate-800 overflow-hidden flex flex-col group hover:border-red-600 hover:shadow-2xl hover:shadow-red-900/10 transition-all duration-300 transform hover:-translate-y-1">

                    <div class="relative overflow-hidden">
                        <img src="{{ Str::startsWith($project->thumbnail, 'http') ? $project->thumbnail : asset('storage/' . $project->thumbnail) }}" alt="{{ $project->name }}" class="h-64 w-full object-cover transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100">

                        @if($project->featured === 'yes')
                            <span class="absolute top-4 left-4 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow-lg">
                                Featured
                            </span>
                        @endif

                        <span class="absolute top-4 right-4 text-xs font-bold px-3 py-1.5 rounded shadow-lg backdrop-blur-md bg-black/60 text-white border border-white/10">
                            {{ ucfirst($project->type) }}
                        </span>
                    </div>

                    <div class="p-7 flex-grow flex flex-col">
                        <p class="text-sm text-green-500 font-bold mb-3 flex items-center gap-2 uppercase tracking-wide">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($project->project_date)->format('F d, Y') }}
                        </p>

                        <h3 class="font-extrabold text-2xl text-white mb-3 group-hover:text-red-500 transition-colors leading-tight">
                            {{ $project->name }}
                        </h3>

                        <p class="text-slate-400 text-base line-clamp-3 mb-6 flex-grow leading-relaxed">
                            {{ $project->excerpt }}
                        </p>

                        <div class="mt-auto pt-5 border-t border-slate-800/50 flex items-center text-red-500 font-semibold group-hover:text-red-400">
                            Read Full Details
                            <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </div>
                    </div>
                </a>
            @empty
                <!-- Empty State (Dark Theme) -->
                <div class="col-span-full flex flex-col items-center justify-center py-24 text-slate-400 bg-[#0B1121] rounded-2xl border border-slate-800 border-dashed">
                    <svg class="h-16 w-16 text-slate-600 mb-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-xl font-semibold text-white">No projects found</h3>
                    <p class="mt-2 text-sm text-slate-500">Check back later for updates on our timeline of events.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $this->projects->links() }}
        </div>
    </div>
</div>
