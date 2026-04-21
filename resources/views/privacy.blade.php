@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Standard Header --}}
        <div class="border-b border-white/10 pb-8 mb-12">
            <h1 class="text-3xl lg:text-4xl font-bold mb-4">Privacy Policy</h1>
            <p class="text-gray-400">Last Updated: April 21, 2026</p>
        </div>

        <div class="space-y-12 text-gray-300 leading-relaxed">

            {{-- Introduction --}}
            <section>
                <p>
                    Go-Getter Brand Africa ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how your personal information is collected, used, and disclosed by Go-Getter Brand Africa.
                </p>
            </section>

            {{-- 1. Information Collection --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">1. Information We Collect</h2>
                <p class="mb-4">We collect information that you provide directly to us when you fill out a form, request a consultation, or communicate with us. This may include:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Name and contact information (Email, Phone Number)</li>
                    <li>Company details and job title</li>
                    <li>Project requirements and branding preferences</li>
                    <li>Any other information you choose to provide</li>
                </ul>
            </section>

            {{-- 2. How We Use Your Information --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">2. How We Use Your Information</h2>
                <p class="mb-4">We use the information we collect to:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Respond to your comments, questions, and requests</li>
                    <li>Send you technical notices, updates, and administrative messages</li>
                    <li>Communicate with you about products, services, and events</li>
                </ul>
            </section>

            {{-- 3. Data Protection --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">3. Data Protection</h2>
                <p>
                    We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access, disclosure, alteration, and destruction. We do not sell your personal data to third parties.
                </p>
            </section>

            {{-- 4. Your Rights --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">4. Your Rights</h2>
                <p>
                    In accordance with the Kenya Data Protection Act, you have the right to access, correct, or delete your personal data. You may also object to or restrict certain processing of your data.
                </p>
            </section>

            {{-- 5. Cookies --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">5. Cookies</h2>
                <p>
                    We use cookies to improve your experience on our website. You can set your browser to refuse all or some browser cookies, but this may affect how the website functions.
                </p>
            </section>

            {{-- 6. Contact Us --}}
            <section class="bg-slate-900/50 p-8 rounded-xl border border-white/5">
                <h2 class="text-xl font-bold text-white mb-4">6. Contact Us</h2>
                <p class="mb-4">
                    If you have any questions about this Privacy Policy, please contact us at:
                </p>
                <ul class="space-y-1 text-gray-400">
                    <li>Email: <span class="text-white">info@gogetterbrandafrica.co.ke</span></li>
                    <li>Phone: <span class="text-white">+254 710 878 056</span></li>
                    <li>Location: Nairobi, Kenya</li>
                </ul>
            </section>

        </div>

        {{-- Simple Footer Back Link --}}
        <div class="mt-16 pt-8 border-t border-white/10 text-center">
            <a href="{{ url('/') }}" class="text-red-500 hover:text-red-400 font-medium text-sm transition">
                &larr; Return to Home
            </a>
        </div>

    </div>
</div>
@endsection
