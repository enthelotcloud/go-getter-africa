<div
    x-data="installPwa()"
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-sm z-[150]">

    <div class="flex items-start gap-4 p-4 rounded-2xl bg-gray-800 border border-gray-700 shadow-2xl shadow-black/50 backdrop-blur-xl">
        <div class="w-12 h-12 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center shrink-0 overflow-hidden">
            <img src="/icons/icon-192.png" alt="" class="w-10 h-10 object-contain">
        </div>

        <div class="flex-1 min-w-0">
            <div class="text-sm font-bold text-white mb-0.5">
                Install the Nominee App
            </div>
            <p class="text-xs text-gray-400 leading-relaxed mb-3">
                Get instant access to your votes and withdrawals right from your home screen.
            </p>

            <div class="flex items-center gap-2">
                <button type="button"
                        x-on:click="install()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-colors">
                    <flux:icon.arrow-down-tray class="w-3.5 h-3.5" />
                    Install
                </button>
                <button type="button"
                        x-on:click="dismiss()"
                        class="px-3 py-1.5 rounded-lg text-gray-400 hover:text-white text-xs font-medium transition-colors">
                    Not now
                </button>
            </div>
        </div>

        <button type="button" x-on:click="dismiss()"
                class="text-gray-500 hover:text-gray-300 shrink-0 -mt-1 -mr-1 p-1"
                aria-label="Dismiss">
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
                // Don't show if user permanently dismissed
                if (localStorage.getItem('gga_pwa_dismissed') === '1') return;

                // Don't show if already running installed
                if (window.matchMedia('(display-mode: standalone)').matches) return;
                if (window.navigator.standalone === true) return;

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    // Show after a short delay so it doesn't feel aggressive
                    setTimeout(() => { this.visible = true; }, 3000);
                });

                window.addEventListener('appinstalled', () => {
                    this.visible = false;
                    this.deferredPrompt = null;
                    localStorage.setItem('gga_pwa_dismissed', '1');
                });
            },

            async install() {
                if (! this.deferredPrompt) return;
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.visible = false;
                if (outcome === 'accepted') {
                    localStorage.setItem('gga_pwa_dismissed', '1');
                }
            },

            dismiss() {
                this.visible = false;
                // Don't nag again this session
                sessionStorage.setItem('gga_pwa_skipped', '1');
            },
        }
    }
</script>
