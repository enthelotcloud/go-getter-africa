@php
    $roleDashboards = [
        'admin'   => 'admin.dashboard',
        'staff'   => 'staff.dashboard',
        'voter'   => 'voter.dashboard',
        'nominee' => 'nominee.dashboard',
    ];
    $dashboardRoute = auth()->check()
        ? ($roleDashboards[auth()->user()->role] ?? 'dashboard')
        : 'dashboard';

    $navLinks = [
        ['label' => 'About',    'route' => 'about',    'active' => 'about'],
        ['label' => 'Projects', 'route' => 'projects', 'active' => 'projects*'],
        ['label' => 'Polls',    'route' => 'polls',    'active' => 'polls*'],
        ['label' => 'Services', 'route' => 'services', 'active' => 'services'],
        ['label' => 'Contact',  'route' => 'contact',  'active' => 'contact'],
        ['label' => 'FAQs',     'route' => 'faqs',     'active' => 'faqs'],
    ];
@endphp

<header class="bg-slate-900/80 backdrop-blur-md sticky top-0 z-50 border-b border-white/5"
        x-data="{ mobileMenuOpen: false, userMenuOpen: false }">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-4">
            {{-- Logo --}}
            <div class="flex-1 md:flex md:items-center">
                <a class="block" href="/">
                    <img src="{{ asset('go-getter-logo.png') }}" alt="Go Getter Logo" class="h-12 w-auto">
                </a>
            </div>

            <div class="flex items-center gap-4 lg:gap-8">
                {{-- Desktop nav --}}
                <nav aria-label="Global" class="hidden lg:block">
                    <ul class="flex items-center gap-7 text-sm font-medium">
                        @foreach ($navLinks as $link)
                            @php $isActive = request()->routeIs($link['active']); @endphp
                            <li>
                                <a href="{{ route($link['route']) }}"
                                   class="relative transition {{ $isActive ? 'text-white' : 'text-gray-300 hover:text-red-500' }}">
                                    {{ $link['label'] }}
                                    @if ($isActive)
                                        <span class="absolute -bottom-6 left-0 right-0 h-0.5 bg-red-500 rounded-full"></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- Right-side actions --}}
                <div class="flex items-center gap-3">
                    {{-- WhatsApp Support --}}
                    <a href="https://wa.me/254710878056" target="_blank"
                       class="hidden xl:flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-green-500 border border-green-500/20 bg-green-500/5 px-4 py-2 rounded-full hover:bg-green-500/10 transition">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        24/7 Support
                    </a>

                    @auth
                        {{-- Authenticated: avatar dropdown --}}
                        <div class="relative hidden md:block" @click.outside="userMenuOpen = false">
                            <button @click="userMenuOpen = !userMenuOpen" type="button"
                                    class="flex items-center gap-3 rounded-full border border-white/10 bg-white/5 pl-1.5 pr-4 py-1.5 hover:bg-white/10 transition">
                                <span class="h-8 w-8 rounded-full bg-red-600 text-white flex items-center justify-center text-sm font-bold uppercase">
                                    {{ Str::substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="flex flex-col items-start leading-tight">
                                    <span class="text-xs font-semibold text-white max-w-[120px] truncate">
                                        {{ Str::of(auth()->user()->name)->limit(14) }}
                                    </span>
                                    <span class="text-[10px] uppercase tracking-wider text-red-400 font-bold">
                                        {{ auth()->user()->role }}
                                    </span>
                                </span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="userMenuOpen && 'rotate-180'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="userMenuOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute right-0 mt-3 w-64 rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur-xl shadow-2xl shadow-black/50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-white/5">
                                    <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</div>
                                </div>

                                <div class="p-1.5">
                                    <a href="{{ route($dashboardRoute) }}"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                        Dashboard
                                    </a>

                                    <a href="{{ route('profile.edit') }}"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Settings
                                    </a>
                                </div>

                                <div class="border-t border-white/5 p-1.5">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Guest: login + get started --}}
                        <a href="{{ route('login') }}"
                           class="hidden md:inline-flex items-center text-sm font-semibold text-gray-300 hover:text-white transition">
                            Log in
                        </a>

                        <a href="{{ route('register') }}"
                           class="hidden md:inline-flex rounded-full border-2 border-red-600 px-6 py-2 text-sm font-bold text-white transition hover:bg-red-600 shadow-[0_0_15px_rgba(220,38,38,0.3)]">
                            Get Started
                        </a>
                    @endauth

                    {{-- Mobile hamburger --}}
                    <button @click="mobileMenuOpen = true"
                            class="lg:hidden rounded-lg p-2 text-gray-400 hover:bg-white/10 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <template x-teleport="body">
        <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-[100] lg:hidden" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

            {{-- Drawer --}}
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 z-10 w-[300px] overflow-y-auto bg-slate-900/95 backdrop-blur-xl border-l border-white/10 p-6 shadow-2xl flex flex-col">

                {{-- Drawer header --}}
                <div class="flex items-center justify-between mb-8">
                    <img src="{{ asset('go-getter-logo.png') }}" alt="Logo" class="h-8 w-auto">
                    <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-white p-1">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Auth user card (mobile) --}}
                @auth
                    <div class="mb-6 rounded-xl border border-white/10 bg-white/5 p-4 flex items-center gap-3">
                        <span class="h-10 w-10 rounded-full bg-red-600 text-white flex items-center justify-center text-sm font-bold uppercase shrink-0">
                            {{ Str::substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs uppercase tracking-wider text-red-400 font-bold">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                @endauth

                {{-- Nav links --}}
                <nav class="space-y-1">
                    @foreach ($navLinks as $link)
                        @php $isActive = request()->routeIs($link['active']); @endphp
                        <a href="{{ route($link['route']) }}"
                           class="block rounded-lg px-3 py-3 text-lg font-semibold transition {{ $isActive ? 'bg-red-500/10 text-red-400' : 'text-white hover:bg-white/5 hover:text-red-500' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Bottom actions --}}
                <div class="mt-auto pt-8 space-y-3">
                    @auth
                        <a href="{{ route($dashboardRoute) }}"
                           class="flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white hover:bg-red-500 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/>
                            </svg>
                            Go to Dashboard
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-sm font-bold text-red-400 hover:bg-red-500/10 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Log out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}"
                           class="block w-full rounded-xl border-2 border-red-600 px-5 py-3.5 text-center text-sm font-bold text-white hover:bg-red-600 transition">
                            Get Started
                        </a>
                        <a href="{{ route('login') }}"
                           class="block w-full rounded-xl bg-white/5 px-5 py-3.5 text-center text-sm font-bold text-gray-200 hover:bg-white/10 transition">
                            Log in
                        </a>
                    @endauth

                    <a href="https://wa.me/254710878056" target="_blank"
                       class="flex items-center justify-center gap-2 rounded-xl bg-green-500 px-5 py-3.5 text-sm font-bold text-slate-950 hover:bg-green-400 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </template>
</header>
