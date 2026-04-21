@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen pb-20">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">

        {{-- Consistent Heading Block --}}
        <div class="mb-12 lg:mb-16">
            <div class="flex items-center gap-3 mb-4">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <h1 class="text-xl sm:text-2xl lg:text-2xl font-bold text-white  tracking-wider">
                    Frequently Asked Questions
                </h1>
            </div>
            <p class="text-lg lg:text-xl text-gray-400">
                Have questions about Go-Getter Brand Africa? We have compiled answers to the most common inquiries regarding our branding and empowerment services.
            </p>
        </div>

        {{-- FAQ Accordion Container --}}
        <div class="space-y-4" x-data="{ active: null }">

            {{-- Question 1 --}}
            <div class="rounded-2xl border border-white/5 bg-slate-900/40 overflow-hidden transition-all duration-300"
                 :class="active === 1 ? 'border-red-600/30 bg-slate-900' : ''">
                <button @click="active !== 1 ? active = 1 : active = null"
                        class="flex w-full items-center justify-between p-6 text-left focus:outline-none">
                    <span class="text-lg font-bold" :class="active === 1 ? 'text-red-500' : 'text-white'">
                        What is Go-Getter Brand Africa?
                    </span>
                    <svg class="h-6 w-6 transform transition-transform duration-300"
                         :class="active === 1 ? 'rotate-180 text-red-500' : 'text-gray-500'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 1" x-collapse x-cloak>
                    <div class="px-6 pb-6 text-gray-400 leading-relaxed">
                        Go-Getter Brand Africa is a vibrant branding and empowerment company based in Nairobi, Kenya. We focus on individual and corporate branding to contribute to the national GDP and ensure African brands can compete globally.
                    </div>
                </div>
            </div>

            {{-- Question 2 --}}
            <div class="rounded-2xl border border-white/5 bg-slate-900/40 overflow-hidden transition-all duration-300"
                 :class="active === 2 ? 'border-red-600/30 bg-slate-900' : ''">
                <button @click="active !== 2 ? active = 2 : active = null"
                        class="flex w-full items-center justify-between p-6 text-left">
                    <span class="text-lg font-bold" :class="active === 2 ? 'text-red-500' : 'text-white'">
                        Who do you target for your empowerment programs?
                    </span>
                    <svg class="h-6 w-6 transform transition-transform" :class="active === 2 ? 'rotate-180 text-red-500' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 2" x-collapse x-cloak>
                    <div class="px-6 pb-6 text-gray-400 leading-relaxed">
                        We primarily target Youths, Women, and People Living with Disabilities (PLWDs). We also work with leaders to enhance productivity and accountability within the community.
                    </div>
                </div>
            </div>

            {{-- Question 3 --}}
            <div class="rounded-2xl border border-white/5 bg-slate-900/40 overflow-hidden transition-all duration-300"
                 :class="active === 3 ? 'border-red-600/30 bg-slate-900' : ''">
                <button @click="active !== 3 ? active = 3 : active = null"
                        class="flex w-full items-center justify-between p-6 text-left">
                    <span class="text-lg font-bold" :class="active === 3 ? 'text-red-500' : 'text-white'">
                        What branding services do you offer?
                    </span>
                    <svg class="h-6 w-6 transform transition-transform" :class="active === 3 ? 'rotate-180 text-red-500' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 3" x-collapse x-cloak>
                    <div class="px-6 pb-6 text-gray-400 leading-relaxed">
                        We offer a flexible and nimble approach that includes digital literacy, market linkage, strategy "strike and read" results, and analytics to quantify the effectiveness of your branding efforts.
                    </div>
                </div>
            </div>

            {{-- Question 4 --}}
            <div class="rounded-2xl border border-white/5 bg-slate-900/40 overflow-hidden transition-all duration-300"
                 :class="active === 4 ? 'border-red-600/30 bg-slate-900' : ''">
                <button @click="active !== 4 ? active = 4 : active = null"
                        class="flex w-full items-center justify-between p-6 text-left">
                    <span class="text-lg font-bold" :class="active === 4 ? 'text-red-500' : 'text-white'">
                        How does the "Value Equation" work?
                    </span>
                    <svg class="h-6 w-6 transform transition-transform" :class="active === 4 ? 'rotate-180 text-red-500' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 4" x-collapse x-cloak>
                    <div class="px-6 pb-6 text-gray-400 leading-relaxed">
                        We believe <strong class="text-white">Value = Price x Targeting</strong>. Our highly skilled analytics team helps clients pinpoint where marketing investments are underperforming so we can take corrective action to maximize ROI.
                    </div>
                </div>
            </div>

        </div>

        {{-- Secondary CTA for unasked questions --}}
        <div class="mt-20 p-8 rounded-3xl bg-red-600/5 border border-red-600/20 text-center">
            <h3 class="text-xl font-bold mb-2 text-white uppercase tracking-tight">Still have questions?</h3>
            <p class="text-gray-400 mb-6">Our team is available 24/7 to discuss your project needs.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://wa.me/254710878056" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition flex items-center gap-2">
                    WhatsApp Support
                </a>
                <a href="{{ route('contact') }}" class="px-6 py-3 border border-red-600 text-red-600 hover:bg-red-600 hover:text-white font-bold rounded-xl transition">
                    Contact Us
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
