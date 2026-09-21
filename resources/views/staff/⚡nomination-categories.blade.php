<?php

use App\Models\NominationCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $categoryId = null;
    public $isEditMode = false;
    public $showModal = false;

    #[Validate('required|min:3|max:255')]
    public $name = '';

    public $slug = '';

    #[Validate('nullable|image|max:2048')] // 2MB Max
    public $thumbnail;

    public $existingThumbnail = null;

    #[Validate('nullable|max:500')]
    public $excerpt = '';

    #[Validate('nullable')]
    public $description = '';

    public $is_active = true;

    public function rules()
    {
        return [
            'slug' => 'required|unique:nomination_categories,slug,' . $this->categoryId,
        ];
    }

    public function updatedName($value)
    {
        if (!$this->isEditMode) {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function categories()
    {
        return NominationCategory::latest()->paginate(10);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['categoryId', 'name', 'slug', 'thumbnail', 'existingThumbnail', 'excerpt', 'description', 'is_active', 'isEditMode']);$this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $category = NominationCategory::findOrFail($id);

        $this->categoryId =$category->id;
        $this->name =$category->name;
        $this->slug =$category->slug;
        $this->existingThumbnail =$category->thumbnail;
        $this->excerpt =$category->excerpt;
        $this->description =$category->description;
        $this->is_active =$category->is_active;
        $this->thumbnail = null;

        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function removeThumbnail()
    {
        $this->thumbnail = null;
    }

    public function removeExistingThumbnail()
    {
        $this->existingThumbnail = null;
    }

    public function save()
    {
        $this->validate();

        $imagePath =$this->existingThumbnail;

        if ($this->thumbnail) {
            if ($this->existingThumbnail) {
                Storage::disk('public')->delete($this->existingThumbnail);
            }
            $imagePath =$this->thumbnail->store('categories', 'public');
        } elseif (!$this->existingThumbnail &&$this->categoryId) {
            $oldCat = NominationCategory::find($this->categoryId);
            if($oldCat &&$oldCat->thumbnail) {
                Storage::disk('public')->delete($oldCat->thumbnail);
            }
            $imagePath = null;
        }

        NominationCategory::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'thumbnail' => $imagePath,
                'excerpt' => $this->excerpt,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->isEditMode ? 'Category updated successfully.' : 'Category created successfully.');
    }

    public function delete($id)
    {
        $category = NominationCategory::findOrFail($id);
        if ($category->thumbnail) {
            Storage::disk('public')->delete($category->thumbnail);
        }
        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
    }

    public function toggleActive($id)
    {
        $category = NominationCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Nomination Categories</h2>
            <p class="text-sm text-gray-400 mt-1">Manage the voting categories and their SEO details.</p>
        </div>
        <button wire:click="create" class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Category
        </button>
    </div>

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="p-4 mb-6 text-sm text-green-900 bg-green-400 rounded-lg flex items-center justify-between border border-green-500 shadow-lg shadow-green-500/10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
            <button @click="show = false" class="text-green-900 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- Data Table / Empty State -->
    <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
        @if($this->categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($this->categories as$category)
                            <tr class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        @if($category->thumbnail)
                                            <img class="h-12 w-12 rounded-lg object-cover border border-gray-600 shadow-sm" src="{{ asset('storage/' . $category->thumbnail) }}" alt="">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-700 border border-gray-600 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-white">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $category->excerpt ?? 'No description' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                    {{ $category->slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleActive({{ $category->id }})" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full transition-colors {{ $category->is_active ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $category->id }})" class="text-yellow-500 hover:text-yellow-400 mr-4 transition-colors">Edit</button>
                                    <button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure you want to delete this category? All associated nominees will be deleted." class="text-red-500 hover:text-red-400 transition-colors">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($this->categories->hasPages())
                <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                    {{ $this->categories->links() }}
                </div>
            @endif
        @else
            <!-- Beautiful Empty State -->
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 mb-6 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20 shadow-[0_0_30px_rgba(234,179,8,0.1)]">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Categories Yet</h3>
                <p class="text-gray-400 max-w-sm mb-6">Get started by creating your first voting category. Categories help organize your nominees efficiently.</p>
                <button wire:click="create" class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20">
                    + Create First Category
                </button>
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900/80 backdrop-blur-sm" aria-hidden="true" x-on:click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/50">
                <form wire:submit="save">
                    <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/30 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">{{ $isEditMode ? 'Edit Category' : 'Create Category' }}</h3>
                        <button type="button" @click="show = false" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-5">
                        <!-- Name & Slug -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Category Name</label>
                            <input type="text" wire:model.live="name" placeholder="e.g. TV Presenter of the Year" class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg shadow-sm text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5 transition-colors">
                            @error('name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">URL Slug</label>
                            <input type="text" wire:model="slug" class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-lg shadow-sm text-gray-400 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5 cursor-not-allowed transition-colors" readonly>
                            @error('slug') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Image Upload with Progress and Preview -->
                        <div x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">

                            <label class="block text-sm font-medium text-gray-300 mb-1">Thumbnail Image (Optional)</label>

                            <div class="flex items-center justify-center w-full">
                                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-900 hover:bg-gray-700/50 hover:border-yellow-500 transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-2 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 20 16"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/></svg>
                                        <p class="text-sm text-gray-400"><span class="font-semibold text-yellow-500">Click to upload</span> or drag and drop</p>
                                    </div>
                                    <input id="dropzone-file" type="file" wire:model="thumbnail" class="hidden" accept="image/*" />
                                </label>
                            </div>

                            <!-- Progress Bar -->
                            <div x-show="isUploading" style="display: none;" class="w-full bg-gray-700 rounded-full h-1.5 mt-3 overflow-hidden">
                                <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-300" x-bind:style="`width: ${progress}%`"></div>
                            </div>

                            @error('thumbnail') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Image Previews -->
                        <div class="mt-2">
                            @if ($thumbnail)
                                <div class="relative inline-block group">
                                    <img src="{{ $thumbnail->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-lg border border-yellow-500 shadow-md">
                                    <button type="button" wire:click="removeThumbnail" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 hover:scale-110 transition-transform">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            @elseif ($existingThumbnail)
                                <div class="relative inline-block group">
                                    <img src="{{ asset('storage/' . $existingThumbnail) }}" class="h-24 w-24 object-cover rounded-lg border border-gray-600">
                                    <button type="button" wire:click="removeExistingThumbnail" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 hover:scale-110 transition-transform opacity-0 group-hover:opacity-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- SEO & Descriptions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300">SEO Excerpt <span class="text-gray-500 font-normal">(Short description)</span></label>
                            <textarea wire:model="excerpt" rows="2" class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg shadow-sm text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5 transition-colors"></textarea>
                            @error('excerpt') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status Toggle -->
                        <div class="flex items-center pt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium text-gray-300">Active (Visible to voters)</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-yellow-500 px-5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-yellow-400 focus:outline-none sm:w-auto">
                            <span wire:loading.remove wire:target="save">Save Category</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Saving...
                            </span>
                        </button>
                        <button type="button" x-on:click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg bg-transparent border border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-300 shadow-sm hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
