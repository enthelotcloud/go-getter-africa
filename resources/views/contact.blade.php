@extends('layouts.guest.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
    {{-- Header with pulsing dot --}}
    <div class="mb-12 lg:mb-16">
        <div class="flex items-center gap-3 mb-4">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <h1 class="text-xl sm:text-2xl lg:text-2xl font-bold text-white">
                Get in Touch
            </h1>
        </div>
        <p class="text-lg lg:text-xl text-gray-400 ">
            Ready to elevate your brand? Let's create something extraordinary together.
            Reach out and let's start the conversation.
        </p>
    </div>

    {{-- Top Section: Contact Methods & Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10 mb-10 lg:mb-16">
        {{-- Side A - Contact Methods --}}
        <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 lg:p-8 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-white mb-6 lg:mb-8 flex items-center gap-2">
                <span class="w-1 h-7 bg-gradient-to-b from-red-500 to-amber-400 rounded-full"></span>
                Connect With Us
            </h2>

            <div class="space-y-4 lg:space-y-6">
                {{-- Phone --}}
                <a href="tel:+254710878056"
                   class="group flex items-center gap-4 lg:gap-5 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-green-500/50 transition-all duration-300">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-green-500/10 rounded-xl flex items-center justify-center group-hover:bg-green-500/20 transition shrink-0">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-400 text-xs lg:text-sm mb-1">Call or WhatsApp</p>
                        <p class="text-white text-base lg:text-lg font-semibold group-hover:text-green-500 transition truncate">+254 710 878 056</p>
                    </div>
                </a>

                {{-- Email --}}
                <a href="mailto:info@gogetterbrandafrica.co.ke"
                   class="group flex items-center gap-4 lg:gap-5 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-red-500/50 transition-all duration-300">
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-red-500/10 rounded-xl flex items-center justify-center group-hover:bg-red-500/20 transition shrink-0">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-400 text-xs lg:text-sm mb-1">Email Us</p>
                        <p class="text-white text-base lg:text-lg font-semibold group-hover:text-red-500 transition truncate">info@gogetterbrandafrica.co.ke</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Side B - Contact Form --}}
        <div class="lg:col-span-1">
            <livewire:contact-form />
        </div>
    </div>

    {{-- Bottom Section: Social Media Cards --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 lg:p-8">
        <h2 class="text-2xl font-bold text-white mb-6 lg:mb-8 flex items-center gap-2">
            <span class="w-1 h-7 bg-gradient-to-b from-amber-400 to-green-500 rounded-full"></span>
            Follow Our Journey
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {{-- Facebook 1 --}}
            <a href="https://www.facebook.com/joseph.wainaina.shake.shake" target="_blank"
               class="group flex items-center gap-3 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-blue-500/50 transition-all duration-300">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span class="text-white group-hover:text-blue-500 transition truncate text-sm font-medium">Joseph</span>
            </a>

            {{-- Facebook 2 --}}
            <a href="https://www.facebook.com/jose.wainaina.5" target="_blank"
               class="group flex items-center gap-3 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-blue-500/50 transition-all duration-300">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span class="text-white group-hover:text-blue-500 transition truncate text-sm font-medium">Jose</span>
            </a>

            {{-- Instagram --}}
            <a href="#" target="_blank"
               class="group flex items-center gap-3 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-pink-500/50 transition-all duration-300">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-pink-500 transition shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
                <span class="text-white group-hover:text-pink-500 transition truncate text-sm font-medium">Instagram</span>
            </a>

            {{-- TikTok --}}
            <a href="#" target="_blank"
               class="group flex items-center gap-3 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-gray-400/50 transition-all duration-300">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-white transition shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-5.394 10.692 6.33 6.33 0 0 0 10.857-4.424V8.687a8.182 8.182 0 0 0 4.773 1.526V6.79a4.831 4.831 0 0 1-1.003-.104z"/>
                </svg>
                <span class="text-white group-hover:text-gray-300 transition truncate text-sm font-medium">TikTok</span>
            </a>

            {{-- LinkedIn --}}
            <a href="#" target="_blank"
               class="group flex items-center gap-3 p-4 rounded-xl bg-neutral-800/50 border border-neutral-800 hover:border-blue-600/50 transition-all duration-300">
                <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 transition shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
                <span class="text-white group-hover:text-blue-600 transition truncate text-sm font-medium text-center">LinkedIn</span>
            </a>
        </div>
    </div>
</div>
@endsection
