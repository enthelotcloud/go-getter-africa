<?php

use Livewire\Component;

new class extends Component {
    public $projectName;
    public $name = '';
    public $phone = '';
    public $tickets = 1;
    public $message = '';

    public function mount($projectName)
    {
        $this->projectName = $projectName;
    }

    public function bookViaWhatsapp()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'tickets' => 'required|integer|min:1',
        ]);

        // Build the WhatsApp message
        $text = "Hello Go-Getter Brand Africa! I would like to book for *" . $this->projectName . "*\n\n";
        $text .= "*Name:* " . $this->name . "\n";
        $text .= "*Phone:* " . $this->phone . "\n";
        $text .= "*Tickets/Seats:* " . $this->tickets . "\n";

        if (!empty($this->message)) {
            $text .= "*Additional Info:* " . $this->message . "\n";
        }

        // Format the URL (254710878056)
        $url = "https://wa.me/254710878056?text=" . urlencode($text);

        // Redirect the user to WhatsApp
        $this->redirect($url);
    }
};
?>

<div class="bg-[#0B1121] rounded-2xl border border-slate-800 shadow-2xl p-6 md:p-8 sticky top-8">
    <div class="mb-6">
        <h3 class="text-2xl font-extrabold text-white mb-2">Book Your Spot</h3>
        <p class="text-sm text-slate-400">Fill in your details to secure your reservation via WhatsApp.</p>
    </div>

    <form wire:submit="bookViaWhatsapp" class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
            <input type="text" wire:model="name" required
                   class="w-full bg-[#050A15] border border-slate-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone Number</label>
            <input type="text" wire:model="phone" required placeholder="e.g. 0712345678"
                   class="w-full bg-[#050A15] border border-slate-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Number of Tickets</label>
            <input type="number" wire:model="tickets" required min="1"
                   class="w-full bg-[#050A15] border border-slate-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            @error('tickets') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Message (Optional)</label>
            <textarea wire:model="message" rows="2"
                      class="w-full bg-[#050A15] border border-slate-700 text-white rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"></textarea>
        </div>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3.5 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors shadow-lg shadow-green-900/20 mt-6">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.124.551 4.195 1.597 6.015L.135 24l6.108-1.463A11.97 11.97 0 0012.031 24c6.646 0 12.031-5.385 12.031-12.031S18.677 0 12.031 0zm0 22.015c-1.802 0-3.565-.483-5.111-1.4l-.366-.217-3.793.909.923-3.69-.239-.381A9.974 9.974 0 012.016 12.03c0-5.522 4.494-10.016 10.015-10.016 5.521 0 10.015 4.494 10.015 10.016 0 5.522-4.494 10.016-10.015 10.016zm5.496-7.514c-.302-.151-1.785-.882-2.062-.983-.277-.101-.479-.151-.68.151-.201.302-.781.983-.957 1.184-.176.201-.352.226-.654.075-1.28-.609-2.222-1.127-3.064-2.585-.218-.378.204-.363.639-1.233.076-.151.038-.277-.038-.428-.076-.151-.68-1.637-.932-2.241-.244-.585-.494-.505-.68-.514-.176-.008-.378-.008-.579-.008-.201 0-.529.076-.806.378-.277.302-1.058 1.033-1.058 2.518 0 1.485 1.083 2.92 1.234 3.121.151.201 2.13 3.25 5.158 4.557.72.312 1.282.498 1.721.638.723.23 1.382.197 1.9.12.581-.086 1.785-.73 2.037-1.435.252-.705.252-1.31.176-1.435-.076-.126-.277-.201-.579-.352z"></path></svg>
            Send via WhatsApp
        </button>
    </form>

    <!-- Payment Notice -->
    <div class="mt-6 pt-6 border-t border-slate-800/60">
        <div class="bg-red-600/10 border border-red-600/20 rounded-lg p-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h4 class="text-sm font-bold text-white mb-1">Payment Instructions</h4>
                <p class="text-sm text-slate-400 leading-relaxed">
                    All payments are done strictly via <span class="text-white font-bold">M-Pesa</span>. Send your payment to:
                </p>
                <div class="mt-2 text-xl font-extrabold text-green-500 tracking-wider">
                    0710 878 056
                </div>
            </div>
        </div>
    </div>
</div>
