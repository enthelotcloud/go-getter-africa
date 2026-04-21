@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">

        {{-- Consistent Heading Block --}}
        <div class="mb-12 lg:mb-20">
            <div class="flex items-center gap-3 mb-4">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <h1 class="text-xl sm:text-2xl lg:text-2xl font-bold text-white tracking-wider">
                    About us
                </h1>
            </div>
            <p class="text-lg lg:text-xl text-gray-400 ">
                Kenya remains the key economic pillar to the East African countries and the entire African continent. We are here to ensure that impact is felt.
            </p>
        </div>

        {{-- Who We Are: Image + Text Split --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-24">
            <div class="relative">
                <div class="absolute -inset-4 bg-red-600/10 rounded-3xl blur-2xl"></div>
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=800"
                     alt="Branding Strategy"
                     class="relative rounded-2xl border border-white/10 shadow-2xl grayscale hover:grayscale-0 transition duration-700">
                <div class="absolute -bottom-6 -right-6 bg-slate-900 border border-white/10 p-6 rounded-2xl hidden md:block">
                    <p class="text-3xl font-black text-red-600">HQ</p>
                    <p class="text-xs uppercase tracking-widest text-gray-400">Nairobi, Kenya</p>
                </div>
            </div>
            <div class="space-y-6">
                <h2 class="text-3xl lg:text-4xl font-bold">Who Are We?</h2>
                <p class="text-gray-300 text-lg leading-relaxed">
                    <strong class="text-white">Go-Getter Brand Africa</strong> is a vibrant branding and empowerment company based in Nairobi - Kenya. Its main purpose is to ensure we have a healthy brand both individual and companies that with make great impact to contribute into our national GDP.
                </p>
                <p class="text-gray-400">
                    We have witnessed a great growth in branding industry without government realization. We bridge the gap between creative excellence and economic productivity.
                </p>
            </div>
        </div>

        {{-- Vision & Mission: High-Contrast Cards --}}
        <div class="grid md:grid-cols-2 gap-8 mb-24">
            <div class="p-8 rounded-3xl bg-slate-900/50 border border-white/5 hover:border-red-600/30 transition-all group">

                <h3 class="text-2xl font-bold mb-4 uppercase tracking-tight">Vision</h3>
                <p class="text-gray-400 leading-relaxed">
                    To be the most valued, vital and vibrant service provider in terms of branding companies and individuals. Making unemployed employed, mode thinking to our young generation and improving an active mind to its productive mode.
                </p>
            </div>
            <div class="p-8 rounded-3xl bg-slate-900/50 border border-white/5 hover:border-green-500/30 transition-all group">
                
                <h3 class="text-2xl font-bold mb-4 uppercase tracking-tight">Mission</h3>
                <p class="text-gray-400 leading-relaxed">
                    To provide a trusted and indispensable moving brand that will re-brand Kenya as not only a physical goods exporter but also a brand exporter.
                </p>
            </div>
        </div>


        {{-- Stats Section with Auto-Counter Animation --}}
<section class="py-10 border-y border-white/5 bg-slate-900/20"
         x-data="{
            observed: false,
            stats: [
                { label: 'Years Experience', target: 5, suffix: '+', current: 0 },
                { label: 'Brands Transformed', target: 150, suffix: '+', current: 0 },
                { label: ' Workshops', target: 40, suffix: '+', current: 0 },
                { label: 'Market Reach', target: 12, suffix: '+', current: 0 }
            ],
            startCounter() {
                this.stats.forEach(stat => {
                    let start = 0;
                    let end = stat.target;
                    let duration = 2000; // 2 seconds
                    let increment = end / (duration / 16);
                    let timer = setInterval(() => {
                        start += increment;
                        if (start >= end) {
                            stat.current = end;
                            clearInterval(timer);
                        } else {
                            stat.current = Math.floor(start);
                        }
                    }, 16);
                });
            }
         }"
         x-intersect.once="startCounter()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">

            <template x-for="stat in stats" :key="stat.label">
                <div class="text-center group">
                    <div class="relative inline-block">
                        <span class="text-4xl lg:text-6xl font-black text-white tracking-tighter"
                              x-text="stat.current + stat.suffix">
                            0
                        </span>
                        {{-- Decorative underline that expands on hover --}}
                        <div class="h-1.5 w-8 bg-red-600 mx-auto mt-2 rounded-full group-hover:w-full transition-all duration-500"></div>
                    </div>
                    <p class="mt-4 text-xs lg:text-sm font-bold uppercase tracking-[0.2em] text-gray-500 group-hover:text-red-500 transition-colors"
                       x-text="stat.label">
                    </p>
                </div>
            </template>

        </div>
    </div>
</section>


        {{-- Executive Summary: Grid of Expertise --}}
        <div class="mb-24 mt-24"">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-4xl font-bold mb-4 uppercase tracking-tighter">Executive Summary</h2>
                    <p class="text-gray-400">Supporting individuals and organizations through empowered communication and market-ready skills development.</p>
                </div>
                <div class="text-right">
                    <span class="text-6xl font-black text-white/5">EXSUM</span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Item 1 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">01.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Entrepreneurship</h4>
                    <p class="text-sm text-gray-400">Focusing on business planning, marketing, and finance management for women, youth, and PLWD-led businesses.</p>
                </div>
                {{-- Item 2 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">02.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Skills Development</h4>
                    <p class="text-sm text-gray-400">Training in digital literacy, vocational skills, and leadership to prepare individuals for self-employment.</p>
                </div>
                {{-- Item 3 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">03.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Finance Access</h4>
                    <p class="text-sm text-gray-400">Connecting entrepreneurs to financial institutions for grants and products to expand business operations.</p>
                </div>
                {{-- Item 4 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">04.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Market Linkage</h4>
                    <p class="text-sm text-gray-400">Connecting youth and women with potential customers and partners to expand market reach.</p>
                </div>
                {{-- Item 5 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">05.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Advocacy</h4>
                    <p class="text-sm text-gray-400">Engaging in efforts to promote policies that support economic empowerment across all sectors.</p>
                </div>
                {{-- Item 6 --}}
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-white/5 hover:bg-slate-900 transition">
                    <div class="text-red-600 mb-4 font-black">06.</div>
                    <h4 class="text-lg font-bold text-white mb-2 uppercase">Awarding</h4>
                    <p class="text-sm text-gray-400">Giving credit where necessary to improve altitude and productivity for result-oriented growth.</p>
                </div>
            </div>
        </div>

        {{-- Target Audience: Icon-Led Cards --}}
        <div class="grid lg:grid-cols-4 gap-6 mb-24">
            <div class="bg-slate-900 p-8 rounded-2xl border-t-2 border-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-4 uppercase text-xs tracking-widest">Target: Women</h5>
                <p class="text-sm text-gray-400 leading-relaxed">Empowering specific and special programs for all classes of women.</p>
            </div>
            <div class="bg-slate-900 p-8 rounded-2xl border-t-2 border-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-4 uppercase text-xs tracking-widest">Target: Youths</h5>
                <p class="text-sm text-gray-400 leading-relaxed">Focusing on digital skills and entrepreneurship for various backgrounds.</p>
            </div>
            <div class="bg-slate-900 p-8 rounded-2xl border-t-2 border-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-4 uppercase text-xs tracking-widest">Target: PLWDs</h5>
                <p class="text-sm text-gray-400 leading-relaxed">Supporting intellectual properties and physical business placements.</p>
            </div>
            <div class="bg-slate-900 p-8 rounded-2xl border-t-2 border-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-4 uppercase text-xs tracking-widest">Target: Leaders</h5>
                <p class="text-sm text-gray-400 leading-relaxed">Congratulating performance and correcting non-productivity.</p>
            </div>
        </div>

        {{-- Professional Ethics Footer --}}
        <div class="border-t border-white/10 pt-16 flex flex-col lg:flex-row gap-12">
            <div class="lg:w-1/3">
                <h2 class="text-sm font-black text-red-600 uppercase tracking-widest mb-4">Code of Ethics</h2>
                <p class="text-gray-500 text-sm italic">"We maintain our professional ethics to ensure we make our line straight not to hinder lines of other professionals."</p>
            </div>
            <div class="lg:w-2/3 grid sm:grid-cols-2 gap-8 text-sm text-gray-400">
                <p>Our branding services mavericks use a combination of research tools and feet-on-the-street experience to provide multi-layered brand plans.</p>
                <p>Our flexible and nimble approach offers our clients the ability to mix strategies, strike, withdraw, and read results with Bloom ideas.</p>
            </div>
        </div>

    </div>
</div>
@endsection
