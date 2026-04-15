<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go-Getter Brand Africa | Empowering Youth</title>

    <!-- Tailwind + modern fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #030614;
            position: relative;
            overflow-x: hidden;
        }
        /* Modern animated mesh gradient */
        .bg-aura {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(56, 189, 248, 0.12) 0%, transparent 30%),
                        radial-gradient(circle at 80% 70%, rgba(250, 204, 21, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at 40% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 35%),
                        #0b1120;
            animation: breathe 12s ease-in-out infinite alternate;
        }
        @keyframes breathe {
            0% { opacity: 0.8; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.02); }
        }

        /* Glassmorphism enhancements */
        .glass-card {
            background: rgba(15, 25, 45, 0.4);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 35px -8px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 215, 0, 0.05) inset;
        }

        .glass-card:hover {
            border-color: rgba(250, 204, 21, 0.25);
            box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(250, 204, 21, 0.2) inset;
            transform: translateY(-6px);
            transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
        }

        .countdown-unit {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 215, 0, 0.15);
            box-shadow: 0 8px 20px -6px #00000055;
        }

        .glow-text {
            text-shadow: 0 0 20px rgba(250, 204, 21, 0.4);
        }

        .cta-whatsapp {
            background: linear-gradient(145deg, #25D366, #128C7E);
            box-shadow: 0 12px 24px -8px rgba(37, 211, 102, 0.3);
        }
        .cta-email {
            background: linear-gradient(145deg, #FBBF24, #D97706);
            box-shadow: 0 12px 24px -8px rgba(251, 191, 36, 0.3);
        }

        /* Smooth scroll & reveal */
        .fade-up {
            animation: fadeUp 0.9s ease-out;
        }
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .accent-line {
            background: linear-gradient(90deg, transparent, #FBBF24, #FDE047, #FBBF24, transparent);
            height: 2px;
            width: 160px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="text-white antialiased">

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

                <!-- HIGHLIGHT CARDS — modern glassmorphism + hover -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-7 max-w-5xl mx-auto mb-12">

                    <div class="glass-card p-6 rounded-3xl transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 flex items-center justify-center mb-5 border border-yellow-500/30 group-hover:scale-110 transition">
                            <span class="text-2xl">🚀</span>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-white">Youth Empowerment</h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Mentorship circles, financial literacy bootcamps, and forums that equip over 5,000 young Africans annually.
                        </p>
                    </div>

                    <div class="glass-card p-6 rounded-3xl transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 flex items-center justify-center mb-5 border border-yellow-500/30 group-hover:scale-110 transition">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-white">Talent Development</h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Football leagues, creative hubs, and skill accelerators impacting 50+ communities and rising stars.
                        </p>
                    </div>

                    <div class="glass-card p-6 rounded-3xl transition-all duration-300 group">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-500/10 flex items-center justify-center mb-5 border border-yellow-500/30 group-hover:scale-110 transition">
                            <span class="text-2xl">🌱</span>
                        </div>
                        <h3 class="font-bold text-xl mb-2 text-white">Leadership Growth</h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Governance training, strategy labs, and real-world projects that shape tomorrow's African decision-makers.
                        </p>
                    </div>
                </div>

                <!-- CTA Buttons — modern, with icons and micro-interactions -->
                <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
                    <a href="https://wa.me/254710878056" target="_blank"
                       class="cta-whatsapp group relative px-8 py-4 rounded-2xl font-bold text-white text-lg shadow-2xl transition-all duration-300 flex items-center gap-3 hover:scale-105">
                        <span class="text-2xl">💬</span>
                        <span>Chat on WhatsApp</span>
                        <span class="absolute -right-1 -top-1 flex h-4 w-4">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-300 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-4 w-4 bg-green-400"></span>
                        </span>
                    </a>

                    <a href="mailto:info@gogetterbrandafrica.co.ke"
                       class="cta-email group px-8 py-4 rounded-2xl font-bold text-gray-900 text-lg shadow-2xl transition-all duration-300 flex items-center gap-3 hover:scale-105 border border-yellow-300/50">
                        <span class="text-2xl">📧</span>
                        <span>Contact via Email</span>
                    </a>
                </div>

                <!-- subtle extra info -->
                <p class="text-gray-400 text-xs mt-8 opacity-70 max-w-md mx-auto">
                    Join a movement of young go-getters shaping the future of Africa.
                </p>
            </div>
        </div>

        <!-- FOOTER — minimal, elegant -->
        <footer class="relative z-10 border-t border-white/5 bg-black/20 backdrop-blur-sm">
            <div class="max-w-6xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between text-sm text-gray-400">
                <div class="flex items-center gap-2 mb-3 md:mb-0">
                    <span class="font-semibold text-white/80">© 2026 Go-Getter Brand Africa</span>
                    <span class="w-1 h-1 bg-yellow-500/60 rounded-full"></span>
                    <span>All rights reserved.</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                    <span class="flex items-center gap-1 hover:text-yellow-300 transition cursor-default">
                        <span class="text-yellow-400 text-lg">📞</span> 0727 976 566
                    </span>
                    <span class="flex items-center gap-1 hover:text-yellow-300 transition cursor-default">
                        <span class="text-yellow-400 text-lg">📱</span> 0710 878 056
                    </span>
                    <span class="flex items-center gap-1 hover:text-yellow-300 transition">
                        <span class="text-yellow-400 text-lg">✉️</span> info@gogetterbrandafrica.co.ke
                    </span>
                </div>
            </div>
        </footer>
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
</body>
</html>
