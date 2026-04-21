<header class="bg-slate-900/80 backdrop-blur-md sticky top-0 z-50 border-b border-white/5" x-data="{ mobileMenuOpen: false }">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between">
            <div class="flex-1 md:flex md:items-center">
                <a class="block" href="/">
                    <img src="{{asset('go-getter-logo.png')}}" alt="Go Getter Logo" class="h-12 w-auto">
                </a>
            </div>

            <div class="flex items-center gap-6 lg:gap-10">
                <nav aria-label="Global" class="hidden md:block">
                    <ul class="flex items-center gap-8 text-sm font-medium">
                        <li><a class="text-gray-300 transition hover:text-red-500" href="{{ route('about') }}">About</a></li>
                        <li><a class="text-gray-300 transition hover:text-red-500" href="{{ route('contact') }}">Contact</a></li>
                        <li><a class="text-gray-300 transition hover:text-red-500" href="#">Projects</a></li>
                        <li><a class="text-gray-300 transition hover:text-red-500" href="{{ route('services') }}">Services</a></li>
                        <li><a class="text-gray-300 transition hover:text-red-500" href="{{ route('faqs') }}">FAQs</a></li>
                    </ul>
                </nav>

                <div class="flex items-center gap-4">
                    <a href="https://wa.me/254710878056" target="_blank"
                       class="hidden sm:flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-green-500 border border-green-500/20 bg-green-500/5 px-4 py-2 rounded-full hover:bg-green-500/10 transition">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        24/7 Support
                    </a>

                    <a class="hidden md:block rounded-full border-2 border-red-600 px-6 py-2 text-sm font-bold text-white transition hover:bg-red-600 shadow-[0_0_15px_rgba(220,38,38,0.3)]"
                       href="{{ route('contact') }}">
                        Get Started
                    </a>

                    <button @click="mobileMenuOpen = true" class="md:hidden rounded-lg p-2 text-gray-400 hover:bg-white/10 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="mobileMenuOpen" class="fixed inset-0 z-[100] md:hidden" role="dialog" aria-modal="true">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 z-10 w-[280px] overflow-y-auto bg-slate-900/40 backdrop-blur-xl border-l border-white/10 p-6 shadow-2xl">

                <div class="flex items-center justify-between mb-12">
                    <img src="{{asset('go-getter-logo.png')}}" alt="Logo" class="h-8 w-auto">
                    <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-6">
                    <a href="{{ route('about') }}" class="block text-xl font-semibold text-white hover:text-red-500 transition">About</a>
                    <a href="{{ route('contact') }}" class="block text-xl font-semibold text-white hover:text-red-500 transition">Contact</a>
                    <a href="{{ route('services') }}" class="block text-xl font-semibold text-white hover:text-red-500 transition">Services</a>
                    <a href="#" class="block text-xl font-semibold text-white hover:text-red-500 transition">Projects</a>
                    <a href="{{ route('faqs') }}" class="block text-xl font-semibold text-white hover:text-red-500 transition">FAQs</a>
                </nav>

                <div class="mt-20 space-y-4">
                    <a href="https://wa.me/254710878056" class="flex items-center justify-center gap-2 rounded-xl bg-green-500 px-5 py-4 text-sm font-bold text-slate-950">
                        Chat on WhatsApp
                    </a>
                    <a href="{{ route('contact') }}" class="block w-full rounded-xl border-2 border-red-600 px-5 py-4 text-center text-sm font-bold text-white">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </template>
</header>
