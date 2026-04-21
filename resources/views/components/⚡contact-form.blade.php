<?php

use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\ContactMessage;

new class extends Component {
    #[Validate('required|min:3')]
    public $name = '';

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $subject = '';

    #[Validate('required|min:10')]
    public $message = '';

    public function send()
    {
        $this->validate();

        ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        $this->reset(['name', 'email', 'subject', 'message']);

        session()->flash('status', 'Message received! We will be in touch.');
    }
}; ?>

<div>
    @if (session('status'))
        <flux:callout color="green" icon="check-circle" dismissable class="mb-6">
            <flux:callout.text>{{ session('status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <form wire:submit="send" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input
                    wire:model="name"
                    placeholder="Your full name"
                    :invalid="$errors->has('name')"
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input
                    type="email"
                    wire:model="email"
                    placeholder="you@example.com"
                    :invalid="$errors->has('email')"
                />
                <flux:error name="email" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Subject</flux:label>
            <flux:input
                wire:model="subject"
                placeholder="What's this about?"
                :invalid="$errors->has('subject')"
            />
            <flux:error name="subject" />
        </flux:field>

        <flux:field>
            <flux:label>Message</flux:label>
            <flux:textarea
                wire:model="message"
                rows="5"
                placeholder="Tell us about your project or inquiry..."
                :invalid="$errors->has('message')"
            />
            <flux:error name="message" />
        </flux:field>

        <flux:button
            type="submit"
            variant="primary"
            class="w-full"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="send">Send Message</span>
            <span wire:loading wire:target="send" class="flex items-center justify-center gap-2">
                <flux:icon.loading class="animate-spin" />
                Sending...
            </span>
        </flux:button>
    </form>
</div>
