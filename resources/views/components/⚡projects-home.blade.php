<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Project;

new class extends Component {
    
    #[Computed]
    public function featuredProjects()
    {
        // Fetch up to 3 active & featured projects, ordered by the latest project date
        return Project::query()
            ->where('status', 'active')
            ->where('featured', 'yes')
            ->orderBy('project_date', 'desc')
            ->take(3)
            ->get();
    }
};
?>

<div>
    {{-- Projects Grid --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($this->featuredProjects as $project)
            {{-- Project Card --}}
            <div class="bg-slate-900/50 border border-white/5 rounded-2xl overflow-hidden hover:border-white/20 transition-all duration-300 flex flex-col h-full group">
                
                {{-- Image & Badges --}}
                <div class="relative h-56 w-full overflow-hidden">
                    <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="{{ $project->name }}" 
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out opacity-80 group-hover:opacity-100">
                    
                    {{-- Gradient overlay for text readability --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>

                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="bg-red-600/90 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-wider py-1 px-2.5 rounded">
                            Featured
                        </span>
                        <span class="bg-slate-900/80 backdrop-blur-sm text-gray-300 text-[10px] font-bold uppercase tracking-wider py-1 px-2.5 rounded border border-white/10">
                            {{ ucfirst($project->type) }}
                        </span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs font-medium text-gray-400">
                            {{ \Carbon\Carbon::parse($project->project_date)->format('F d, Y') }}
                        </span>
                    </div>
                    
                    <h5 class="text-xl font-bold text-white mb-3 group-hover:text-red-500 transition-colors">
                        {{ $project->name }}
                    </h5>
                    
                    <p class="text-sm text-gray-500 leading-relaxed mb-6 flex-grow line-clamp-3">
                        {{ $project->excerpt }}
                    </p>
                    
                    {{-- Action Link (Will update to actual project route later) --}}
                    <a href="#" class="inline-flex items-center gap-2 text-xs font-bold text-white uppercase tracking-wider group-hover:text-red-500 transition-colors w-max mt-auto">
                        Explore Project
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="col-span-full border border-dashed border-white/10 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h5 class="text-lg font-bold text-white mb-2">Projects Coming Soon</h5>
                <p class="text-sm text-gray-500">We are currently curating our latest impact projects. Check back soon.</p>
            </div>
        @endforelse
    </div>

    {{-- Call to Action / View All Link --}}
    @if($this->featuredProjects->count() > 0)
        <div class="mt-16 text-center">
            <a href="#" class="inline-flex items-center justify-center gap-2 text-sm font-bold text-white uppercase tracking-wider py-4 px-8 bg-transparent border border-white/20 rounded-full hover:bg-white/5 hover:border-white/40 transition-all">
                View All Projects
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    @endif
</div>