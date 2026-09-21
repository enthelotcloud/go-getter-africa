{{-- First-visit onboarding tour. Dismissible, remembered via localStorage. --}}
<div
    x-data="voteTour()"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tour-title">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-950/85 backdrop-blur-sm"
         @click="skip()"></div>

    {{-- Card --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="relative z-10 w-full max-w-lg bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden">

        {{-- Close --}}
        <button type="button" @click="skip()"
                class="absolute top-4 right-4 z-20 p-1.5 rounded-lg text-gray-500 hover:text-white hover:bg-white/10 transition-colors"
                aria-label="Close tour">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Progress bar --}}
        <div class="h-1 bg-gray-800 w-full">
            <div class="h-full bg-red-600 transition-all duration-500 ease-out"
                 :style="`width: ${((current + 1) / totalSlides) * 100}%`"></div>
        </div>

        {{-- Slides --}}
        <div class="relative min-h-[380px] p-8 pt-10 flex flex-col">

            {{-- SLIDE 1: Welcome --}}
            <div x-show="current === 0" x-transition.opacity.duration.300ms
                 class="flex-1 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <h2 id="tour-title" class="text-2xl font-bold text-white mb-3">Welcome to Go Getter Africa</h2>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                    Discover, vote for, and support your favorite nominees across TV, radio, sports, and more.
                    Here's how to make your vote count in 4 quick steps.
                </p>
                <p class="mt-6 text-xs font-bold uppercase tracking-wider text-gray-500">
                    Takes about 30 seconds
                </p>
            </div>

            {{-- SLIDE 2: Find your nominee --}}
            <div x-show="current === 1" x-transition.opacity.duration.300ms
                 class="flex-1 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-blue-500/15 border border-blue-500/30 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-3">Find your favorite</h2>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                    Use the search bar at the top of any page — <kbd class="px-1.5 py-0.5 bg-gray-800 border border-gray-700 rounded text-[10px] font-mono text-gray-300">⌘K</kbd> works anywhere.
                    Or browse by category to see who's in the running.
                </p>
                <div class="mt-6 w-full max-w-xs bg-gray-800 border border-gray-700 rounded-full px-4 py-2.5 flex items-center gap-2 text-left">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="text-sm text-gray-500">Try "Jane" or "NOM-A4K9C"</span>
                </div>
            </div>

            {{-- SLIDE 3: Three ways to vote --}}
            <div x-show="current === 2" x-transition.opacity.duration.300ms
                 class="flex-1 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-green-500/15 border border-green-500/30 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-3">Three ways to vote</h2>
                <p class="text-gray-400 text-sm mb-5">Pick whichever is fastest for you.</p>

                <div class="w-full space-y-2.5 text-left">
                    <div class="flex items-start gap-3 p-3 bg-gray-800/60 border border-gray-700/60 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-red-500/15 border border-red-500/30 flex items-center justify-center shrink-0">
                            <span class="text-red-400 font-bold text-sm">1</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-white">Search & vote</div>
                            <div class="text-xs text-gray-400">Type their name, click the Vote button</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-800/60 border border-gray-700/60 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-red-500/15 border border-red-500/30 flex items-center justify-center shrink-0">
                            <span class="text-red-400 font-bold text-sm">2</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-white">Browse by category</div>
                            <div class="text-xs text-gray-400">See live rankings, then vote for who you like</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-gray-800/60 border border-gray-700/60 rounded-xl">
                        <div class="w-8 h-8 rounded-lg bg-red-500/15 border border-red-500/30 flex items-center justify-center shrink-0">
                            <span class="text-red-400 font-bold text-sm">3</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-white">Enter voting code</div>
                            <div class="text-xs text-gray-400">Every nominee has a unique code — use it to jump straight in</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SLIDE 4: Tokens = Votes --}}
            <div x-show="current === 3" x-transition.opacity.duration.300ms
                 class="flex-1 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-yellow-500/15 border border-yellow-500/30 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-3">The more tokens, the more votes</h2>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                    Buy tokens with M-PESA directly from any nominee's page.
                    Each token you spend becomes one vote — and bulk packages give you better value.
                </p>

                <div class="mt-6 grid grid-cols-3 gap-3 w-full max-w-sm">
                    <div class="p-3 bg-gray-800/60 border border-gray-700/60 rounded-xl">
                        <div class="text-lg font-mono font-bold text-yellow-500">10</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">votes</div>
                    </div>
                    <div class="p-3 bg-gray-800/60 border border-yellow-500/30 rounded-xl">
                        <div class="text-lg font-mono font-bold text-yellow-500">100</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">votes</div>
                    </div>
                    <div class="p-3 bg-gray-800/60 border border-gray-700/60 rounded-xl">
                        <div class="text-lg font-mono font-bold text-yellow-500">500</div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">votes</div>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-500">
                    Every KES you spend goes directly to supporting nominees.
                </p>
            </div>

            {{-- SLIDE 5: Share --}}
            <div x-show="current === 4" x-transition.opacity.duration.300ms
                 class="flex-1 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-purple-500/15 border border-purple-500/30 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-3">Share and cheer them on</h2>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm mb-5">
                    Every share multiplies your support. Post on WhatsApp, Facebook, X, or copy the link
                    and send it to friends and family.
                </p>

                <div class="flex items-center justify-center gap-2 flex-wrap">
                    <span class="px-3 py-1.5 rounded-lg bg-[#25D366]/15 text-[#25D366] text-xs font-bold border border-[#25D366]/30">WhatsApp</span>
                    <span class="px-3 py-1.5 rounded-lg bg-[#1877F2]/15 text-[#1877F2] text-xs font-bold border border-[#1877F2]/30">Facebook</span>
                    <span class="px-3 py-1.5 rounded-lg bg-black text-white text-xs font-bold border border-gray-700">X</span>
                    <span class="px-3 py-1.5 rounded-lg bg-gray-700 text-gray-200 text-xs font-bold border border-gray-600">Copy link</span>
                </div>
            </div>

            {{-- Footer: dots + buttons --}}
            <div class="mt-auto pt-8">

                {{-- Progress dots --}}
                <div class="flex items-center justify-center gap-2 mb-5">
                    <template x-for="i in totalSlides" :key="i">
                        <button type="button"
                                @click="current = i - 1"
                                class="h-1.5 rounded-full transition-all"
                                :class="(i - 1) === current ? 'w-6 bg-red-500' : 'w-1.5 bg-gray-700 hover:bg-gray-600'"
                                :aria-label="`Go to slide ${i}`"></button>
                    </template>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="skip()"
                            class="px-4 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                        Skip
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" x-show="current > 0" @click="prev()"
                                class="px-4 py-2.5 rounded-lg border border-gray-600 bg-transparent hover:bg-white/5 text-sm font-medium text-gray-300 transition-colors">
                            Back
                        </button>

                        <button type="button" @click="next()"
                                class="px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-bold transition-colors shadow-lg shadow-red-600/20"
                                x-text="current === totalSlides - 1 ? 'Get Started' : 'Next'"></button>
                    </div>
                </div>

                {{-- Don't show again --}}
                <div class="mt-4 text-center">
                    <button type="button" @click="never()"
                            class="text-[11px] font-medium text-gray-500 hover:text-gray-300 underline transition-colors">
                        Don't show this again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function voteTour() {
        return {
            open: false,
            current: 0,
            totalSlides: 5,
            storageKey: 'gga_tour_dismissed_v1',
            sessionKey: 'gga_tour_seen_session',

            init() {
                // Skip entirely if permanently dismissed
                if (localStorage.getItem(this.storageKey)) return;

                // Skip if already shown this browser session (so navigating between pages doesn't re-open)
                if (sessionStorage.getItem(this.sessionKey)) return;

                // Delay slightly so the page doesn't feel instantly interrupted
                setTimeout(() => {
                    this.open = true;
                    sessionStorage.setItem(this.sessionKey, '1');
                }, 700);
            },

            next() {
                if (this.current < this.totalSlides - 1) {
                    this.current++;
                } else {
                    this.finish();
                }
            },

            prev() {
                if (this.current > 0) this.current--;
            },

            // Completed all slides — mark as done
            finish() {
                localStorage.setItem(this.storageKey, '1');
                this.open = false;
            },

            // Skip for now — will show again next session
            skip() {
                this.open = false;
            },

            // Never show again
            never() {
                localStorage.setItem(this.storageKey, '1');
                this.open = false;
            },
        }
    }
</script>
