<?php

use App\Models\Nomination;
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

    public ?int $nominationId = null;
    public bool $isEditMode = false;
    public bool $showModal = false;

    #[Validate('required|exists:nomination_categories,id')]
    public $nomination_category_id = '';

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    public string $code = '';

    #[Validate('nullable|string|max:255')]
    public string $company_or_show = '';

    #[Validate('nullable|image|max:2048')]
    public $profile_image = null;

    public ?string $existingProfileImage = null;

    #[Validate('nullable|string|max:1000')]
    public string $bio = '';

    #[Validate('nullable|url|max:255')] public string $facebook_url  = '';
    #[Validate('nullable|url|max:255')] public string $instagram_url = '';
    #[Validate('nullable|url|max:255')] public string $twitter_url   = '';
    #[Validate('nullable|url|max:255')] public string $tiktok_url    = '';
    #[Validate('nullable|url|max:255')] public string $youtube_url   = '';
    #[Validate('nullable|url|max:255')] public string $website_url   = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:nominations,code'
                . ($this->nominationId ? ',' . $this->nominationId : ''),
        ];
    }

    #[Computed]
    public function categories()
    {
        return NominationCategory::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function nominations()
    {
        return Nomination::with('category')->latest()->paginate(10);
    }

    public function generateCode(): void
    {
        if (empty($this->code)) {
            $this->code = 'NOM-' . strtoupper(Str::random(5));
        }
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->reset([
            'nominationId', 'nomination_category_id', 'name', 'code', 'company_or_show',
            'profile_image', 'existingProfileImage', 'bio', 'facebook_url', 'instagram_url',
            'twitter_url', 'tiktok_url', 'youtube_url', 'website_url', 'isEditMode',
        ]);
        $this->is_active = true;
        $this->generateCode();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $nomination = Nomination::findOrFail($id);

        $this->nominationId           = $nomination->id;
        $this->nomination_category_id = $nomination->nomination_category_id;
        $this->name                   = $nomination->name;
        $this->code                   = $nomination->code;
        $this->company_or_show        = $nomination->company_or_show ?? '';
        $this->existingProfileImage   = $nomination->profile_image;
        $this->bio                    = $nomination->bio ?? '';
        $this->facebook_url           = $nomination->facebook_url ?? '';
        $this->instagram_url          = $nomination->instagram_url ?? '';
        $this->twitter_url            = $nomination->twitter_url ?? '';
        $this->tiktok_url             = $nomination->tiktok_url ?? '';
        $this->youtube_url            = $nomination->youtube_url ?? '';
        $this->website_url            = $nomination->website_url ?? '';
        $this->is_active              = (bool) $nomination->is_active;
        $this->profile_image          = null;

        $this->isEditMode = true;
        $this->showModal  = true;
    }

    public function removeProfileImage(): void
    {
        $this->profile_image = null;
    }

    public function removeExistingProfileImage(): void
    {
        $this->existingProfileImage = null;
    }

    public function save(): void
    {
        $this->validate();

        $imagePath = $this->existingProfileImage;

        if ($this->profile_image) {
            if ($this->existingProfileImage) {
                Storage::disk('public')->delete($this->existingProfileImage);
            }
            $imagePath = $this->profile_image->store('nominations', 'public');
        } elseif (! $this->existingProfileImage && $this->nominationId) {
            $old = Nomination::find($this->nominationId);
            if ($old?->profile_image) {
                Storage::disk('public')->delete($old->profile_image);
            }
            $imagePath = null;
        }

        Nomination::updateOrCreate(
            ['id' => $this->nominationId],
            [
                'nomination_category_id' => $this->nomination_category_id,
                'name'                   => $this->name,
                'code'                   => $this->code,
                'company_or_show'        => $this->company_or_show ?: null,
                'profile_image'          => $imagePath,
                'bio'                    => $this->bio ?: null,
                'facebook_url'           => $this->facebook_url ?: null,
                'instagram_url'          => $this->instagram_url ?: null,
                'twitter_url'            => $this->twitter_url ?: null,
                'tiktok_url'             => $this->tiktok_url ?: null,
                'youtube_url'            => $this->youtube_url ?: null,
                'website_url'            => $this->website_url ?: null,
                'is_active'              => $this->is_active,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->isEditMode
            ? 'Nominee updated successfully.'
            : 'Nominee added successfully.');
    }

    public function delete(int $id): void
    {
        $nomination = Nomination::findOrFail($id);
        if ($nomination->profile_image) {
            Storage::disk('public')->delete($nomination->profile_image);
        }
        $nomination->delete();
        session()->flash('message', 'Nominee deleted successfully.');
    }

    public function toggleActive(int $id): void
    {
        $nomination = Nomination::findOrFail($id);
        $nomination->update(['is_active' => ! $nomination->is_active]);
    }
};
?>

<div class="max-w-7xl mx-auto text-gray-200">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Nominees Directory</h2>
            <p class="text-sm text-gray-400 mt-1">Manage the candidates, voting codes, and social profiles.</p>
        </div>
        <button wire:click="create"
                class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20 flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Add Nominee
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
        @if ($this->nominations->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Nominee</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Votes</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($this->nominations as $nominee)
                            <tr wire:key="nom-{{ $nominee->id }}" class="hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        @if ($nominee->profile_image)
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-600"
                                                 src="{{ asset('storage/' . $nominee->profile_image) }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-700 border border-gray-600 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-white">{{ $nominee->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $nominee->company_or_show ?? 'Independent' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $nominee->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-gray-900 border border-gray-600 rounded text-xs text-yellow-500 font-mono">
                                        {{ $nominee->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 font-bold">
                                    {{ number_format((int) $nominee->total_votes) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleActive({{ $nominee->id }})"
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full transition-colors {{ $nominee->is_active
                                                ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20'
                                                : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20' }}">
                                        {{ $nominee->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $nominee->id }})"
                                            class="text-yellow-500 hover:text-yellow-400 mr-4 transition-colors">Edit</button>
                                    <button wire:click="delete({{ $nominee->id }})"
                                            wire:confirm="Remove this nominee?"
                                            class="text-red-500 hover:text-red-400 transition-colors">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($this->nominations->hasPages())
                <div class="p-4 border-t border-gray-700 bg-gray-900/30">
                    {{ $this->nominations->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="w-24 h-24 mb-6 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                    <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Nominees Yet</h3>
                <p class="text-gray-400 max-w-sm mb-6">Add your first nominee. Make sure categories exist first.</p>
                <button wire:click="create"
                        class="px-5 py-2.5 bg-yellow-500 text-gray-900 font-semibold rounded-lg hover:bg-yellow-400 transition-colors shadow-lg shadow-yellow-500/20">
                    + Add First Nominee
                </button>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-10">
            <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative w-full max-w-2xl bg-gray-800 border border-gray-700 rounded-xl shadow-2xl shadow-black/50">
                <form wire:submit="save">
                    <div class="px-6 py-5 border-b border-gray-700 bg-gray-900/30 flex justify-between items-center rounded-t-xl">
                        <h3 class="text-lg font-bold text-white">{{ $isEditMode ? 'Edit Nominee' : 'Add Nominee' }}</h3>
                        <button type="button" @click="show = false" class="text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-5 space-y-6 max-h-[70vh] overflow-y-auto">
                        {{-- Core fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Category *</label>
                                <select wire:model="nomination_category_id"
                                        class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                    <option value="">Select Category...</option>
                                    @foreach ($this->categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('nomination_category_id') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300">Voting Code *</label>
                                <div class="flex mt-1">
                                    <input type="text" wire:model="code"
                                           class="block w-full bg-gray-900 border border-gray-600 rounded-l-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5 font-mono uppercase">
                                    <button type="button" wire:click="generateCode"
                                            class="px-4 py-2 bg-gray-700 border border-gray-600 border-l-0 rounded-r-lg text-sm text-gray-300 hover:text-white hover:bg-gray-600">
                                        Regenerate
                                    </button>
                                </div>
                                @error('code') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300">Nominee Name *</label>
                                <input type="text" wire:model="name"
                                       class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                @error('name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300">Company / Show (Optional)</label>
                                <input type="text" wire:model="company_or_show" placeholder="e.g. Citizen TV"
                                       class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5">
                                @error('company_or_show') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Image --}}
                        <div class="border-t border-gray-700 pt-5"
                             x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Profile Picture</label>
                            <div class="flex items-center gap-4">
                                @if ($profile_image)
                                    <div class="relative inline-block group shrink-0">
                                        <img src="{{ $profile_image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-full border-2 border-yellow-500">
                                        <button type="button" wire:click="removeProfileImage"
                                                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @elseif ($existingProfileImage)
                                    <div class="relative inline-block group shrink-0">
                                        <img src="{{ asset('storage/' . $existingProfileImage) }}" class="h-20 w-20 object-cover rounded-full border border-gray-600">
                                        <button type="button" wire:click="removeExistingProfileImage"
                                                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-20 w-20 rounded-full bg-gray-900 border border-gray-600 flex items-center justify-center shrink-0">
                                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div class="w-full">
                                    <label for="dropzone-nom"
                                           class="flex flex-col items-center justify-center w-full h-20 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-900 hover:bg-gray-700/50 hover:border-yellow-500 transition-all">
                                        <p class="text-sm text-gray-400"><span class="font-semibold text-yellow-500">Upload Image</span></p>
                                        <input id="dropzone-nom" type="file" wire:model="profile_image" class="hidden" accept="image/*">
                                    </label>
                                    <div x-show="isUploading" x-cloak class="w-full bg-gray-700 rounded-full h-1 mt-2 overflow-hidden">
                                        <div class="bg-yellow-500 h-1 rounded-full transition-all" x-bind:style="`width: ${progress}%`"></div>
                                    </div>
                                    @error('profile_image') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Bio --}}
                        <div class="border-t border-gray-700 pt-5">
                            <label class="block text-sm font-medium text-gray-300">Bio (Optional)</label>
                            <textarea wire:model="bio" rows="3"
                                      class="mt-1 block w-full bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm px-4 py-2.5"></textarea>
                            @error('bio') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Socials --}}
                        <div class="border-t border-gray-700 pt-5">
                            <h4 class="text-sm font-medium text-gray-300 mb-3">Social Profiles (Optional)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" wire:model="facebook_url"  placeholder="Facebook URL"    class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                                <input type="text" wire:model="instagram_url" placeholder="Instagram URL"   class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                                <input type="text" wire:model="twitter_url"   placeholder="Twitter (X) URL" class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                                <input type="text" wire:model="tiktok_url"    placeholder="TikTok URL"      class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                                <input type="text" wire:model="youtube_url"   placeholder="YouTube URL"     class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                                <input type="text" wire:model="website_url"   placeholder="Website URL"     class="block w-full bg-gray-900 border border-gray-600 rounded-lg text-gray-300 sm:text-sm px-4 py-2">
                            </div>
                            @error('facebook_url')  <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            @error('instagram_url') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            @error('twitter_url')   <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            @error('tiktok_url')    <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            @error('youtube_url')   <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                            @error('website_url')   <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Active --}}
                        <div class="border-t border-gray-700 pt-5">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm font-medium text-gray-300">Active (accepting votes)</span>
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
                            <span wire:loading.remove wire:target="save">Save Nominee</span>
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
