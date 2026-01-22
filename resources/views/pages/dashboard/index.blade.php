<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Welcome Section -->
            <div class="mb-8 animate-fade-in">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                    Hello, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-gray-600 text-lg">Ready to practice your IELTS today?</p>
            </div>

            <!-- Latest Score Card -->
            @if($tests->isNotEmpty() && $tests->first()->band
)
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 sm:p-8 text-white mb-8 shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm sm:text-base opacity-90 mb-1">Latest Overall Band Score</p>
                        <p class="text-5xl sm:text-6xl font-bold">{{ $tests->first()->band
 }}</p>
                    </div>
                    <div class="text-right">
                        @if($tests->count() > 1 && $tests->first()->band
 && $tests->skip(1)->first()->overall_band)
                            @php
                                $diff = $tests->first()->band
 - $tests->skip(1)->first()->overall_band;
                            @endphp
                            <div class="bg-white/20 backdrop-blur rounded-full px-4 py-2 inline-flex items-center">
                                @if($diff > 0)
                                    <svg class="w-5 h-5 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold">+{{ number_format($diff, 1) }}</span>
                                @elseif($diff < 0)
                                    <svg class="w-5 h-5 text-red-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-semibold">{{ number_format($diff, 1) }}</span>
                                @else
                                    <span class="font-semibold">—</span>
                                @endif
                            </div>
                            <p class="text-sm mt-2 opacity-80">From last test</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Test CTA Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-8">
                <!-- Speaking Test Card -->
                <a href="{{ route('speaking.test') }}" 
                   class="group bg-white border-2 border-indigo-600 rounded-2xl p-6 sm:p-8 hover:bg-indigo-50 transition-all shadow-md hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-indigo-100 w-14 h-14 rounded-full flex items-center justify-center group-hover:bg-indigo-200 transition-colors group-hover:scale-110 transform duration-300">
                            <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                        </div>
                        <svg class="w-6 h-6 text-indigo-600 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Speaking Test</h3>
                    <p class="text-gray-600">Practice with AI voice recognition</p>
                </a>

                <!-- Writing Test Card -->
                <a href="{{ route('writing.index') }}"
                   class="group bg-white border-2 border-purple-600 rounded-2xl p-6 sm:p-8 hover:bg-purple-50 transition-all shadow-md hover:shadow-xl transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-100 w-14 h-14 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition-colors group-hover:scale-110 transform duration-300">
                            <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>
                        </div>
                        <svg class="w-6 h-6 text-purple-600 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Writing Test</h3>
                    <p class="text-gray-600">Write essays with instant feedback</p>
                </a>
            </div>

            <!-- Quick Stats -->
            @if($tests->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $tests->count() }}</p>
                            <p class="text-xs text-gray-500">Total Tests</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $tests->where('type', 'speaking')->count() }}</p>
                            <p class="text-xs text-gray-500">Speaking</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="bg-purple-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $tests->where('type', 'writing')->count() }}</p>
                            <p class="text-xs text-gray-500">Writing</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $tests->where('overall_band', '>=', 7)->count() }}</p>
                            <p class="text-xs text-gray-500">Band 7+</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Tests Section -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recent Tests</h2>
                    @if($tests->isNotEmpty())
                    <span class="text-sm text-gray-500">{{ $tests->count() }} test{{ $tests->count() !== 1 ? 's' : '' }}</span>
                    @endif
                </div>

                @if($tests->isEmpty())
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No tests yet</h3>
                        <p class="text-gray-500 mb-6">Start your first test to see your progress here!</p>
                        <div class="flex gap-3 justify-center flex-wrap">
                            <a href="{{ route('speaking.test') }}" 
                               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                </svg>
                                Speaking Test
                            </a>
                            <a href="{{ route('writing.index') }}" 
                               class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                                Writing Test
                            </a>
                        </div>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($tests as $test)
                            @php
                                // Determine the correct result route based on test type
                               $resultRoute = $test->result_route;

                            @endphp
                            <a href="{{ $resultRoute }}" 
                               class="block p-4 sm:p-6 hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                                            <span class="inline-flex items-center gap-2 {{ $test->type === 'speaking' ? 'bg-indigo-100 text-indigo-600' : 'bg-purple-100 text-purple-600' }} px-3 py-1 rounded-full text-sm font-medium">
                                                @if($test->type === 'speaking')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                                    </svg>
                                                @endif
                                                {{ ucfirst($test->type) }}
                                            </span>
                                            <span class="text-gray-500 text-sm">{{ $test->created_at->diffForHumans() }}</span>
                                            <span class="hidden sm:inline text-gray-400 text-xs">{{ $test->created_at->format('M d, Y • h:i A') }}</span>
                                        </div>
                                        <p class="text-gray-900 font-medium">
                                           @if($test->band)
                                                Band Score: {{ $test->band }}
                                            @else
                                                <span class="text-yellow-600">Processing...</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        @if($test->overall_band || $test->score)
                                            <div class="text-right hidden sm:block">
                                                <div class="text-3xl font-bold text-indigo-600">{{ $test->overall_band ?? $test->score }}</div>
                                                <div class="text-xs text-gray-500">Band</div>
                                            </div>
                                        @endif
                                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 sm:px-6 py-2 rounded-lg font-medium text-sm sm:text-base transition-colors group-hover:scale-105 transform duration-200">
                                            View
                                        </button>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if(method_exists($tests, 'hasPages') && $tests->hasPages())
                        <div class="p-6 border-t border-gray-200">
                            {{ $tests->links() }}
                        </div>
                    @endif
                @endif
            </div>

            <!-- Motivational Banner -->
            @if($tests->isNotEmpty())
            <div class="mt-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl sm:text-2xl font-bold mb-2">Keep Going! 🎯</h3>
                        <p class="text-white/90">
                            @php
                                $latestScore = $tests->first()->band
 ?? $tests->first()->score ?? 0;
                            @endphp
                            @if($latestScore >= 7)
                                Excellent progress! Keep practicing to maintain your high scores.
                            @elseif($latestScore >= 6)
                                You're doing great! A few more tests to reach band 7+.
                            @else
                                Every test makes you better. Keep practicing!
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('speaking.test') }}" 
                           class="bg-white hover:bg-gray-100 text-indigo-600 px-6 py-3 rounded-lg font-semibold transition-colors whitespace-nowrap">
                            Practice Now
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>
</x-app-layout>