<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Project;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';

    public $project_id, $name, $description, $status = 'active', $type = 'Past';
    public $project_date, $featured = 'no', $excerpt;
    
    public $thumbnail; 
    public $newThumbnail; 
    
    public $gallery_images = [];
    public $newGalleryImages = [];

    public $isOpen = false;
    public $isViewMode = false;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingTypeFilter() { $this->resetPage(); }

    #[Computed]
    public function projects()
    {
        return Project::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('excerpt', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->orderBy('project_date', 'desc')
            ->paginate(9);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isViewMode = false;
        $this->isOpen = true;
    }

    public function show($id)
    {
        $this->edit($id);
        $this->isViewMode = true;
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $this->project_id = $id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->status = $project->status;
        $this->type = $project->type; // Note: Ensure this matches the exact casing in DB
        $this->project_date = $project->project_date->format('Y-m-d');
        $this->featured = $project->featured;
        $this->excerpt = $project->excerpt;
        
        $this->thumbnail = $project->thumbnail;
        $this->gallery_images = $project->gallery_images ?? [];
        
        $this->isViewMode = false;
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'project_date' => 'required|date',
            'excerpt' => 'required|string|max:255',
            'newThumbnail' => $this->project_id ? 'nullable|image|max:2048' : 'required|image|max:2048',
            'newGalleryImages.*' => 'image|max:2048', // Validate multiple images
        ]);

        // Handle Main Thumbnail
        $thumbnailPath = $this->thumbnail;
        if ($this->newThumbnail) {
            $thumbnailPath = $this->newThumbnail->store('thumbnails', 'public');
        }

        // Handle JSON Gallery Expansion
        $currentGallery = $this->gallery_images;
        if (!empty($this->newGalleryImages)) {
            foreach ($this->newGalleryImages as $image) {
                $currentGallery[] = $image->store('galleries', 'public');
            }
        }

        Project::updateOrCreate(['id' => $this->project_id], [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'thumbnail' => $thumbnailPath,
            'gallery_images' => $currentGallery,
            'status' => $this->status,
            'type' => $this->type,
            'project_date' => $this->project_date,
            'featured' => $this->featured,
            'excerpt' => $this->excerpt,
        ]);

        session()->flash('message', $this->project_id ? 'Event Updated Successfully.' : 'Event Created Successfully.');
        $this->isOpen = false;
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Project::findOrFail($id)->delete();
        session()->flash('message', 'Event Deleted Successfully.');
    }

    public function removeGalleryImage($index)
    {
        unset($this->gallery_images[$index]);
        $this->gallery_images = array_values($this->gallery_images); // Re-index array
        // Optionally save directly to DB here if updating an existing project
    }

    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->reset(['project_id', 'name', 'description', 'thumbnail', 'newThumbnail', 'gallery_images', 'newGalleryImages', 'project_date', 'excerpt']);
        $this->status = 'active';
        $this->type = 'upcomming';
        $this->featured = 'no';
    }
};
?>

<div class="p-4 md:p-6 bg-zinc-50 dark:bg-zinc-900 min-h-screen text-zinc-900 dark:text-zinc-200">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <input type="text" wire:model.live="search" placeholder="Search events..." class="border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded px-4 py-2 w-full md:w-64 focus:ring-amber-500 focus:border-amber-500">
            
            <select wire:model.live="statusFilter" class="border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded px-4 py-2">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            
            <select wire:model.live="typeFilter" class="border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded px-4 py-2">
                <option value="">All Types</option>
                <option value="upcomming">Upcoming</option>
                <option value="soon">Soon</option>
                <option value="Past">Past</option>
            </select>
        </div>
        <button wire:click="create()" class="bg-emerald-600 text-white font-bold px-6 py-2 rounded shadow hover:bg-emerald-700 w-full md:w-auto transition-colors">
            + New Event
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-100 border-l-4 border-emerald-600 text-emerald-800 p-4 rounded mb-6 dark:bg-emerald-900 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($this->projects as $project)
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col group hover:border-amber-500 transition-colors">
                <div class="relative">
                    <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="{{ $project->name }}" class="h-56 w-full object-cover">
                    @if($project->featured === 'yes')
                        <span class="absolute top-3 left-3 bg-amber-500 text-zinc-900 text-xs font-bold px-3 py-1 rounded shadow-md">Featured</span>
                    @endif
                    <span class="absolute top-3 right-3 text-xs font-bold px-3 py-1 rounded shadow-md {{ $project->status == 'active' ? 'bg-emerald-500 text-white' : 'bg-red-600 text-white' }}">
                        {{ ucfirst($project->status) }}
                    </span>
                </div>
                
                <div class="p-5 flex-grow">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-bold text-xl text-zinc-900 dark:text-zinc-100">{{ $project->name }}</h3>
                    </div>
                    <p class="text-sm text-amber-600 dark:text-amber-500 font-semibold mb-3">
                        {{ \Carbon\Carbon::parse($project->project_date)->format('M d, Y') }} &bull; {{ ucfirst($project->type) }}
                    </p>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm line-clamp-2">{{ $project->excerpt }}</p>
                </div>
                
                <div class="bg-zinc-100 dark:bg-zinc-900/50 px-5 py-4 border-t border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <button wire:click="show({{ $project->id }})" class="text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 font-semibold text-sm">View Details</button>
                    <div class="space-x-3">
                        <button wire:click="edit({{ $project->id }})" class="text-amber-600 hover:text-amber-500 font-semibold text-sm">Edit</button>
                        <button wire:click="delete({{ $project->id }})" wire:confirm="Are you sure you want to delete this event?" class="text-red-600 hover:text-red-500 font-semibold text-sm">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-zinc-500 dark:text-zinc-400">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium">No events found</h3>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->projects->links() }}
    </div>

    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-zinc-900/80 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-3xl bg-white dark:bg-zinc-800 rounded-xl shadow-2xl border border-zinc-700 max-h-[90vh] flex flex-col">
                <div class="flex items-start justify-between p-5 border-b border-zinc-200 dark:border-zinc-700 rounded-t shrink-0">
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
                        {{ $isViewMode ? 'View Event' : ($project_id ? 'Edit Event' : 'Create Event') }}
                    </h3>
                    <button wire:click="closeModal()" class="text-zinc-400 bg-transparent hover:bg-zinc-200 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                        &times;
                    </button>
                </div>
                
                <div class="p-6 space-y-6 overflow-y-auto">
                    <form>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Event Name</label>
                                    <input type="text" wire:model="name" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}>
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Event Date</label>
                                    <input type="date" wire:model="project_date" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}>
                                    @error('project_date') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Status</label>
                                        <select wire:model="status" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Featured</label>
                                        <select wire:model="featured" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}>
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Type (Timeline)</label>
                                    <select wire:model="type" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}>
                                        <option value="upcomming">Upcoming</option>
                                        <option value="soon">Soon</option>
                                        <option value="Past">Past</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Main Thumbnail</label>
                                    @if(!$isViewMode)
                                        <input type="file" wire:model="newThumbnail" class="mt-1 w-full text-sm text-zinc-500 border border-zinc-300 dark:border-zinc-600 rounded p-2">
                                    @endif
                                    @error('newThumbnail') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    
                                    @if ($newThumbnail)
                                        <img src="{{ $newThumbnail->temporaryUrl() }}" class="mt-3 h-24 w-full object-cover rounded shadow-md border border-amber-500">
                                    @elseif ($thumbnail)
                                        <img src="{{ asset('storage/' . $thumbnail) }}" class="mt-3 h-24 w-full object-cover rounded shadow-md">
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Short Excerpt</label>
                                    <textarea wire:model="excerpt" rows="3" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}></textarea>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300">Event Description</label>
                                <textarea wire:model="description" rows="4" class="mt-1 w-full border border-zinc-300 dark:border-zinc-600 bg-transparent rounded p-2 focus:border-amber-500 focus:ring-amber-500" {{ $isViewMode ? 'disabled' : '' }}></textarea>
                            </div>

                            <div class="md:col-span-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-2">Event Gallery Images</label>
                                @if(!$isViewMode)
                                    <input type="file" multiple wire:model="newGalleryImages" class="mb-3 w-full text-sm text-zinc-500 border border-zinc-300 dark:border-zinc-600 rounded p-2">
                                @endif
                                @error('newGalleryImages.*') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror

                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                                    @foreach($gallery_images as $index => $g_img)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/' . $g_img) }}" class="h-16 w-full object-cover rounded border border-zinc-600">
                                            @if(!$isViewMode)
                                                <button type="button" wire:click.prevent="removeGalleryImage({{ $index }})" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">x</button>
                                            @endif
                                        </div>
                                    @endforeach
                                    
                                    @if($newGalleryImages)
                                        @foreach($newGalleryImages as $tempImage)
                                            <img src="{{ $tempImage->temporaryUrl() }}" class="h-16 w-full object-cover rounded border-2 border-amber-500">
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="flex items-center justify-end p-5 border-t border-zinc-200 dark:border-zinc-700 rounded-b shrink-0 bg-zinc-50 dark:bg-zinc-800/50">
                    <button wire:click="closeModal()" class="text-zinc-600 dark:text-zinc-300 bg-transparent hover:bg-zinc-200 dark:hover:bg-zinc-700 font-bold rounded-lg text-sm px-6 py-2.5 transition-colors">Cancel</button>
                    @if(!$isViewMode)
                        <button wire:click.prevent="store()" class="ml-3 text-white bg-emerald-600 hover:bg-emerald-700 font-bold rounded-lg text-sm px-6 py-2.5 shadow-lg transition-colors">Save Event</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>