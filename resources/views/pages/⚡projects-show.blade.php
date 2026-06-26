<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Project;
use Illuminate\Support\Str;
use Carbon\Carbon;

new #[Layout('layouts.guest.app')] class extends Component {
    public Project $project;

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    #[Computed]
    public function greeting()
    {
        // Get the current hour in Nairobi timezone
        $hour = Carbon::now('Africa/Nairobi')->hour;

        if ($hour < 12) {
            return "Good morning, Go-Getter! 🌅 Ready to make an impact today?";
        } elseif ($hour < 18) {
            return "Good afternoon, Go-Getter! ☀️ Keep pushing boundaries.";
        } else {
            return "Good evening, Go-Getter! 🌙 Reflecting on a legacy of excellence.";
        }
    }

    #[Computed]
    public function randomPastProjects()
    {
        // Only run this query if the current project is a 'Past' event
        if ($this->project->type !== 'Past') {
            return collect();
        }

        return Project::query()
            ->where('status', 'active')
            ->where('type', 'Past')
            ->where('id', '!=', $this->project->id) // Don't show the one we are currently viewing
            ->inRandomOrder()
            ->take(3)
            ->get();
    }
};
?>

<div class="py-16 bg-[#050A15] min-h-screen text-slate-300 font-sans" x-data="{ lightboxOpen: false, activeImage: '' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Top Navigation & Time-Based Greeting -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <a href="/projects" wire:navigate class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-red-500 transition-colors group uppercase tracking-wider">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Timeline
            </a>

            <!-- Creative Carbon Time Greeting -->
            <div class="bg-slate-800/40 border border-slate-700/50 px-4 py-2 rounded-full backdrop-blur-sm">
                <p class="text-sm font-bold text-amber-400 tracking-wide">
                    {{ $this->greeting }}
                </p>
            </div>
        </div>

        <!-- Header Section -->
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

        <!-- 2-Column Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            <!-- Main Content (Left Side - spans 2 columns) -->
            <div class="lg:col-span-2">
                <!-- Main Hero Image -->
                <div class="relative w-full h-[50vh] rounded-2xl overflow-hidden border border-slate-800 shadow-2xl mb-12">
                    <img src="{{ Str::startsWith($project->thumbnail, 'http') ? $project->thumbnail : asset('storage/' . $project->thumbnail) }}"
                         alt="{{ $project->name }}"
                         class="w-full h-full object-cover">

                    <div class="absolute inset-0 bg-gradient-to-t from-[#050A15] via-transparent to-transparent"></div>
                </div>

                <!-- Content Area -->
                <div class="prose prose-invert prose-lg max-w-none mb-16">
                    <p class="text-xl text-slate-300 leading-relaxed font-medium mb-8">
                        {{ $project->excerpt }}
                    </p>

                    <div class="text-slate-400 leading-relaxed space-y-6">
                        {!! nl2br(e($project->description)) !!}
                    </div>
                </div>

                <!-- Gallery / Lightbox Section -->
                @php
                    $gallery = is_array($project->gallery_images) ? $project->gallery_images : json_decode($project->gallery_images, true);
                @endphp

                @if(!empty($gallery) && count($gallery) > 0)
                    <div class="border-t border-slate-800/60 pt-12">
                        <h3 class="text-2xl font-bold text-white mb-6">Event <span class="text-red-600">Gallery.</span></h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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

            <!-- Side Panel (Right Side - spans 1 column) -->
            <div class="lg:col-span-1">
                @if($project->type !== 'Past' && $project->status === 'active')
                    <!-- Upcomming/Soon Events get the WhatsApp Booking Component -->
                    <livewire:whatsapp-booking :projectName="$project->name" />

                @elseif($project->type === 'Past')
                    <!-- Past Events get the 'Random Past Events' Sidebar Feed -->
                    <div class="bg-[#0B1121] rounded-2xl border border-slate-800 shadow-2xl p-6 md:p-8 sticky top-8">
                        <h3 class="text-xl font-extrabold text-white mb-6 border-b border-slate-800/60 pb-4">
                            More <span class="text-red-600">Past Events.</span>
                        </h3>

                        <div class="space-y-6">
                            @forelse($this->randomPastProjects as $pastProject)
                                <a href="/projects/{{ $pastProject->slug }}" wire:navigate class="group block">
                                    <div class="flex gap-4 items-center">
                                        <!-- Thumbnail -->
                                        <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 border border-slate-700">
                                            <img src="{{ Str::startsWith($pastProject->thumbnail, 'http') ? $pastProject->thumbnail : asset('storage/' . $pastProject->thumbnail) }}"
                                                 alt="{{ $pastProject->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        </div>
                                        <!-- Details -->
                                        <div>
                                            <p class="text-xs text-green-500 font-bold mb-1 uppercase tracking-wider">
                                                {{ \Carbon\Carbon::parse($pastProject->project_date)->format('M d, Y') }}
                                            </p>
                                            <h4 class="text-sm font-bold text-slate-200 group-hover:text-red-500 transition-colors line-clamp-2 leading-snug">
                                                {{ $pastProject->name }}
                                            </h4>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-sm text-slate-500">Explore our main timeline to see all of our historic engagements.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Link back to all projects -->
                        <div class="mt-8 pt-6 border-t border-slate-800/60 text-center">
                            <a href="/projects" wire:navigate class="text-sm font-bold text-red-500 hover:text-red-400 transition-colors inline-flex items-center uppercase tracking-wider">
                                View Full Timeline
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>

                @else
                    <!-- Fallback if the project is inactive/closed but not past -->
                    <div class="bg-slate-800/20 border border-slate-800 rounded-2xl p-8 text-center sticky top-8">
                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <h4 class="text-xl font-bold text-white mb-2">Bookings Closed</h4>
                        <p class="text-slate-400 text-sm">Reservations for this event are currently unavailable.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Alpine.js Lightbox Modal (Hidden by default) -->
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
