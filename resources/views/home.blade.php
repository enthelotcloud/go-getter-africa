@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    {{-- Hero Slider Section --}}
    <section x-data="{
                activeSlide: 0,
                slides: [
                    {
                        image: 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=1600',
                        subtitle: 'Go-Getter Brand Africa',
                        title: 'Strategic branding for the African economic pillar.',
                        desc: 'We empower individuals and organizations to scale their impact on the national GDP through healthy branding and structured empowerment.'
                    },
                    {
                        image: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&q=80&w=1600',
                        subtitle: 'Youth & Women Empowerment',
                        title: 'Bridging the gap to financial independence.',
                        desc: 'Providing resources, opportunities, and digital literacy to help you build your business and achieve economic freedom.'
                    },
                    {
                        image: 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=1600',
                        subtitle: 'Market Linkage & Strategy',
                        title: 'Value = Price x Targeting.',
                        desc: 'Our nimble approach allows clients to mix strategies, strike, withdraw, and read results with precise market timing.'
                    }
                ],
                startSlider() {
                    setInterval(() => {
                        this.activeSlide = this.activeSlide === this.slides.length - 1 ? 0 : this.activeSlide + 1;
                    }, 6000);
                }
             }"
             x-init="startSlider()"
             class="relative h-[80vh] min-h-[600px] w-full overflow-hidden">

        {{-- Slides Loop --}}
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index"
                 x-transition:enter="transition opacity-100 duration-1000 ease-in-out"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition opacity-100 duration-1000 ease-in-out"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 w-full h-full">

                {{-- Background Image --}}
                <img :src="slide.image" alt="Hero Image" class="absolute inset-0 w-full h-full object-cover grayscale opacity-70">

                {{-- Gradient Overlay (Dark on left, transparent on right) --}}
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent"></div>

                {{-- Content Container --}}
                <div class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <h1 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]" x-text="slide.subtitle"></h1>
                        </div>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight mb-6" x-text="slide.title"></h2>
                        <p class="text-base lg:text-lg text-gray-400 leading-relaxed mb-10 max-w-xl" x-text="slide.desc"></p>

                        <div class="flex items-center gap-6">
                            <a href="{{ route('contact') }}" class="text-sm font-bold text-white border-b-2 border-red-600 pb-1 hover:text-red-500 transition">
                                Start a Project
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Slider Indicators --}}
        <div class="absolute bottom-10 left-0 w-full">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-3">
                <template x-for="(slide, index) in slides" :key="index">
                    <button @click="activeSlide = index"
                            class="h-1 transition-all duration-500 rounded-full"
                            :class="activeSlide === index ? 'w-8 bg-red-600' : 'w-4 bg-white/30 hover:bg-white/50'"></button>
                </template>
            </div>
        </div>
    </section>

    {{-- About Us Section --}}
    <section class="py-24 border-b border-white/5 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-red-600">About Us</h3>
                    <h4 class="text-2xl font-bold text-white leading-snug">
                        Making non-brands become well-known brands.
                    </h4>
                </div>
                <div class="space-y-6">
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Based in Nairobi, Kenya, Go-Getter Brand Africa is an organization that supports individuals and organizations to grow their brand. We focus on private interest or community by empowering super speech, diversified thinking, and sharp execution across all forms of communication.
                    </p>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Our vision is to be the most valued, vital, and vibrant service provider. We engage Youths, Women, and PLWDs in our community to improve the productivity of a nation through entrepreneurial workshops and market linkages.
                    </p>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white hover:text-red-500 transition group">
                        Read Our Full Story
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Our Services Section (Cards) --}}
    <section class="py-24 bg-slate-900/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-16 space-y-4">
                <h3 class="text-sm font-black uppercase tracking-widest text-red-600">Our Services</h3>
                <h4 class="text-2xl font-bold text-white">Empowering Growth & Productivity</h4>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Service Card 1 --}}
                <div class="bg-slate-900/50 border border-white/5 p-8 rounded-2xl hover:border-white/20 transition flex flex-col h-full">
                    <div class="w-10 h-10 rounded-lg bg-red-600/10 flex items-center justify-center mb-6">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-3">Brand Strategy & Identity</h5>
                    <p class="text-sm text-gray-500 leading-relaxed mb-8 flex-grow">
                        We use a combination of industry research and feet-on-the-street experience to provide multi-layered brand plans tailored to right opportunities.
                    </p>
                    <a href="https://wa.me/254710878056?text=Hi%20Go-Getter%20Brand%20Africa,%20I'd%20like%20to%20book%20a%20consultation%20for%20Brand%20Strategy%20%26%20Identity."
                       target="_blank"
                       class="text-xs font-bold text-white uppercase tracking-wider py-3 px-4 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition inline-flex items-center gap-2 w-max">
                        Book Service
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                {{-- Service Card 2 --}}
                <div class="bg-slate-900/50 border border-white/5 p-8 rounded-2xl hover:border-white/20 transition flex flex-col h-full">
                    <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center mb-6">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-3">Digital Skills Training</h5>
                    <p class="text-sm text-gray-500 leading-relaxed mb-8 flex-grow">
                        We offer training in digital literacy and vocational skills, preparing individuals from different backgrounds for the modern employment landscape.
                    </p>
                    <a href="https://wa.me/254710878056?text=Hi%20Go-Getter%20Brand%20Africa,%20I'd%20like%20to%20book%20a%20session%20for%20Digital%20Skills%20Training."
                       target="_blank"
                       class="text-xs font-bold text-white uppercase tracking-wider py-3 px-4 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition inline-flex items-center gap-2 w-max">
                        Book Service
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                {{-- Service Card 3 --}}
                <div class="bg-slate-900/50 border border-white/5 p-8 rounded-2xl hover:border-white/20 transition flex flex-col h-full">
                    <div class="w-10 h-10 rounded-lg bg-red-600/10 flex items-center justify-center mb-6">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h5 class="text-lg font-bold text-white mb-3">Entrepreneurship & Linkage</h5>
                    <p class="text-sm text-gray-500 leading-relaxed mb-8 flex-grow">
                        Supporting youth, women, and PLWDs in business planning, finance management, and connecting them to potential customers and financial institutions.
                    </p>
                    <a href="https://wa.me/254710878056?text=Hi%20Go-Getter%20Brand%20Africa,%20I'd%20like%20to%20book%20a%20consultation%20for%20Entrepreneurship%20%26%20Market%20Linkage."
                       target="_blank"
                       class="text-xs font-bold text-white uppercase tracking-wider py-3 px-4 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10 transition inline-flex items-center gap-2 w-max">
                        Book Service
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </section>

</div>
@endsection
