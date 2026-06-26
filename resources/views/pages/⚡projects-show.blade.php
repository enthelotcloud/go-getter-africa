<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Project;
use Illuminate\Support\Str;

new #[Layout('layouts.guest.app')] class extends Component {
    public Project $project;

    // Route model binding automatically fetches the project via the slug in the URL
    public function mount(Project $project)
    {
        $this->project = $project;
    }
};
?>

<div class="py-16 bg-[#050A15] min-h-screen text-slate-300 font-sans" x-data="{ lightboxOpen: false, activeImage: '' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <a href="/projects" wire:navigate class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-red-500 transition-colors mb-10 group uppercase tracking-wider">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Projects
        </a>

        <div class="mb-10">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase">
                    {{ \Carbon\Carbon::parse($project->project_date)->format('F d, Y') }} &bull; {{ ucfirst($project->type) }}
                </span>

                @if($project->featured === 'yes')
                    <span class="ml-3 bg-red-600/10 text-red-500 border border-red-600/20 text-xs font-bold px-2 py-0.5 rounded">
                        Featured
                    </span>
                @endif
            </div>

            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-6 leading-tight">
                {{ $project->name }}
            </h1>
        </div>

        <div class="relative w-full h-[60vh] rounded-2xl overflow-hidden border border-slate-800 shadow-2xl mb-12">
            <img src="{{ Str::startsWith($project->thumbnail, 'http') ? $project->thumbnail : asset('storage/' . $project->thumbnail) }}"
                 alt="{{ $project->name }}"
                 class="w-full h-full object-cover">

            <div class="absolute inset-0 bg-gradient-to-t from-[#050A15] via-transparent to-transparent"></div>
        </div>

        <div class="prose prose-invert prose-lg max-w-none mb-16">
            <p class="text-xl text-slate-300 leading-relaxed font-medium mb-8">
                {{ $project->excerpt }}
            </p>

            <div class="text-slate-400 leading-relaxed space-y-6">
                {!! nl2br(e($project->description)) !!}
            </div>
        </div>

        @php
            // Ensure gallery images are decoded properly
            $gallery = is_array($project->gallery_images) ? $project->gallery_images : json_decode($project->gallery_images, true);
        @endphp

        @if(!empty($gallery) && count($gallery) > 0)
            <div class="border-t border-slate-800/60 pt-12">
                <h3 class="text-2xl font-bold text-white mb-6">Event <span class="text-red-600">Gallery.</span></h3>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($gallery as $image)
                        @php $imageUrl = Str::startsWith($image, 'http') ? $image : asset('storage/' . $image); @endphp
                        <div class="aspect-square rounded-xl overflow-hidden border border-slate-800 cursor-pointer group relative"
                             @click="lightboxOpen = true; activeImage = '{{ $imageUrl }}'">

                            <img src="{{ $imageUrl }}" alt="Gallery image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-80 group-hover:opacity-100">

                            <div class="absolute inset-0 bg-red-900/0 group-hover:bg-red-900/20 flex items-center justify-center transition-all duration-300">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transform scale-50 group-hover:scale-100 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <div x-show="lightboxOpen"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-sm p-4 sm:p-8"
         @keydown.escape.window="lightboxOpen = false">

        <button @click="lightboxOpen = false" class="absolute top-6 right-6 text-slate-400 hover:text-white transition-colors p-2 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <img :src="activeImage" alt="Enlarged gallery image"
             @click.away="lightboxOpen = false"
             class="max-h-full max-w-full object-contain rounded-lg shadow-2xl border border-slate-800"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">
    </div>
</div>
