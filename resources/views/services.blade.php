@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Header --}}
        <div class="border-b border-white/10 pb-8 mb-12">
            <h1 class="text-3xl lg:text-4xl font-bold mb-4">Our Services</h1>
            <p class="text-gray-400 text-lg">Empowering growth through strategic branding and vocational literacy.</p>
        </div>

        <div class="space-y-16 text-gray-300 leading-relaxed">

            {{-- Service 1 --}}
            <section>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-8 h-8 rounded bg-red-600/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Brand Strategy & Identity</h2>
                </div>
                <p class="mb-6">
                    We use a combination of industry research and feet-on-the-street experience to provide multi-layered brand plans. Our focus is on identifying market opportunities and positioning your brand for maximum visibility and impact.
                </p>
                <a href="https://wa.me/254710878056?text=Inquiry:%20Brand%20Strategy" class="text-red-500 hover:text-red-400 font-bold text-sm tracking-widest uppercase transition">
                    Book Consultation &rarr;
                </a>
            </section>

            {{-- Service 2 --}}
            <section>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-8 h-8 rounded bg-green-600/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Digital Skills Training</h2>
                </div>
                <p class="mb-6">
                    We offer vocational training in digital literacy, preparing individuals for the modern workforce. Our programs are designed to turn potential into productivity, specifically targeting youth and women in the digital economy.
                </p>
                <a href="https://wa.me/254710878056?text=Inquiry:%20Digital%20Skills" class="text-green-500 hover:text-green-400 font-bold text-sm tracking-widest uppercase transition">
                    Enroll in Training &rarr;
                </a>
            </section>

            {{-- Service 3 --}}
            <section>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-8 h-8 rounded bg-blue-600/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Entrepreneurship & Market Linkage</h2>
                </div>
                <p class="mb-6">
                    Supporting business planning and financial management for youth, women, and PLWDs. We facilitate linkages between small businesses, potential customers, and financial institutions to ensure sustainable growth.
                </p>
                <a href="https://wa.me/254710878056?text=Inquiry:%20Entrepreneurship" class="text-blue-500 hover:text-blue-400 font-bold text-sm tracking-widest uppercase transition">
                    Inquire Linkage &rarr;
                </a>
            </section>

            {{-- Methodology Box --}}
            <section class="bg-slate-900/50 p-8 rounded-xl border border-white/5">
                <h2 class="text-xl font-bold text-white mb-4 italic-none">Our Methodology</h2>
                <p class="text-gray-400 mb-4">
                    Our "Strike and Read" approach ensures that every strategy is measurable. We focus on the Value Equation (Value = Price x Targeting) to maximize your brand's return on investment.
                </p>
            </section>

        </div>

        {{-- Footer Back Link --}}
        <div class="mt-16 pt-8 border-t border-white/10">
            <a href="{{ url('/') }}" class="text-gray-500 hover:text-white font-bold text-xs tracking-widest uppercase transition">
                &larr; Back to Home
            </a>
        </div>

    </div>
</div>
@endsection
