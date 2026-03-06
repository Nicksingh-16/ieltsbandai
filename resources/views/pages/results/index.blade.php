<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50" x-data="resultPageData()">
        @php
            $feedbackText = is_array($feedback ?? null)
                ? implode(' ', array_filter($feedback))
                : ($feedback ?? '');
        @endphp
        <!-- PRINT ONLY HEADER -->
        <div id="print-report" style="display: none;">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-start border-b-4 border-gray-900 pb-8 mb-8">
                    <div class="flex items-center gap-5">
                         <div class="w-20 h-20 bg-gray-900 text-white rounded-xl flex items-center justify-center font-bold text-3xl shadow-lg">AI</div>
                         <div>
                             <h1 class="text-4xl font-extrabold text-gray-900 leading-none tracking-tight">IELTS BAND AI</h1>
                             <p class="text-sm text-gray-500 uppercase tracking-[0.3em] mt-2 font-bold">Official Test Report</p>
                         </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase tracking-widest mb-2 font-semibold">Overall Band Score</p>
                        <div class="inline-block bg-gray-900 text-white px-6 py-2 rounded-lg">
                            <p class="text-5xl font-bold">{{ number_format($scores['overall_band'], 1) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-12 mb-12">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Candidate Name</p>
                        <p class="text-2xl font-serif italic text-gray-900 border-b-2 border-gray-200 pb-2">{{ Auth::user()->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Date of Test</p>
                        <p class="text-2xl font-mono text-gray-900 border-b-2 border-gray-200 pb-2">{{ now()->format('d F Y') }}</p>
                    </div>
                </div>

                 <div class="grid grid-cols-4 gap-4 text-center mb-12">
                    <div class="border-2 border-gray-100 p-4 rounded-xl bg-gray-50 break-inside-avoid">
                        <span class="block text-xs text-gray-500 uppercase mb-2 font-bold">Task Response</span>
                        <span class="block text-3xl font-bold text-gray-900">{{ number_format($scores['task_achievement'], 1) }}</span>
                    </div>
                    <div class="border-2 border-gray-100 p-4 rounded-xl bg-gray-50 break-inside-avoid">
                        <span class="block text-xs text-gray-500 uppercase mb-2 font-bold">Coherence</span>
                        <span class="block text-3xl font-bold text-gray-900">{{ number_format($scores['coherence_cohesion'], 1) }}</span>
                    </div>
                    <div class="border-2 border-gray-100 p-4 rounded-xl bg-gray-50 break-inside-avoid">
                        <span class="block text-xs text-gray-500 uppercase mb-2 font-bold">Lexical</span>
                        <span class="block text-3xl font-bold text-gray-900">{{ number_format($scores['lexical_resource'], 1) }}</span>
                    </div>
                    <div class="border-2 border-gray-100 p-4 rounded-xl bg-gray-50 break-inside-avoid">
                        <span class="block text-xs text-gray-500 uppercase mb-2 font-bold">Grammar</span>
                        <span class="block text-3xl font-bold text-gray-900">{{ number_format($scores['grammar'], 1) }}</span>
                    </div>
                </div>
                
                <div class="border-t-2 border-gray-100 pt-8 mb-8">
                     <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide mb-4">Examiner Feedback Summary</h3>
                     <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed text-justify">
                         {{ $feedbackText ?? 'No specific feedback available.' }}
                     </div>
                </div>

                <!-- Strengths & Improvements -->
                <div class="grid grid-cols-2 gap-8 mb-8 break-inside-avoid">
                    @if(isset($strengths) && count($strengths) > 0)
                        <div class="border border-green-200 rounded-xl p-6 bg-green-50">
                            <h3 class="font-bold text-green-900 mb-4 text-lg">Strengths</h3>
                            <ul class="space-y-2">
                                @foreach($strengths as $strength)
                                    <li class="flex items-start gap-2 text-sm text-gray-800">
                                        <span class="text-green-600 font-bold">✓</span>
                                        <span>{{ $strength }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($improvements) && count($improvements) > 0)
                        <div class="border border-blue-200 rounded-xl p-6 bg-blue-50">
                            <h3 class="font-bold text-blue-900 mb-4 text-lg">Areas for Improvement</h3>
                             <ul class="space-y-2">
                                @foreach($improvements as $improvement)
                                    <li class="flex items-start gap-2 text-sm text-gray-800">
                                        <span class="text-blue-600 font-bold">→</span>
                                         <span>{{ $improvement }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Essay Text -->
                <div class="mb-12 break-inside-avoid">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-gray-200 pb-2">Your Essay</h3>
                    
                    <!-- Print Legend -->
                    <div class="flex flex-wrap gap-4 mb-4 text-xs border border-gray-200 rounded-lg p-3 bg-gray-50">
                        <span class="font-bold text-gray-700 mr-2">LEGEND:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-red-200 border border-red-500"></span>
                            <span>Grammar</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-orange-200 border border-orange-500"></span>
                            <span>Vocabulary</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-yellow-200 border border-yellow-500"></span>
                            <span>Cohesion</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-blue-200 border border-blue-500"></span>
                            <span>Punctuation</span>
                        </div>
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-800 text-justify leading-relaxed p-6 bg-gray-50 rounded-xl border border-gray-100">
                        {!! $highlightedEssay !!}
                    </div>
                </div>

                <!-- Detailed Errors Analysis -->
                @if(isset($errors) && count($errors) > 0)
                    <div class="break-before-page">
                        <h3 class="text-xl font-bold text-gray-900 mb-6 border-b-2 border-gray-200 pb-2">Detailed Error Analysis</h3>
                        <div class="space-y-4">
                            @foreach($errors as $index => $error)
                                <div class="break-inside-avoid border border-gray-200 rounded-lg p-4 bg-white">
                                    <div class="flex items-start gap-4">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center flex-shrink-0">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wide bg-gray-100 text-gray-700">{{ $error['category'] ?? $error['type'] ?? 'Issue' }}</span>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4 mb-2">
                                                <div>
                                                    <span class="text-xs text-red-500 font-semibold uppercase">Original</span>
                                                    <p class="text-red-700 bg-red-50 p-2 rounded mt-1 text-sm">{{ $error['original_text'] ?? $error['text'] ?? 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-green-600 font-semibold uppercase">Correction</span>
                                                    <p class="text-green-700 bg-green-50 p-2 rounded mt-1 text-sm">{{ $error['correction'] ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            
                                            @if(!empty($error['explanation']))
                                                <p class="text-sm text-gray-600 italic mt-2">
                                                    <span class="font-semibold not-italic text-indigo-900">Explanation:</span> 
                                                    {{ $error['explanation'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="absolute bottom-12 left-12 right-12 text-center text-xs text-gray-400 border-t border-gray-100 pt-4 flex justify-between">
                    <span>Test ID: {{ $test->id ?? 'N/A' }}</span>
                    <span>Verified by IELTS Band AI</span>
                    <span>{{ url('/') }}</span>
                </div>
            </div>
        </div>

        
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 print:hidden">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 print:hidden">
                <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="font-medium">Back to Dashboard</span>
                </a>
                <div class="flex items-center gap-3">
                    <button @click="openShareModal = true" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 transition-colors bg-indigo-50 px-4 py-2 rounded-lg font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span>Share Report</span>
                    </button>
                    <button onclick="window.print()" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span class="font-medium hidden sm:inline">Print Report</span>
                    </button>
                </div>
            </div>

            @php
                $errors = $errors ?? [];
                $unpositioned_errors = $unpositioned_errors ?? [];
                $band_explanations = $band_explanations ?? [];
                $summary = $summary ?? null;
            @endphp

            @php
                $summaryData = $summary ?? [
                    'estimated_band' => $scores['overall_band'] ?? 0,
                    'strength' => $strengths[0] ?? 'Ideas are generally clear.',
                    'weakness' => $improvements[0] ?? 'Accuracy needs improvement.',
                    'tip' => $improvements[1] ?? 'Focus on precise grammar in your next draft.',
                ];
            @endphp

            <!-- Summary strip -->
            <div class="bg-white border border-indigo-100 rounded-2xl p-5 mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-indigo-500 font-semibold">Snapshot</p>
                    <div class="text-3xl font-bold text-gray-900">Estimated Band {{ number_format($summaryData['estimated_band'] ?? ($scores['overall_band'] ?? 0), 1) }}</div>
                </div>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-gray-700">
                    <div class="bg-green-50 border border-green-100 rounded-xl p-3">
                        <p class="text-xs font-semibold text-green-700 mb-1">Strength</p>
                        <p class="font-medium">{{ $summaryData['strength'] ?? 'Clear ideas and relevance.' }}</p>
                    </div>
                    <div class="bg-red-50 border border-red-100 rounded-xl p-3">
                        <p class="text-xs font-semibold text-red-700 mb-1">Weakness</p>
                        <p class="font-medium">{{ $summaryData['weakness'] ?? 'Accuracy issues lower clarity.' }}</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Fast Tip</p>
                        <p class="font-medium">{{ $summaryData['tip'] ?? 'Tighten grammar and link ideas clearly.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Celebration Animation -->
            <div class="text-center mb-8 animate-fade-in">
                @if($scores['overall_band'] >= 7)
                    <div class="inline-block text-6xl mb-4 animate-bounce">🎉</div>
                @elseif($scores['overall_band'] >= 6)
                    <div class="inline-block text-6xl mb-4 animate-bounce">👏</div>
                @else
                    <div class="inline-block text-6xl mb-4">📚</div>
                @endif
            </div>

            <!-- Overall Band Score Card -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 sm:p-12 text-white text-center mb-8 shadow-2xl relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>
                
                <div class="relative z-10">
                    <p class="text-lg sm:text-xl mb-2 opacity-90">Your Overall Band Score</p>
                    <div class="text-7xl sm:text-8xl font-bold mb-4 animate-scale-in">
                        {{ number_format($scores['overall_band'], 1) }}
                    </div>
                    <p class="text-base sm:text-lg opacity-90 mb-6">
                        @if($scores['overall_band'] >= 8)
                            Outstanding! You're at expert level! 🌟
                        @elseif($scores['overall_band'] >= 7)
                            Excellent work! You're doing great! 🎯
                        @elseif($scores['overall_band'] >= 6)
                            Good job! Keep practicing to improve! 💪
                        @else
                            Great start! Practice makes perfect! 📈
                        @endif
                    </p>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        <span class="font-semibold">Writing Test • {{ $task_info['title'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Scores -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                    <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    IELTS Band Scores
                </h2>
                
                @php
                    $criteriaInfo = [
                        'task_achievement' => [
                            'label' => 'Task Achievement',
                            'icon' => '🎯',
                            'color' => 'blue',
                            'description' => 'How well you addressed the task'
                        ],
                        'coherence_cohesion' => [
                            'label' => 'Coherence & Cohesion',
                            'icon' => '🔗',
                            'color' => 'green',
                            'description' => 'Organization and flow of ideas'
                        ],
                        'lexical_resource' => [
                            'label' => 'Lexical Resource',
                            'icon' => '📚',
                            'color' => 'purple',
                            'description' => 'Vocabulary range and accuracy'
                        ],
                        'grammar' => [
                            'label' => 'Grammatical Range & Accuracy',
                            'icon' => '✍️',
                            'color' => 'pink',
                            'description' => 'Grammar variety and correctness'
                        ],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($criteriaInfo as $key => $info)
                        @if(isset($scores[$key]))
                            <div class="bg-gradient-to-br from-gray-50 to-white border-2 border-gray-100 rounded-xl p-6 hover:shadow-lg transition-all group">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="text-3xl">{{ $info['icon'] }}</div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 group-hover:text-{{ $info['color'] }}-600 transition-colors">{{ $info['label'] }}</h3>
                                            <p class="text-xs text-gray-500 mt-1">{{ $info['description'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-4xl font-bold text-{{ $info['color'] }}-600">
                                        {{ number_format($scores[$key], 1) }}
                                    </div>
                                </div>
                               @php
    $score = $scores[$key];
    $percentage = min(100, ($score / 9) * 100);
    $barClass = $score >= 7 ? 'band-green' : ($score >= 6 ? 'band-orange' : 'band-red');
    $labelClass = $score >= 7 ? 'text-green-600' : ($score >= 6 ? 'text-orange-600' : 'text-red-600');
    $labelText = $score >= 7 ? 'Ready for test centre' : ($score >= 6 ? 'Almost there' : 'Needs stronger control');
    $why = data_get($band_explanations ?? [], $key.'.why') ?? 'Examiner rationale pending.';
    $tip = data_get($band_explanations ?? [], $key.'.tip') ?? 'Tighten accuracy and clarity to lift this band.';
@endphp

<div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
    <div
        class="h-3 rounded-full band-bar {{ $barClass }}"
        data-width="{{ $percentage }}">
    </div>
</div>

<div class="flex justify-between mt-2 text-xs text-gray-500">
    <span>0</span>
    <span class="font-semibold {{ $labelClass }}">
        {{ $labelText }}
    </span>
    <span>9</span>
</div>

<div class="mt-3 bg-gray-50 border border-gray-100 rounded-lg p-3 text-sm">
    <p class="font-semibold text-gray-800 mb-1">Why this band</p>
    <p class="text-gray-700 leading-snug">{{ $why }}</p>
    <p class="text-xs text-indigo-600 font-semibold mt-1">Examiner tip: {{ $tip }}</p>
</div>

                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- AI Feedback -->
            @if($feedbackText)
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-2xl p-6 sm:p-8 mb-8">
                    <div class="flex items-start gap-4">
                        <div class="bg-amber-100 p-3 rounded-xl flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
                                AI-Powered Feedback
                                <span class="text-xs bg-amber-200 text-amber-800 px-2 py-1 rounded-full font-semibold">PERSONALIZED</span>
                            </h3>
                            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                                {{ $feedbackText }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Strengths & Improvements -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Strengths -->
                @if(isset($strengths) && count($strengths) > 0)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">What You Did Well ✨</h3>
                        </div>
                        <ul class="space-y-2">
                            @foreach($strengths as $strength)
                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                    <span class="text-green-500 mt-0.5">✓</span>
                                    <span>{{ $strength }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Improvements -->
                @if(isset($improvements) && count($improvements) > 0)
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Areas to Improve 📈</h3>
                        </div>
                        <ul class="space-y-2">
                            @foreach($improvements as $improvement)
                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                    <span class="text-blue-500 mt-0.5">→</span>
                                    <span>{{ $improvement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Your Essay with Error Highlighting -->
            <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <svg class="w-7 h-7 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        Your Essay with Corrections
                    </h2>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-semibold">{{ $word_count }}</span> words
                    </div>
                </div>

                <!-- Error Legend -->
                @if(isset($errors) && count($errors) > 0)
                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <p class="text-sm font-semibold text-gray-700 mb-3">Error Types:</p>
                        <div class="flex flex-wrap gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded bg-red-200 border-2 border-red-500"></span>
                                <span class="text-xs text-gray-600">Grammar (Critical)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded bg-orange-200 border-2 border-orange-500"></span>
                                <span class="text-xs text-gray-600">Vocabulary (Lexical)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded bg-yellow-200 border-2 border-yellow-500"></span>
                                <span class="text-xs text-gray-600">Cohesion (Flow)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded bg-blue-200 border-2 border-blue-500"></span>
                                <span class="text-xs text-gray-600">Punctuation (Technical)</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">💡 Click on any highlighted text to see the correction and explanation</p>
                    </div>
                @endif

                <!-- Essay Text with Highlighting and Side Bubbles -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Essay Content (Left Side) -->
                    <div class="lg:col-span-8">
                        <div class="bg-gray-50 rounded-xl p-6 border-2 border-gray-200">
                            <div id="essayContent" class="prose prose-lg max-w-none text-gray-800" style="line-height: 1.6;">
                                {!! $highlightedEssay !!}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Unplaced Errors Side Bubbles (Right Side) -->
                    @if(isset($unpositioned_errors) && count($unpositioned_errors) > 0)
                        <div class="lg:col-span-4">
                            <div class="sticky top-4 space-y-3">
                                <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <h4 class="text-sm font-bold text-amber-900">Additional Issues</h4>
                                    </div>
                                    <p class="text-xs text-amber-700 mb-3">These errors couldn't be highlighted but are important to review:</p>
                                </div>
                                
                                @foreach($unpositioned_errors as $index => $error)
                                    <div class="bg-white rounded-xl border-2 border-amber-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                                        <div class="flex items-start gap-2 mb-2">
                                            <span class="flex-shrink-0 w-6 h-6 bg-amber-500 text-white rounded-full flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                                            <div class="flex-1">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $error['category'] === 'grammar' ? 'red' : ($error['category'] === 'vocabulary' ? 'orange' : ($error['category'] === 'punctuation' ? 'blue' : 'yellow')) }}-100 text-{{ $error['category'] === 'grammar' ? 'red' : ($error['category'] === 'vocabulary' ? 'orange' : ($error['category'] === 'punctuation' ? 'blue' : 'yellow')) }}-700">
                                                    {{ ucfirst($error['category'] ?? $error['type'] ?? 'issue') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-8">
                                            <p class="text-xs font-semibold text-gray-700 mb-1">Original:</p>
                                            <p class="text-sm text-gray-800 mb-2">"{{ $error['original_text'] ?? $error['text'] ?? '' }}"</p>
                                            @if(!empty($error['correction']))
                                                <p class="text-xs font-semibold text-green-700 mb-1">Suggested:</p>
                                                <p class="text-sm text-green-800 mb-2">"{{ $error['correction'] }}"</p>
                                            @endif
                                            @if(!empty($error['explanation']))
                                                <p class="text-xs text-gray-600 mt-2 italic">{{ $error['explanation'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- No unplaced errors - essay takes full width -->
                        <div class="lg:col-span-4 hidden lg:block">
                            <div class="sticky top-4 bg-green-50 border-2 border-green-200 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <h4 class="text-sm font-bold text-green-900">All Errors Highlighted</h4>
                                </div>
                                <p class="text-xs text-green-700">All detected errors have been successfully highlighted in your essay.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Error Popup Modal -->
                <div 
                    x-show="selectedError"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.away="selectedError = null"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                    style="display: none;"
                >
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 transform" @click.stop>
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center"
                                    :class="{
                                        'bg-red-100': selectedError && ['grammar','tense','article','preposition'].includes(selectedError.category ?? selectedError.type),
                                        'bg-orange-100': selectedError && ['vocabulary','word_choice'].includes(selectedError.category ?? selectedError.type),
                                        'bg-yellow-100': selectedError && ['cohesion','clarity'].includes(selectedError.category ?? selectedError.type)
                                    }"
                                >
                                    <span class="text-xl">
                                        <template x-if="selectedError && (selectedError.category ?? selectedError.type) === 'grammar'">❌</template>
                                        <template x-if="selectedError && (selectedError.category ?? selectedError.type) === 'vocabulary'">📚</template>
                                        <template x-if="selectedError && (selectedError.category ?? selectedError.type) === 'cohesion'">🔗</template>
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 capitalize" x-text="selectedError ? (selectedError.category ?? selectedError.type) : ''"></h3>
                                    <p class="text-xs text-gray-500">Error detected</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700" x-text="selectedError ? ('Severity: ' + (selectedError.severity ?? '')) : ''"></span>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700" x-text="selectedError ? ('Type: ' + (selectedError.type ?? '')) : ''"></span>
                            </div>
                            <button @click="selectedError = null" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Original Text -->
                        <div class="mb-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg p-4">
                            <p class="text-xs font-semibold text-red-800 mb-1">Original:</p>
                            <p class="text-gray-800 font-medium" x-text="selectedError ? selectedError.text : ''"></p>
                        </div>

                        <!-- Correction -->
                        <div class="mb-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg p-4">
                            <p class="text-xs font-semibold text-green-800 mb-1">Correction:</p>
                            <p class="text-gray-800 font-medium" x-text="selectedError ? selectedError.correction : ''"></p>
                        </div>

                        <!-- Explanation -->
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg p-4">
                            <p class="text-xs font-semibold text-blue-800 mb-1">Explanation:</p>
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="selectedError ? selectedError.explanation : ''"></p>
                        </div>

                        <!-- Close Button -->
                        <button 
                            @click="selectedError = null"
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg"
                        >
                            Got it! 👍
                        </button>
                    </div>
                </div>
            </div>

            <!-- Band 9 Maximize Section -->
            @if(!empty($band_9_rewrite) || !empty($examiner_comments) || !empty($topic_vocabulary))
                <div class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 border-2 border-purple-200 rounded-2xl p-6 sm:p-8 mb-8" x-data="{ showRewrite: false, showVocab: false }">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                Maximize to Band 9
                                <span class="text-xs bg-purple-200 text-purple-800 px-3 py-1 rounded-full font-semibold">PREMIUM</span>
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Learn from examiner-calibrated model answers and expert insights</p>
                        </div>
                    </div>

                    <!-- Examiner Comments -->
                    @if(!empty($examiner_comments))
                        <div class="bg-white rounded-xl border-2 border-indigo-100 p-6 mb-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Senior Examiner Insights</h3>
                            </div>
                            <div class="space-y-3">
                                @foreach($examiner_comments as $comment)
                                    <div class="flex items-start gap-3 bg-indigo-50 rounded-lg p-4">
                                        <span class="text-indigo-600 mt-0.5">💡</span>
                                        <p class="text-gray-700 leading-relaxed">{{ $comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Topic Vocabulary -->
                    @if(!empty($topic_vocabulary))
                        <div class="bg-white rounded-xl border-2 border-purple-100 p-6 mb-6 shadow-sm">
                            <button @click="showVocab = !showVocab" class="w-full flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <h3 class="text-lg font-bold text-gray-900">Topic-Specific Vocabulary</h3>
                                        <p class="text-xs text-gray-500">{{ count($topic_vocabulary) }} advanced words for this topic</p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': showVocab}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="showVocab" x-collapse class="mt-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($topic_vocabulary as $word)
                                        <span class="px-4 py-2 bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-800 rounded-full text-sm font-semibold border border-purple-200 hover:shadow-md transition-shadow">
                                            {{ $word }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Band 9 Model Answer -->
                    @if(!empty($band_9_rewrite))
                        <div class="bg-white rounded-xl border-2 border-purple-100 p-6 shadow-sm">
                            <button @click="showRewrite = !showRewrite" class="w-full flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">9</span>
                                    </div>
                                    <div class="text-left">
                                        <h3 class="text-lg font-bold text-gray-900">Band 9 Model Answer</h3>
                                        <p class="text-xs text-gray-500">See how an examiner would write this response</p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': showRewrite}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="showRewrite" x-collapse class="mt-6">
                                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border-2 border-purple-200">
                                    <div class="prose prose-lg max-w-none text-gray-800 leading-relaxed whitespace-pre-wrap">
                                        {{ $band_9_rewrite }}
                                    </div>
                                </div>
                                <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-amber-900 mb-1">Study Tip</p>
                                            <p class="text-sm text-amber-800">Compare this model answer with your response. Notice the sentence structures, vocabulary choices, and how ideas are developed with specific examples.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Share & Download Modal (Hidden by default) -->
            <div x-show="openShareModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 style="display: none;"
            >
                <!-- Modal Content -->
                <div @click.away="openShareModal = false" 
                     class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto transform transition-all"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                >
                    <div class="sticky top-0 z-10 flex items-center justify-between p-6 bg-white border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                             🚀 Share Your Success
                        </h2>
                        <button @click="openShareModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>


                    <div class="p-6 sm:p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                            
                             <!-- The Card Preview (Center Stage) -->
                            <div class="flex justify-center bg-gray-50 rounded-xl border border-gray-200 p-4">
                                <div id="ielts-result-card" class="relative w-full max-w-[500px] aspect-[4/5] bg-[#0f172a] text-white p-6 sm:p-8 rounded-none shadow-2xl flex flex-col justify-between overflow-hidden">
                                     <!-- Background pattern -->
                                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fbbf24 1px, transparent 1px); background-size: 20px 20px;"></div>
                                    
                                    <!-- Header -->
                                    <div class="relative z-10 flex justify-between items-start border-b border-gray-700 pb-5">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded flex items-center justify-center font-bold text-[#0f172a]">AI</div>
                                                <span class="text-xl font-bold tracking-wider">IELTS BAND AI</span>
                                            </div>
                                            <p class="text-xs text-gray-400 uppercase tracking-[0.2em]">Official Test Report</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-3xl font-bold text-yellow-500">{{ number_format($scores['overall_band'], 1) }}</p>
                                            <p class="text-[10px] text-gray-400 uppercase">Overall Band</p>
                                        </div>
                                    </div>

                                    <!-- User Info -->
                                    <div class="relative z-10 py-4 flex-1 flex flex-col justify-center">
                                        <div class="flex justify-between items-end mb-6">
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase mb-1">Candidate Name</p>
                                                <p class="text-lg font-serif italic">{{ Auth::user()->name }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-500 uppercase mb-1">Date</p>
                                                <p class="text-sm font-mono">{{ now()->format('d M Y') }}</p>
                                            </div>
                                        </div>

                                        <!-- Scores Grid -->
                                        <div class="grid grid-cols-2 gap-3 mb-6">
                                            <div class="bg-gray-800/50 p-3 rounded border-l-2 border-yellow-500">
                                                <p class="text-[10px] text-gray-400 uppercase">Task Response</p>
                                                <p class="text-lg font-bold">{{ number_format($scores['task_achievement'] ?? 0, 1) }}</p>
                                            </div>
                                            <div class="bg-gray-800/50 p-3 rounded border-l-2 border-green-500">
                                                <p class="text-[10px] text-gray-400 uppercase">Coherence</p>
                                                <p class="text-lg font-bold">{{ number_format($scores['coherence_cohesion'] ?? 0, 1) }}</p>
                                            </div>
                                            <div class="bg-gray-800/50 p-3 rounded border-l-2 border-blue-500">
                                                <p class="text-[10px] text-gray-400 uppercase">Lexical</p>
                                                <p class="text-lg font-bold">{{ number_format($scores['lexical_resource'] ?? 0, 1) }}</p>
                                            </div>
                                            <div class="bg-gray-800/50 p-3 rounded border-l-2 border-pink-500">
                                                <p class="text-[10px] text-gray-400 uppercase">Grammar</p>
                                                <p class="text-lg font-bold">{{ number_format($scores['grammar'] ?? 0, 1) }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-3 rounded border border-gray-700">
                                            <p class="text-xs text-gray-400 mb-1">Examiner Insight</p>
                                            <p class="text-xs italic text-gray-300 leading-relaxed">"{{ \Illuminate\Support\Str::limit($feedbackText ?? 'Evaluation complete.', 90) }}"</p>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="relative z-10 border-t border-gray-700 pt-4 flex justify-between items-center">
                                        <div>
                                            <p class="text-[10px] text-gray-500">ID: {{ $test->id ?? 'GEN' }}-{{ strtoupper(\Illuminate\Support\Str::random(6)) }}</p>
                                            <p class="text-[10px] text-gray-500">ieltsband.ai</p>
                                        </div>
                                        <div class="w-10 h-10 bg-white p-1">
                                             <!-- Fake QR -->
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url()->current() }}" alt="QR" class="w-full h-full opacity-90">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Controls Side -->
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Choose Action</h3>
                                
                                <button 
                                    @click="downloadImage()"
                                    :disabled="generating"
                                    class="w-full flex items-center justify-center gap-3 bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition-all shadow-lg mb-8 group"
                                >
                                    <svg x-show="!generating" class="w-6 h-6 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <svg x-show="generating" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="generating ? 'Generating Image...' : 'Download Official Card'"></span>
                                </button>
                                
                                <div class="space-y-4">
                                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Or Share Directly</p>
                                    
                                     @php
                                        $shareText = "I just scored Band " . number_format($scores['overall_band'], 1) . " on IELTS Band AI! 🚀 Check out my result:";
                                        $shareUrl = url()->current();
                                    @endphp
                                    
                                    <a href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank" class="flex items-center gap-4 p-4 rounded-xl bg-green-50 border border-green-100 hover:bg-green-100 transition-colors group">
                                        <div class="w-10 h-10 bg-[#25D366] text-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </div>
                                        <div>
                                            <span class="block font-bold text-gray-900">Share via WhatsApp</span>
                                            <span class="text-xs text-gray-500">Send to friends & family</span>
                                        </div>
                                    </a>

                                    <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" class="flex items-center gap-4 p-4 rounded-xl bg-sky-50 border border-sky-100 hover:bg-sky-100 transition-colors group">
                                         <div class="w-10 h-10 bg-[#0088cc] text-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 11.944 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                        </div>
                                        <div>
                                            <span class="block font-bold text-gray-900">Share via Telegram</span>
                                            <span class="text-xs text-gray-500">Post to channels or chats</span>
                                        </div>
                                    </a>

                                    <button onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied!')" class="w-full flex items-center gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200 hover:bg-gray-100 transition-colors group text-left">
                                         <div class="w-10 h-10 bg-gray-700 text-white rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="block font-bold text-gray-900">Copy Link</span>
                                            <span class="text-xs text-gray-500">Share manually anywhere</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 print:hidden">

                <a href="{{ route('writing.index') }}" 
                   class="flex items-center justify-center gap-2 bg-white border-2 border-indigo-600 text-indigo-600 py-4 rounded-xl text-lg font-bold hover:bg-indigo-50 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Take Another Test
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl text-lg font-bold hover:from-indigo-700 hover:to-purple-700 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>

        </div>
    </div>






    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }

        @keyframes scale-in {
            from {
                transform: scale(0.5);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .animate-scale-in {
            animation: scale-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Band bar colours */
        .band-bar {
            box-shadow: inset 0 0 0 rgba(0,0,0,0);
        }
        .band-green {
            background-color: #22c55e;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.7);
        }
        .band-orange {
            background-color: #f97316;
            box-shadow: 0 0 12px rgba(249, 115, 22, 0.55);
        }
        .band-red {
            background-color: #ef4444;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.55);
        }

     /* Error highlighting styles - UPDATE THIS SECTION */
    .error {
        cursor: pointer;
        border-bottom: 2px solid;
        padding: 2px 0;
        transition: all 0.2s;
        border-radius: 2px;
    }
    
    .error:hover {
        transform: translateY(-1px);
        opacity: 0.85;
    }
    
    /* Grammar errors - RED */
    .error-grammar {
        background-color: #fee2e2;
        border-color: #ef4444;
    }
    
    /* Vocabulary errors - ORANGE */
    .error-vocabulary {
        background-color: #ffedd5;
        border-color: #f97316;
    }
    
    /* Cohesion errors - YELLOW */
    .error-cohesion {
        background-color: #fef3c7;
        border-color: #f59e0b;
    }
    
    /* Punctuation errors - BLUE */
    .error-punctuation {
        background-color: #dbeafe;
        border-color: #3b82f6;
    }

    .error-severity-high {
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
    }

    .error-severity-medium {
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
    }

    .error-severity-low {
        box-shadow: 0 0 0 2px rgba(250, 204, 21, 0.2);
    }

    /* ========== PRINT STYLES ========== */
    @media print {
        /* Show ONLY the print report */
        #print-report {
            display: block !important;
            visibility: visible !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            min-height: 100vh !important;
            background: white !important;
            padding: 3rem !important;
            z-index: 9999 !important;
        }
        
        /* Hide web content explicitly */
        .print\:hidden {
            display: none !important;
        }
        
        /* Reset body and html for clean print */
        body, html {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            overflow: visible !important;
        }
        
        /* Ensure colors and backgrounds print correctly */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        /* Ensure proper page breaks */
        .break-inside-avoid {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .break-before-page {
            break-before: page !important;
            page-break-before: always !important;
        }

        /* Fixed Footer for every page */
        #print-footer {
            display: none !important;
        }
    }

    @media print {
        #print-footer {
            display: flex !important;
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            background: white !important;
            border-top: 1px solid #e5e7eb !important;
            padding: 1rem 3rem !important; /* Match report padding */
            justify-content: space-between !important;
            font-size: 0.75rem !important;
            color: #9ca3af !important;
            z-index: 10000 !important;
        }
        
        /* Add margin to bottom of body to prevent content overlap */
        body {
            margin-bottom: 2cm !important;
        }

        #print-report {
            padding-bottom: 3rem !important; /* Space for footer */
        }
    }
    </style>
    
    <!-- Fixed Print Footer HTML -->
    <div id="print-footer">
        <span>Test ID: {{ $test->id ?? 'N/A' }}</span>
        <span>Verified by IELTS Band AI • {{ now()->format('d M Y') }}</span>
        <span>{{ url('/') }}</span>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        function resultPageData() {
            return {
                openShareModal: false,
                generating: false,
                selectedError: null,
                errors: @json($errors ?? []),

                init() {
                    // Initialize highlighter
                    this.$nextTick(() => {
                        document.querySelectorAll('[data-error-id]').forEach(span => {
                            span.addEventListener('click', (e) => {
                                const errorId = e.target.getAttribute('data-error-id');
                                const error = this.errors.find(err => err.id === errorId);
                                if (error) {
                                    this.selectedError = error;
                                }
                            });
                        });
                    });
                },

                downloadImage() {
                    this.generating = true;
                    // Wait for modal to be fully visible and rendered
                    this.$nextTick(() => {
                        const card = document.getElementById('ielts-result-card');
                        
                        html2canvas(card, {
                            scale: 3, 
                            useCORS: true,
                            backgroundColor: '#0f172a',
                            logging: false
                        }).then(canvas => {
                            const link = document.createElement('a');
                            link.download = 'IELTS-Band-AI-Result.png';
                            link.href = canvas.toDataURL('image/png');
                            link.click();
                            this.generating = false;
                        }).catch(err => {
                            console.error(err);
                            alert('Failed to generate image. Please try again.');
                            this.generating = false;
                        });
                    });
                }
            }
        }
    </script>




    <script>
document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.band-bar');

    bars.forEach(bar => {
        const targetWidth = bar.dataset.width;

        bar.style.width = '0%';
        bar.style.transition = 'width 1.2s cubic-bezier(0.4, 0, 0.2, 1)';

        // Small delay for smooth animation
        setTimeout(() => {
            bar.style.width = targetWidth + '%';
        }, 200);
    });
});
</script>

</x-app-layout>