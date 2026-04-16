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
