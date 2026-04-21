@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Balanced Heading Block --}}
        <div class="mb-16 lg:mb-24 max-w-7xl">
            <div class="flex items-center gap-3 mb-6">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <h1 class="text-sm font-bold text-gray-400 uppercase tracking-[0.2em]">
                    About Us
                </h1>
            </div>
            <h2 class="text-3xl lg:text-5xl font-bold text-white mb-8 leading-tight tracking-tight">
                Ensuring Kenya remains the <span class="text-red-600">economic pillar</span> of the continent.
            </h2>
            <p class="text-lg lg:text-xl text-gray-400 leading-relaxed ">
                We bridge the gap between creative excellence and economic productivity. While the branding industry grows, we ensure that individual and corporate brands drive national impact.
            </p>
        </div>

        {{-- Who We Are: Balanced Image + Text --}}
        <div class="grid lg:grid-cols-2 gap-16 items-start mb-32">
            <div class="relative">
                <div class="absolute -inset-4 bg-red-600/5 rounded-3xl blur-2xl"></div>
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=1200"
                     alt="Branding Strategy"
                     class="relative rounded-2xl border border-white/5 grayscale opacity-90 shadow-2xl">
                <div class="absolute -bottom-6 -right-6 bg-slate-900 border border-white/10 p-5 rounded-2xl hidden md:block">
                    <p class="text-2xl font-black text-red-600">HQ</p>
                    <p class="text-xs uppercase tracking-widest text-gray-500">Nairobi, Kenya</p>
                </div>
            </div>
            <div class="space-y-8">
                <h3 class="text-sm font-black uppercase tracking-widest text-red-600">The Mission</h3>
                <p class="text-lg text-gray-300 leading-relaxed">
                    <strong class="text-white">Go-Getter Brand Africa</strong> is a vibrant branding and empowerment company. Our core purpose is to cultivate healthy individual and company brands that contribute directly to the national GDP.
                </p>
                <div class="h-1 w-12 bg-red-600"></div>
                <p class="text-base text-gray-400 leading-relaxed">
                    We have witnessed significant growth in the branding industry. We exist to channel that growth into measurable economic success for entrepreneurs across Africa, turning creative ideas into industrial realities.
                </p>
            </div>
        </div>

        {{-- Vision & Mission: Balanced Cards --}}
        <div class="grid md:grid-cols-2 gap-8 mb-32">
            <div class="p-12 rounded-3xl bg-slate-900/30 border border-white/5 border-l-4 border-l-red-600 transition-all">
                <h3 class="text-xl font-bold mb-6 text-white tracking-tight">Vision</h3>
                <p class="text-gray-400 text-base lg:text-lg leading-relaxed">
                    To be the most valued, vital and vibrant service provider. We transform the mindset of the young generation, moving from unemployment to active, productive modes of thinking.
                </p>
            </div>
            <div class="p-12 rounded-3xl bg-slate-900/30 border border-white/5 border-l-4 border-l-green-500 transition-all">
                <h3 class="text-xl font-bold mb-6 text-white tracking-tight">Mission</h3>
                <p class="text-gray-400 text-base lg:text-lg leading-relaxed">
                    To provide a trusted brand that re-brands Kenya not just as an exporter of physical goods, but as a premier exporter of world-class brands.
                </p>
            </div>
        </div>

        {{-- Executive Summary: Balanced Grid --}}
        <div class="mb-32">
            <h2 class="text-2xl lg:text-3xl font-bold mb-12 tracking-tight uppercase">Executive Summary</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $expertise = [
                        ['01', 'Entrepreneurship', 'Focusing on business planning and finance for women, youth, and PLWD-led businesses.'],
                        ['02', 'Skills Development', 'Training in digital literacy and leadership to prepare individuals for self-employment.'],
                        ['03', 'Finance Access', 'Connecting entrepreneurs to financial institutions for expansion grants and products.'],
                        ['04', 'Market Linkage', 'Connecting youth and women with potential customers and partners to expand reach.'],
                        ['05', 'Advocacy', 'Promoting policies that support economic empowerment across all sectors.'],
                        ['06', 'Awarding', 'Giving credit where necessary to improve altitude and productivity for result-oriented growth.'],
                    ];
                @endphp

                @foreach($expertise as $item)
                <div class="p-8 rounded-2xl bg-slate-900/20 border border-white/5 group hover:bg-slate-900/40 transition">
                    <div class="text-xs font-bold text-red-600 mb-4 tracking-[0.2em]">{{ $item[0] }}</div>
                    <h4 class="text-lg font-bold text-white mb-3 tracking-tight">{{ $item[1] }}</h4>
                    <p class="text-sm lg:text-base text-gray-500 leading-relaxed">{{ $item[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Target Audience: Side Stroke Color Cards --}}
        <div class="grid lg:grid-cols-4 gap-6 mb-32">
            {{-- Women --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-white/5 border-l-4 border-l-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-3 uppercase text-xs tracking-widest">Target: Women</h5>
                <p class="text-sm lg:text-base text-gray-400 leading-relaxed">Empowering specific and special programs for all classes of women.</p>
            </div>
            {{-- Youth --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-white/5 border-l-4 border-l-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-3 uppercase text-xs tracking-widest">Target: Youths</h5>
                <p class="text-sm lg:text-base text-gray-400 leading-relaxed">Focusing on digital skills and entrepreneurship for various backgrounds.</p>
            </div>
            {{-- PLWDs --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-white/5 border-l-4 border-l-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-3 uppercase text-xs tracking-widest">Target: PLWDs</h5>
                <p class="text-sm lg:text-base text-gray-400 leading-relaxed">Supporting intellectual properties and physical business placements.</p>
            </div>
            {{-- Leaders --}}
            <div class="p-8 rounded-2xl bg-slate-900 border border-white/5 border-l-4 border-l-red-600 shadow-xl">
                <h5 class="font-bold text-white mb-3 uppercase text-xs tracking-widest">Target: Leaders</h5>
                <p class="text-sm lg:text-base text-gray-400 leading-relaxed">Congratulating performance and correcting non-productivity.</p>
            </div>
        </div>

        {{-- Professional Ethics: Balanced Footer --}}
        <div class="border-t border-white/5 pt-16 flex flex-col lg:flex-row gap-12">
            <div class="lg:w-1/3">
                <h2 class="text-sm font-black text-red-600 uppercase tracking-widest mb-4">Code of Ethics</h2>
                <p class="text-gray-400 text-base italic leading-relaxed">"We maintain our professional ethics to ensure we make our line straight not to hinder lines of other professionals."</p>
            </div>
            <div class="lg:w-2/3 grid sm:grid-cols-2 gap-12">
                <p class="text-sm lg:text-base text-gray-500 leading-relaxed">Our branding services use a combination of research tools and feet-on-the-street experience to provide multi-layered brand plans.</p>
                <p class="text-sm lg:text-base text-gray-500 leading-relaxed">Our flexible approach allows clients to mix strategies, strike, withdraw, and read results with precise market timing.</p>
            </div>
        </div>

    </div>
</div>
@endsection
