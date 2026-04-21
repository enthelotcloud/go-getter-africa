@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Balanced Heading Block (No more 3xl/4xl constraints) --}}
        <div class="mb-16 lg:mb-24">
            <div class="flex items-center gap-3 mb-6">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <h1 class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">
                    FAQs
                </h1>
            </div>
            <h2 class="text-3xl lg:text-5xl font-bold text-white mb-8 leading-tight tracking-tight">
                Frequently Asked <span class="text-red-600">Questions.</span>
            </h2>
            <p class="text-lg lg:text-xl text-gray-400 leading-relaxed max-w-5xl">
                Have questions about Go-Getter Brand Africa? We have compiled answers to the most common inquiries regarding our branding and empowerment services.
            </p>
        </div>

        {{-- FAQ Accordion: Cleaner Editorial Style --}}
        <div class="space-y-6 mb-32" x-data="{ active: null }">

            @php
                $faqs = [
                    [
                        'id' => 1,
                        'q' => 'What is Go-Getter Brand Africa?',
                        'a' => 'Go-Getter Brand Africa is a vibrant branding and empowerment company based in Nairobi, Kenya. We focus on individual and corporate branding to contribute to the national GDP and ensure African brands can compete globally.'
                    ],
                    [
                        'id' => 2,
                        'q' => 'Who do you target for your empowerment programs?',
                        'a' => 'We primarily target Youths, Women, and People Living with Disabilities (PLWDs). We also work with leaders to enhance productivity and accountability within the community.'
                    ],
                    [
                        'id' => 3,
                        'q' => 'What branding services do you offer?',
                        'a' => 'We offer a flexible and nimble approach that includes digital literacy, market linkage, strategy strike and read results, and analytics to quantify the effectiveness of your branding efforts.'
                    ],
                    [
                        'id' => 4,
                        'q' => 'How does the Value Equation work?',
                        'a' => 'We believe Value = Price x Targeting. Our highly skilled analytics team helps clients pinpoint where marketing investments are underperforming so we can take corrective action to maximize ROI.'
                    ]
                ];
            @endphp

            @foreach($faqs as $faq)
            <div class="rounded-2xl border border-white/5 bg-slate-900/20 overflow-hidden transition-all duration-500 border-l-4 border-l-white/10"
                 :class="active === {{ $faq['id'] }} ? 'border-l-red-600 bg-slate-900/50' : ''">
                <button @click="active !== {{ $faq['id'] }} ? active = {{ $faq['id'] }} : active = null"
                        class="flex w-full items-center justify-between p-8 text-left focus:outline-none">
                    <span class="text-lg lg:text-xl font-bold tracking-tight" :class="active === {{ $faq['id'] }} ? 'text-white' : 'text-gray-300'">
                        {{ $faq['q'] }}
                    </span>
                    <div class="ml-4 flex-shrink-0">
                        <svg class="h-5 w-5 transition-transform duration-500"
                             :class="active === {{ $faq['id'] }} ? 'rotate-180 text-red-600' : 'text-gray-600'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>
                <div x-show="active === {{ $faq['id'] }}" x-collapse x-cloak>
                    <div class="px-8 pb-8 text-base lg:text-lg text-gray-400 leading-relaxed border-t border-white/5 pt-4">
                        {{ $faq['a'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- The "Narrative-Grade" CTA --}}
<div class="mt-32 mb-16 py-20 border-t border-white/5 relative overflow-hidden">
    {{-- Background Decorative Text --}}
    <div class="absolute -bottom-10 -right-10 opacity-[0.02] select-none pointer-events-none">
        <span class="text-[15rem] font-black tracking-tighter">DIRECT</span>
    </div>

    <div class="flex flex-col lg:flex-row items-end justify-between gap-12 relative z-10">
        <div class="max-w-3xl">
            <h3 class="text-sm font-black text-red-600 uppercase tracking-[0.4em] mb-8 flex items-center gap-4">
                Inquiries
                <span class="h-px w-12 bg-red-600/30"></span>
            </h3>
            <h2 class="text-4xl lg:text-6xl font-bold text-white leading-[1.1] tracking-tight">
                Have a question <br>
                <span class="text-gray-500 italic font-light">unanswered?</span>
            </h2>
            <p class="mt-8 text-lg text-gray-400 max-w-xl leading-relaxed">
                Our team operates with the same "strike and read" precision as our branding strategies. We are available 24/7 for direct consultation.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-6 w-full lg:w-auto">
            {{-- Minimalist WhatsApp Link --}}
            <a href="https://wa.me/254710878056"
               class="group flex items-center justify-between gap-12 p-1 border-b border-white/10 hover:border-green-500 transition-all duration-500">
                <span class="text-sm font-bold uppercase tracking-widest text-gray-400 group-hover:text-white transition">WhatsApp</span>
                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-green-600 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </div>
            </a>

            {{-- Minimalist Contact Link --}}
            <a href="{{ route('contact') }}"
               class="group flex items-center justify-between gap-12 p-1 border-b border-white/10 hover:border-red-600 transition-all duration-500">
                <span class="text-sm font-bold uppercase tracking-widest text-gray-400 group-hover:text-white transition">Contact Us</span>
                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover:bg-red-600 transition-colors">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>

        {{-- Professional Ethics Footer (Matching Previous Pages) --}}
        <div class="border-t border-white/5 pt-16 mt-32 grid lg:grid-cols-3 gap-12 opacity-80">
            <div>
                <h2 class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-4">Code of Ethics</h2>
                <p class="text-gray-500 text-xs italic leading-relaxed">"We maintain our professional ethics to ensure we make our line straight not to hinder lines of other professionals."</p>
            </div>
            <div class="lg:col-span-2 grid sm:grid-cols-2 gap-12 text-sm text-gray-500">
                <p>Branding is more than just visuals; it is an economic driver. We ensure every brand strategy is backed by market-ready skills and professional integrity.</p>
                <p>Headquartered in Nairobi, Kenya, we are dedicated to re-branding Africa as a global powerhouse of talent and services.</p>
            </div>
        </div>

    </div>
</div>
@endsection
