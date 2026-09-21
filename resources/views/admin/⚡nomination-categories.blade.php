<?php

use App\Models\NominationCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, WithFileUploads;

    public ?int $categoryId = null;
    public bool $isEditMode = false;
    public bool $showModal = false;

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    public string $slug = '';

    #[Validate('nullable|image|max:2048')]
    public $thumbnail = null;

    public ?string $existingThumbnail = null;

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('nullable|string')]
    public string $description = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'slug' => 'required|string|max:255|unique:nomination_categories,slug'
                . ($this->categoryId ? ',' . $this->categoryId : ''),
        ];
    }

    public function updatedName(string $value): void
    {
        if (! $this->isEditMode) {
            $this->slug = Str::slug($value);
        }
    }

    #[Computed]
    public function categories()
    {
        return NominationCategory::latest()->paginate(10);
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset([
            'categoryId', 'name', 'slug', 'thumbnail', 'existingThumbnail',
            'excerpt', 'description', 'is_active', 'isEditMode',
        ]);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $category = NominationCategory::findOrFail($id);

        $this->categoryId        = $category->id;
        $this->name              = $category->name;
        $this->slug              = $category->slug;
        $this->existingThumbnail = $category->thumbnail;
        $this->excerpt           = $category->excerpt ?? '';
        $this->description       = $category->description ?? '';
        $this->is_active         = (bool) $category->is_active;
        $this->thumbnail         = null;

        $this->isEditMode = true;
        $this->showModal  = true;
    }

    public function removeThumbnail(): void
    {
        $this->thumbnail = null;
    }

    public function removeExistingThumbnail(): void
    {
        $this->existingThumbnail = null;
    }

    public function save(): void
    {
        $this->validate();

        $imagePath = $this->existingThumbnail;

        if ($this->thumbnail) {
            if ($this->existingThumbnail) {
                Storage::disk('public')->delete($this->existingThumbnail);
            }
            $imagePath = $this->thumbnail->store('categories', 'public');
        } elseif (! $this->existingThumbnail && $this->categoryId) {
            $old = NominationCategory::find($this->categoryId);
            if ($old?->thumbnail) {
                Storage::disk('public')->delete($old->thumbnail);
            }
            $imagePath = null;
        }

        NominationCategory::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'name'        => $this->name,
                'slug'        => $this->slug,
                'thumbnail'   => $imagePath,
                'excerpt'     => $this->excerpt ?: null,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->dispatch('$refresh');
        session()->flash('message', $this->isEditMode
            ? 'Category updated successfully.'
            : 'Category created successfully.');
    }

    public function delete(int $id): void
    {
        $category = NominationCategory::findOrFail($id);
        if ($category->thumbnail) {
            Storage::disk('public')->delete($category->thumbnail);
        }
        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
    }

    public function toggleActive(int $id): void
    {
        $category = NominationCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Nomination Categories</h2>
            <p class="text-sm text-gray-400 mt-1">Manage the voting categories and their SEO details.</p>
        </div>
        <button wire:click="create"
                class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20 flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Category
        </button>
    </div>

    {{-- Flash --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="p-4 mb-6 text-sm text-green-900 bg-green-400 rounded-lg flex items-center justify-between border border-green-500 shadow-lg shadow-green-500/10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('message') }}
            </div>
            <button @click="show = false" class="text-green-900 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
        @if ($this->categories->count() > 0)
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
                        @foreach ($this->categories as $category)
                            <tr wire:key="cat-{{ $category->id }}" class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        @if ($category->thumbnail)
                                            <img class="h-12 w-12 rounded-lg object-cover border border-gray-600 shadow-sm"
                                                 src="{{ asset('storage/' . $category->thumbnail) }}" alt="">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-700 border border-gray-600 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-white">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-400 truncate max-w-[200px]">
                                                {{ $category->excerpt ?? 'No description' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                                    {{ $category->slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleActive({{ $category->id }})"
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full transition-colors {{ $category->is_active
                                                ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20'
                                                : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $category->id }})"
                                            class="text-yellow-500 hover:text-yellow-400 mr-4 transition-colors">Edit</button>
                                    <button wire:click="delete({{ $category->id }})"
                                            wire:confirm="Delete this category? All associated nominees will be deleted."
                                            class="text-red-500 hover:text-red-400 transition-colors">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($this->categories->hasPages())
                <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                    {{ $this->categories->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 mb-6 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Categories Yet</h3>
                <p class="text-gray-400 max-w-sm mb-6">Get started by creating your first voting category.</p>
                <button wire:click="create"
                        class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20">
                    + Create First Category
                </button>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-10">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative w-full max-w-lg bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/50">
                <form wire:submit="save">
                    <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/30 flex justify-between items-center rounded-t-xl">
                        <h3 class="text-lg font-bold text-white">{{ $isEditMode ? 'Edit Category' : 'Create Category' }}</h3>
                        <button type="button" @click="show = false" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Category Name</label>
                            <input type="text" wire:model.live="name" placeholder="e.g. TV Presenter of the Year"
                                   class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                            @error('name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">URL Slug</label>
                            <input type="text" wire:model="slug" readonly
                                   class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-lg text-gray-400 sm:text-sm px-4 py-2.5 cursor-not-allowed">
                            @error('slug') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Thumbnail Image (Optional)</label>
                            <label for="dropzone-cat"
                                   class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-900 hover:bg-gray-700/50 hover:border-yellow-500 transition-all">
                                <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="text-sm text-gray-400"><span class="font-semibold text-yellow-500">Click to upload</span></p>
                                <input id="dropzone-cat" type="file" wire:model="thumbnail" class="hidden" accept="image/*">
                            </label>
                            <div x-show="isUploading" x-cloak class="w-full bg-gray-700 rounded-full h-1.5 mt-3 overflow-hidden">
                                <div class="bg-yellow-500 h-1.5 rounded-full transition-all" x-bind:style="`width: ${progress}%`"></div>
                            </div>
                            @error('thumbnail') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            @if ($thumbnail)
                                <div class="relative inline-block group">
                                    <img src="{{ $thumbnail->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-lg border border-yellow-500">
                                    <button type="button" wire:click="removeThumbnail"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @elseif ($existingThumbnail)
                                <div class="relative inline-block group">
                                    <img src="{{ asset('storage/' . $existingThumbnail) }}" class="h-24 w-24 object-cover rounded-lg border border-gray-600">
                                    <button type="button" wire:click="removeExistingThumbnail"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">SEO Excerpt</label>
                            <textarea wire:model="excerpt" rows="2"
                                      class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5"></textarea>
                            @error('excerpt') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300">Description (Optional)</label>
                            <textarea wire:model="description" rows="3"
                                      class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5"></textarea>
                            @error('description') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center pt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium text-gray-300">Active (visible to voters)</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-xl">
                        <button type="button" @click="show = false"
                                class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-300 hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-lg bg-yellow-500 px-5 py-2.5 text-sm font-semibold text-gray-900 hover:bg-yellow-400">
                            <span wire:loading.remove wire:target="save">Save Category</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
