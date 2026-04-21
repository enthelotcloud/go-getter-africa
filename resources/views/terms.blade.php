@extends('layouts.guest.app')

@section('content')
<div class="bg-slate-950 text-white selection:bg-red-600/30 min-h-screen">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">

        {{-- Standard Header --}}
        <div class="border-b border-white/10 pb-8 mb-12">
            <h1 class="text-3xl lg:text-4xl font-bold mb-4">Terms of Service</h1>
            <p class="text-gray-400">Effective Date: April 21, 2026</p>
        </div>

        <div class=" space-y-12 text-gray-300 leading-relaxed">

            {{-- Introduction --}}
            <section>
                <p>
                    Welcome to Go-Getter Brand Africa. By accessing our website and engaging our services, you agree to comply with and be bound by the following terms and conditions. If you disagree with any part of these terms, please do not use our services.
                </p>
            </section>

            {{-- 1. Service Engagement --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">1. Service Engagement</h2>
                <p class="mb-4">Go-Getter Brand Africa provides branding, digital literacy training, and corporate empowerment programs. Engagement begins only upon:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Execution of a formal service agreement or quote acceptance.</li>
                    <li>Receipt of the required initial deposit.</li>
                    <li>Provision of all necessary brand assets and information by the client.</li>
                </ul>
            </section>

            {{-- 2. Intellectual Property --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">2. Intellectual Property Rights</h2>
                <p class="mb-4">
                    Unless otherwise stated, Go-Getter Brand Africa owns the intellectual property rights for all material, training modules, and strategies produced during our operations.
                </p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Upon full payment, clients are granted a license to use deliverables for their intended business purpose.</li>
                    <li>Our proprietary "strike and read" methodologies and training frameworks remain our exclusive property.</li>
                    <li>Unauthorized reproduction or distribution of our materials is strictly prohibited.</li>
                </ul>
            </section>

            {{-- 3. Payment Terms --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">3. Payment Terms & Refunds</h2>
                <p class="mb-4">We operate on a professional fee structure designed to ensure project momentum:</p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>All invoices are payable within the timeframe specified in your quote.</li>
                    <li>Deposits are non-refundable once creative or strategic work has commenced.</li>
                    <li>We reserve the right to suspend services if payments are overdue.</li>
                </ul>
            </section>

            {{-- 4. Limitation of Liability --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">4. Limitation of Liability</h2>
                <p>
                    Go-Getter Brand Africa will not be held liable for any consequential, indirect, or special loss or damage arising under these terms and conditions. While we provide high-level branding strategies, the ultimate commercial success of the brand depends on the client’s execution and market variables beyond our control.
                </p>
            </section>

            {{-- 5. Professional Ethics --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">5. Professional Ethics</h2>
                <p>
                    In line with our code of ethics, we maintain professional integrity in all our dealings. We do not engage in branding practices that infringe upon the rights of other professionals or established global standards.
                </p>
            </section>

            {{-- 6. Governing Law --}}
            <section>
                <h2 class="text-xl font-bold text-white mb-4">6. Governing Law</h2>
                <p>
                    These terms and conditions are governed by and construed in accordance with the laws of Kenya. Any disputes relating to these terms shall be subject to the exclusive jurisdiction of the courts of Kenya.
                </p>
            </section>

            {{-- Contact Information --}}
            <section class="bg-slate-900/50 p-8 rounded-xl border border-white/5">
                <h2 class="text-xl font-bold text-white mb-4">Contact Legal</h2>
                <p class="mb-4">
                    If you have questions regarding these terms, please contact our legal department:
                </p>
                <ul class="space-y-1 text-gray-400">
                    <li>Email: <span class="text-white">info@gogetterbrandafrica.co.ke</span></li>
                    <li>Address: Nairobi, Kenya</li>
                </ul>
            </section>

        </div>

        {{-- Return Link --}}
        <div class="mt-16 pt-8 border-t border-white/10 text-left">
            <a href="{{ url('/') }}" class="text-red-600 hover:text-red-500 font-bold text-sm transition flex items-center gap-2">
                &larr; BACK TO HOME
            </a>
        </div>

    </div>
</div>
@endsection
