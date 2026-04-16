<footer class="relative bg-gradient-to-br from-gray-900 to-gray-800 text-white overflow-hidden">
  <!-- Animated background elements -->
  <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20">
    <div class="absolute top-20 left-10 w-32 h-32 bg-blue-500 rounded-full filter blur-3xl animate-float1"></div>
    <div class="absolute top-40 right-20 w-40 h-40 bg-purple-500 rounded-full filter blur-3xl animate-float2"></div>
    <div class="absolute bottom-10 left-1/2 w-48 h-48 bg-cyan-500 rounded-full filter blur-3xl animate-float3"></div>
  </div>

  <!-- Main footer content -->
  <div class="relative max-w-7xl mx-auto px-6 py-16 sm:py-20 lg:px-8">
    <!-- Grid layout -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-12">

      <!-- Logo/Name section with interactive effect -->
      <div class="group">
        <div class="flex items-center space-x-2 mb-6">
             <img src="{{asset('go-getter-logo.png')}}" alt="Go Getter Logo" class="w-auto h-16">
        </div>
        <p class="text-gray-300 mb-6">Innovating digital experiences one line of code at a time.</p>

        <!-- Social media with hover glow -->
        <div class="flex space-x-4">
          <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-blue-500 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-sky-500 hover:shadow-lg hover:shadow-sky-500/30 transition-all duration-300">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
          </a>
          <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-purple-500 hover:shadow-lg hover:shadow-purple-500/30 transition-all duration-300">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/></svg>
          </a>
        </div>
      </div>

      <!-- Quick links with hover effect -->
      <div>
        <h3 class="text-lg font-semibold mb-6 relative inline-block">
          <span class="relative z-10">Quick Links</span>
          <span class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></span>
        </h3>
        <ul class="space-y-3">
          <li><a href="{{route('about')}}" class="text-gray-300 hover:text-white hover:pl-2 transition-all duration-300 flex items-center">
            <span class="w-1 h-1 bg-blue-500 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition duration-300"></span>
            About</a></li>
          <li><a href="{{route('faqs')}}" class="text-gray-300 hover:text-white hover:pl-2 transition-all duration-300 flex items-center">
            <span class="w-1 h-1 bg-blue-500 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition duration-300"></span>
            FAQs</a></li>
          <li><a href="#" class="text-gray-300 hover:text-white hover:pl-2 transition-all duration-300 flex items-center">
            <span class="w-1 h-1 bg-blue-500 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition duration-300"></span>
            Services</a></li>
          <li><a href="{{route('contact')}}" class="text-gray-300 hover:text-white hover:pl-2 transition-all duration-300 flex items-center">
            <span class="w-1 h-1 bg-blue-500 rounded-full mr-2 opacity-0 group-hover:opacity-100 transition duration-300"></span>
            Contact</a></li>
        </ul>
      </div>

      <!-- Contact info with animated icons -->
      <div>
        <h3 class="text-lg font-semibold mb-6">Get in Touch</h3>
        <ul class="space-y-4">
          <li class="flex items-start">
            <div class="flex-shrink-0 mt-1">
              <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center animate-pulse">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm text-gray-300">Email</p>
              <a href="mailto:info@gogetterbrandafrica.co.ke" class="text-white hover:text-blue-400 transition">info@gogetterbrandafrica.co.ke</a>
            </div>
          </li>
          <li class="flex items-start">
            <div class="flex-shrink-0 mt-1">
              <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center animate-pulse" style="animation-delay: 0.2s">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm text-gray-300">Phone</p>
              <a href="tel:+254710878056" class="text-white hover:text-blue-400 transition">+254 710 878 056</a>
            </div>
          </li>
        </ul>
      </div>

      <!-- Newsletter with floating input -->
        <div>
            @livewire('subscriber')
        </div>

    </div>

    <!-- Bottom section with animated copyright -->
    <div class="mt-16 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
      <p class="text-gray-400 text-sm mb-4 md:mb-0">
        &copy; <span id="year" class="text-blue-400"></span> Go-getter Brand Africa. All rights reserved | Developed by<span class="text-blue-400">
            <a href="https://enthelotcloud.com" class="hover:text-blue-400 transition"> Enthelot Cloud</a>
        </span>
      </p>
      <div class="flex space-x-6">
        <a href="{{route('privacy')}}" class="text-gray-400 hover:text-white transition">Privacy Policy</a>
        <a href="{{route('terms')}}" class="text-gray-400 hover:text-white transition">Terms of Service</a>
      </div>
    </div>
  </div>

  <!-- Floating orb that follows cursor -->
  <div class="orb absolute w-64 h-64 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 filter blur-3xl pointer-events-none"></div>

  <style>
    /* Animations */
    @keyframes float1 {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(20px, 20px); }
    }
    @keyframes float2 {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(-15px, 15px); }
    }
    @keyframes float3 {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(0, -20px); }
    }
    .animate-float1 { animation: float1 8s ease-in-out infinite; }
    .animate-float2 { animation: float2 10s ease-in-out infinite; }
    .animate-float3 { animation: float3 12s ease-in-out infinite; }

    /* Orb follow effect */
    .orb {
      transform: translate(-50%, -50%);
      opacity: 0.3;
      transition: transform 0.1s ease-out;
    }
  </style>

  <script>
    // Update copyright year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Mouse follow orb effect
    document.addEventListener('mousemove', (e) => {
      const orb = document.querySelector('.orb');
      orb.style.left = `${e.clientX}px`;
      orb.style.top = `${e.clientY}px`;
    });
  </script>
</footer>
