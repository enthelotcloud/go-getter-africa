{{-- CSS safety net: never render inside an installed PWA, period --}}
<style>
    @media (display-mode: standalone),
           (display-mode: fullscreen),
           (display-mode: minimal-ui) {
        [data-pwa-install] { display: none !important; }
    }
</style>

<div
    data-pwa-install
    x-data="installPwa()"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed left-4 right-4 bottom-24 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-sm z-[150]">

    <div class="flex items-start gap-3 p-3.5 rounded-2xl bg-gray-800/95 backdrop-blur-xl border border-gray-700 shadow-2xl shadow-black/50">
        <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center shrink-0 overflow-hidden">
            <img src="/icons/icon-192.png" alt="" class="w-8 h-8 object-contain">
        </div>

        <div class="flex-1 min-w-0">
            <div class="text-sm font-bold text-white mb-0.5">Install the Nominee App</div>
            <p class="text-xs text-gray-400 leading-relaxed mb-2.5">
                Instant access from your home screen.
            </p>

            <div class="flex items-center gap-2">
                <button type="button" x-on:click="install()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-colors">
                    <flux:icon.arrow-down-tray class="w-3.5 h-3.5" />
                    Install
                </button>
                <button type="button" x-on:click="dismiss()"
                        class="px-2.5 py-1.5 rounded-lg text-gray-400 hover:text-white text-xs font-medium transition-colors">
                    Later
                </button>
            </div>
        </div>

        <button type="button" x-on:click="dismiss()"
                class="text-gray-500 hover:text-gray-300 -mt-1 -mr-1 p-1 shrink-0" aria-label="Dismiss">
            <flux:icon.x-mark class="w-4 h-4" />
        </button>
    </div>
</div>

<script>
    function installPwa() {
        return {
            visible: false,
            deferredPrompt: null,

            init() {
                // Re-check whenever the tab regains focus (covers install-from-menu)
                document.addEventListener('visibilitychange', () => {
                    if (! document.hidden) this.evaluate();
                });

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    setTimeout(() => this.evaluate(), 2500);
                });

                window.addEventListener('appinstalled', () => {
                    this.markInstalled();
                });

                this.evaluate();
            },

            // True if currently running as an installed PWA
            isStandalone() {
                return window.matchMedia('(display-mode: standalone)').matches
                    || window.matchMedia('(display-mode: fullscreen)').matches
                    || window.matchMedia('(display-mode: minimal-ui)').matches
                    || window.navigator.standalone === true;
            },

            evaluate() {
                if (this.isStandalone()) {
                    this.visible = false;
                    this.markInstalled();
                    return;
                }

                if (localStorage.getItem('gga_pwa_dismissed') === '1') {
                    this.visible = false;
                    return;
                }

                // Only show if the browser actually offered an install prompt
                if (this.deferredPrompt) {
                    this.visible = true;
                }
            },

            markInstalled() {
                localStorage.setItem('gga_pwa_dismissed', '1');
                this.visible = false;
                this.deferredPrompt = null;
            },

            async install() {
                if (! this.deferredPrompt) return;
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.visible = false;
                if (outcome === 'accepted') this.markInstalled();
            },

            dismiss() {
                this.visible = false;
                sessionStorage.setItem('gga_pwa_skipped', '1');
            },
        }
    }
</script>
