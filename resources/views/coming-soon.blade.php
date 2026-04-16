@extends('layouts.guest.app')
@section('content')
    <div>
        <!-- Animated background layer -->
    <div class="bg-aura"></div>

    <!-- Main content wrapper -->
    <div class="relative z-10 flex flex-col min-h-screen">

        <!-- MAIN SECTION -->
        <div class="flex-1 flex flex-col items-center justify-center px-5 sm:px-6 py-12 md:py-16">

            <div class="max-w-6xl w-full mx-auto text-center fade-up">

                <!-- Brand + tagline with modern twist -->
                <div class="mb-6 inline-block">
                    <span class="text-xs font-semibold tracking-[0.3em] text-yellow-300/70 uppercase bg-white/5 px-4 py-1.5 rounded-full backdrop-blur-sm border border-white/10">Africa's youth movement</span>
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-7xl font-extrabold tracking-tight mb-5 leading-[1.1]">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-yellow-100 to-white">GO-GETTER</span><br/>
                    <span class="text-yellow-400 glow-text">BRAND AFRICA</span>
                    <span class="text-3xl md:text-5xl inline-block ml-1">🌍</span>
                </h1>

                <p class="text-gray-300 text-lg md:text-2xl max-w-3xl mx-auto font-medium mb-6 opacity-90">
                    Empowering young leaders through mentorship, talent incubation, and community action — from Nairobi to the continent.
                </p>

                <!-- Modern divider -->
                <div class="accent-line mb-8"></div>

                <!-- COUNTDOWN — enhanced with separate cards -->
                <div class="mb-12">
                    <p class="text-sm uppercase tracking-widest text-yellow-300/80 mb-3 font-semibold">Launching in</p>
                    <div id="countdown" class="flex flex-wrap items-center justify-center gap-3 sm:gap-5">
                        <!-- JS will populate dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COUNTDOWN SCRIPT — with modern box design -->
    <script>
        (function() {
            const launchDate = new Date();
            launchDate.setDate(launchDate.getDate() + 2);
            // Set to midnight for consistency (optional)
            launchDate.setHours(0, 0, 0, 0);

            const countdownContainer = document.getElementById("countdown");

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = launchDate - now;

                if (distance < 0) {
                    countdownContainer.innerHTML = `
                        <div class="bg-yellow-500/20 backdrop-blur-md border border-yellow-400/40 px-10 py-5 rounded-3xl">
                            <span class="text-4xl md:text-5xl font-black text-yellow-300 tracking-wide">🚀 WE ARE LIVE!</span>
                        </div>
                    `;
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
                const minutes = Math.floor((distance / 1000 / 60) % 60);
                const seconds = Math.floor((distance / 1000) % 60);

                // Create modern countdown blocks
                const html = `
                    <div class="countdown-unit rounded-2xl px-6 sm:px-8 py-4 sm:py-5 flex flex-col items-center min-w-[90px] sm:min-w-[110px] shadow-inner">
                        <span class="text-4xl sm:text-5xl md:text-6xl font-black text-yellow-300 tabular-nums leading-none">${days}</span>
                        <span class="text-xs sm:text-sm uppercase tracking-widest text-gray-300 mt-2 font-semibold">Days</span>
                    </div>
                    <div class="countdown-unit rounded-2xl px-6 sm:px-8 py-4 sm:py-5 flex flex-col items-center min-w-[90px] sm:min-w-[110px]">
                        <span class="text-4xl sm:text-5xl md:text-6xl font-black text-yellow-300 tabular-nums leading-none">${hours.toString().padStart(2, '0')}</span>
                        <span class="text-xs sm:text-sm uppercase tracking-widest text-gray-300 mt-2 font-semibold">Hours</span>
                    </div>
                    <div class="countdown-unit rounded-2xl px-6 sm:px-8 py-4 sm:py-5 flex flex-col items-center min-w-[90px] sm:min-w-[110px]">
                        <span class="text-4xl sm:text-5xl md:text-6xl font-black text-yellow-300 tabular-nums leading-none">${minutes.toString().padStart(2, '0')}</span>
                        <span class="text-xs sm:text-sm uppercase tracking-widest text-gray-300 mt-2 font-semibold">Minutes</span>
                    </div>
                    <div class="countdown-unit rounded-2xl px-6 sm:px-8 py-4 sm:py-5 flex flex-col items-center min-w-[90px] sm:min-w-[110px]">
                        <span class="text-4xl sm:text-5xl md:text-6xl font-black text-yellow-300 tabular-nums leading-none">${seconds.toString().padStart(2, '0')}</span>
                        <span class="text-xs sm:text-sm uppercase tracking-widest text-gray-300 mt-2 font-semibold">Seconds</span>
                    </div>
                `;

                countdownContainer.innerHTML = html;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
    </script>

    <!-- subtle preload / interactive polish -->
    <style>
        /* smooth everything */
        .transition { transition: all 0.25s ease; }
        .glass-card { transition: transform 0.25s ease, border-color 0.2s, box-shadow 0.3s; }
        .cta-whatsapp:hover, .cta-email:hover { transform: translateY(-3px) scale(1.02); }
        .countdown-unit span:first-child { text-shadow: 0 0 12px #fbbf24aa; }

        /* responsive fine-tuning */
        @media (max-width: 640px) {
            .countdown-unit { min-width: 70px; padding: 0.8rem 0.5rem; }
        }
    </style>
    </div>
@endsection
