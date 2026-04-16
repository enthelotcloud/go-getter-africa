<?php

use Livewire\Component;
use App\Models\Subscriber;
new class extends Component
{
    public $phone;

    public function subscribe()
    {
        $this->validate([
            'phone' => 'required|unique:subscribers,phone',
        ]);

        Subscriber::create(['phone' => $this->phone]);

        session()->flash('message', 'Thank you for subscribing!');
        $this->phone = '';
    }

};
?>

<div>
    <div>
        @if (session()->has('message'))
            <div class="flex inline-flex justify-between bg-teal-100 border border-teal-400 text-teal-700 px-4 py-3 my-2 rounded "
                role="alert">
                <span class="block sm:inline pl-2">
                    <strong class="font-bold">Success</strong>
                    {{ session('message') }}
                </span>
                <span class="inline" onclick="return this.parentNode.remove();">
                    <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        @endif
    </div>
    <form wire:submit.prevent="subscribe">
        <div>
        <h3 class="text-lg font-semibold mb-6">Stay Updated</h3>
        <p class="text-gray-300 mb-4">Subscribe to my newsletter for the latest updates.</p>
        <form class="mt-4">
          <div class="relative">
            <input type="tel" wire:model="phone" placeholder="Your phone number" class="w-full bg-gray-800 border border-gray-700 rounded-lg py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-500 text-white">
            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-1 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </button>
          </div>
        </form>
      </div>
    </form>
</div>
