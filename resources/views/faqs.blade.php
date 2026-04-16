 @extends('layouts.guest.app')
@section('content')
    <div class="py-20 id="faq">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Find answers to our most commonly asked questions.</p>
            </div>

            <div class="max-w-3xl mx-auto">
                <!-- FAQ Item 1 -->
                <div class="mb-6" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex justify-between items-center w-full bg-gray-900 hover:bg-gray-700 px-6 py-4 rounded-lg text-left transition-all duration-200"
                    >
                        <h3 class="font-bold text-lg">How long does installation take?</h3>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="!open">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="open">
                            <i class="fas fa-minus"></i>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-6 pr-4 py-4"
                    >
                        <p class="text-gray-50">Most standard playset installations take between 4-8 hours, depending on the complexity of your chosen design. Our professional installation team will arrive on the scheduled day with all necessary tools and equipment to complete the job efficiently.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="mb-6" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex justify-between items-center w-full bg-gray-900 hover:bg-gray-700 px-6 py-4 rounded-lg text-left transition-all duration-200"
                    >
                        <h3 class="font-bold text-lg">What type of maintenance is required?</h3>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="!open">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="open">
                            <i class="fas fa-minus"></i>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-6 pr-4 py-4"
                    >
                        <p class="text-gray-50">Our vinyl playsets require minimal maintenance. Simply rinse with a garden hose as needed to remove dirt or debris. Unlike wooden sets, there's no need for staining, sealing, or checking for splinters. We recommend checking hardware connections once a year to ensure everything remains tight and secure.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="mb-6" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex justify-between items-center w-full bg-gray-900 hover:bg-gray-700 px-6 py-4 rounded-lg text-left transition-all duration-200"
                    >
                        <h3 class="font-bold text-lg">Do you offer financing options?</h3>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="!open">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="open">
                            <i class="fas fa-minus"></i>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-6 pr-4 py-4"
                    >
                        <p class="text-gray-50">Yes, we offer several financing options to help make your dream playset affordable. During your consultation, our team can explain the various payment plans available, including 0% interest options for qualified customers.</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="mb-6" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex justify-between items-center w-full bg-gray-900 hover:bg-gray-700 px-6 py-4 rounded-lg text-left transition-all duration-200"
                    >
                        <h3 class="font-bold text-lg">What warranty do your playsets include?</h3>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="!open">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="open">
                            <i class="fas fa-minus"></i>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-6 pr-4 py-4"
                    >
                        <p class="text-gray-50">All Demo come with our comprehensive warranty that covers structural components for 15 years, vinyl materials for 10 years, and accessories for 1 year. We stand behind the quality of our products and are committed to your family's satisfaction.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="mb-6" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex justify-between items-center w-full bg-gray-900 hover:bg-gray-700 px-6 py-4 rounded-lg text-left transition-all duration-200"
                    >
                        <h3 class="font-bold text-lg">Can I add accessories later?</h3>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="!open">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-yellow-500" x-show="open">
                            <i class="fas fa-minus"></i>
                        </span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-6 pr-4 py-4"
                    >
                        <p class="text-gray-50">Absolutely! Our playsets are designed to grow with your family. You can easily add or upgrade accessories as your children's interests and abilities develop. From additional swings to challenging climbing features, we make it easy to enhance your playset over time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

