@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Unified Header --}}
        <div class="mb-16 lg:mb-24 ">
            <div class="flex items-center gap-3 mb-6">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <h1 class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">
                    Get in Touch
                </h1>
            </div>
            <h2 class="text-3xl lg:text-5xl font-bold text-white mb-8 leading-tight tracking-tight">
                Ready to elevate your brand? <br class="hidden lg:block">
                <span class="text-red-600">Let's start</span> the conversation.
            </h2>
            <p class="text-lg lg:text-xl text-gray-400 leading-relaxed">
                Reach out and let's create something extraordinary together. We bridge creative excellence with economic productivity.
            </p>
        </div>

        {{-- Contact Grid --}}
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 mb-24">

            {{-- Left Side: Direct Channels --}}
            <div class="space-y-12">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-red-600 mb-8 flex items-center gap-4">
                        Connect Directly
                        <div class="h-px flex-grow bg-white/10"></div>
                    </h3>

                    <div class="space-y-4">
                        {{-- Phone Card --}}
                        <a href="tel:+254710878056"
                           class="group flex items-center gap-6 p-6 rounded-2xl bg-slate-900/30 border border-white/5 border-l-4 border-l-red-600 hover:bg-slate-900/60 transition-all duration-300">
                            <div class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center group-hover:scale-110 transition shrink-0">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Call or WhatsApp</p>
                                <p class="text-lg font-bold text-white group-hover:text-red-500 transition">+254 710 878 056</p>
                            </div>
                        </a>

                        {{-- Email Card --}}
                        <a href="mailto:info@gogetterbrandafrica.co.ke"
                           class="group flex items-center gap-6 p-6 rounded-2xl bg-slate-900/30 border border-white/5 border-l-4 border-l-white/20 hover:bg-slate-900/60 transition-all duration-300">
                            <div class="w-12 h-12 bg-white/5 rounded-xl flex items-center justify-center group-hover:scale-110 transition shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Email Inquiry</p>
                                <p class="text-lg font-bold text-white group-hover:text-gray-300 transition truncate">info@gogetterbrandafrica.co.ke</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Social Mini-Grid --}}
                <div class="pt-8">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 mb-6">Our Digital Footprint</h3>
                    <div class="flex flex-wrap gap-3">
                        @php
                            $socials = [
                                ['FB', 'https://www.facebook.com/joseph.wainaina.shake.shake'],
                                ['IG', '#'],
                                ['TK', '#'],
                                ['LN', '#'],
                            ];
                        @endphp
                        @foreach($socials as $social)
                            <a href="{{ $social[1] }}" class="h-12 px-6 rounded-full border border-white/5 bg-slate-900/40 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center text-xs font-bold tracking-widest text-gray-400">
                                {{ $social[0] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right Side: The Form --}}
            <div class="p-8 lg:p-12 rounded-3xl bg-slate-900/40 border border-white/5 relative">
                <div class="absolute top-0 right-0 p-8 opacity-5 select-none">
                    <span class="text-7xl font-black italic">FORM</span>
                </div>
                <livewire:contact-form />
            </div>
        </div>

        {{-- Professional Ethics Footer (Matching About Page) --}}
        <div class="border-t border-white/5 pt-16 grid lg:grid-cols-3 gap-12 opacity-80">
            <div>
                <h2 class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-4">Code of Ethics</h2>
                <p class="text-gray-500 text-xs italic leading-relaxed">"We maintain our professional ethics to ensure we make our line straight not to hinder lines of other professionals."</p>
            </div>
            <div class="lg:col-span-2 grid sm:grid-cols-2 gap-12 text-sm text-gray-500">
                <p>Our flexible approach allows clients to mix strategies, strike, withdraw, and read results with precise market timing.</p>
                <p>Based in Nairobi, we bridge the gap between creative excellence and economic productivity across the African continent.</p>
            </div>
        </div>

    </div>
</div>
@endsection
