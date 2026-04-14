<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your AI Score — IELTS Band AI Demo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    .error-grammar     { background: rgba(239,68,68,0.18); border-bottom: 2px solid #ef4444; cursor: pointer; }
    .error-vocabulary  { background: rgba(251,146,60,0.18); border-bottom: 2px solid #f97316; cursor: pointer; }
    .error-cohesion    { background: rgba(34,197,94,0.18);  border-bottom: 2px solid #22c55e; cursor: pointer; }
    .error-punctuation { background: rgba(59,130,246,0.18); border-bottom: 2px solid #3b82f6; cursor: pointer; }
    </style>
</head>
<body class="min-h-screen bg-surface-950 text-surface-200 font-sans antialiased">

@php
    $overall = $scoring['overall_band'] ?? 0;
    $ta      = $scoring['task_achievement'] ?? 0;
    $cc      = $scoring['coherence_cohesion'] ?? 0;
    $lr      = $scoring['lexical_resource'] ?? 0;
    $gr      = $scoring['grammar'] ?? 0;

    $bandColor = $overall >= 7.5 ? 'text-emerald-400' : ($overall >= 6.5 ? 'text-brand-400' : ($overall >= 5.5 ? 'text-amber-400' : 'text-red-400'));
    $ringColor = $overall >= 7.5 ? '#10b981' : ($overall >= 6.5 ? '#06b6d4' : ($overall >= 5.5 ? '#f59e0b' : '#ef4444'));

    $criteria = [
        ['Task Achievement',       $ta, 'TA'],
        ['Coherence & Cohesion',   $cc, 'CC'],
        ['Lexical Resource',       $lr, 'LR'],
        ['Grammar Range',          $gr, 'GR'],
    ];

    $summary   = $scoring['summary'] ?? [];
    $strengths = $scoring['strengths'] ?? [];
    $improvements = $scoring['improvements'] ?? [];
    $examiner_comments = $scoring['examiner_comments'] ?? [];
    $band_explanations = $scoring['band_explanations'] ?? [];
    $topic_vocabulary  = $scoring['topic_vocabulary'] ?? [];
    $errors            = $scoring['errors'] ?? [];
    $error_summary     = $scoring['error_summary'] ?? [];
@endphp

{{-- Demo Banner --}}
<div class="bg-brand-500/10 border-b border-brand-500/30 px-4 py-2.5">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-sm text-brand-300">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span><strong>Demo Result</strong> — Sign up free to save, track, and improve over time.</span>
        </div>
        <a href="{{ route('register') }}" class="shrink-0 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-500 px-3 py-1.5 rounded-lg transition-colors">
            Save This Score →
        </a>
    </div>
</div>

{{-- Header --}}
<header class="sticky top-0 z-40 bg-surface-900/90 backdrop-blur border-b border-surface-700/50">
    <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                </svg>
            </div>
            <span class="font-bold text-surface-50 text-sm">IELTS Band <span class="text-brand-400">AI</span></span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('demo') }}" class="text-sm text-surface-400 hover:text-surface-200 transition-colors">Try Again</a>
            <a href="{{ route('register') }}" class="btn-primary text-sm">Get Started Free</a>
        </div>
    </div>
</header>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    {{-- Overall Score Hero --}}
    <div class="card p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6 sm:gap-8">
        {{-- Ring --}}
        <div class="shrink-0">
            <svg class="w-32 h-32" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="#1e293b" stroke-width="10"/>
                @php $pct = min(($overall / 9) * 314, 314); @endphp
                <circle cx="60" cy="60" r="50" fill="none"
                    stroke="{{ $ringColor }}" stroke-width="10"
                    stroke-dasharray="{{ $pct }} 314"
                    stroke-dashoffset="0"
                    stroke-linecap="round"
                    style="filter: drop-shadow(0 0 8px {{ $ringColor }}88); transform-origin: center; transform: rotate(-90deg)"/>
                <text x="60" y="55" text-anchor="middle" font-size="28" font-weight="700" fill="#f1f5f9" font-family="Figtree">{{ $overall }}</text>
                <text x="60" y="73" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="Figtree">OVERALL</text>
            </svg>
        </div>
        {{-- Details --}}
        <div class="flex-1 text-center sm:text-left">
            <p class="text-surface-400 text-sm uppercase tracking-wider mb-1">Your AI Band Score</p>
            <h1 class="text-4xl font-extrabold {{ $bandColor }} mb-2">Band {{ $overall }}</h1>
            @if(!empty($summary['strength']) || !empty($summary['weakness']))
            <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-3">
                @if(!empty($summary['strength']))
                <span class="tag-green text-xs">+ {{ $summary['strength'] }}</span>
                @endif
                @if(!empty($summary['weakness']))
                <span class="tag-red text-xs">↓ {{ $summary['weakness'] }}</span>
                @endif
            </div>
            @endif
            @if(!empty($scoring['feedback']))
            <p class="text-surface-300 text-sm leading-relaxed">{{ $scoring['feedback'] }}</p>
            @endif
        </div>
        {{-- Quick criteria --}}
        <div class="grid grid-cols-2 gap-3 shrink-0">
            @foreach($criteria as [$label, $score, $abbr])
            <div class="bg-surface-800 border border-surface-600 rounded-xl p-3 text-center min-w-[90px]">
                <div class="text-2xl font-bold {{ $score >= 7 ? 'text-emerald-400' : ($score >= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $score }}</div>
                <div class="text-[10px] text-surface-500 mt-0.5">{{ $abbr }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Criteria Breakdown --}}
    <div class="card p-5">
        <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider mb-5">Criteria Breakdown</h2>
        <div class="space-y-4">
            @foreach([
                ['task_achievement',  'Task Achievement',        $ta, 'TA'],
                ['coherence_cohesion','Coherence & Cohesion',    $cc, 'CC'],
                ['lexical_resource',  'Lexical Resource',        $lr, 'LR'],
                ['grammar',           'Grammar Range & Accuracy',$gr, 'GR'],
            ] as [$key, $label, $score, $abbr])
            @php
                $exp = $band_explanations[$key] ?? [];
                $pct = ($score / 9) * 100;
                $barColor = $score >= 7 ? 'bg-emerald-500' : ($score >= 6 ? 'bg-amber-500' : 'bg-red-500');
            @endphp
            <div x-data="{ open: false }">
                <div class="flex items-center justify-between mb-1.5 cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-surface-200">{{ $label }}</span>
                        @if(!empty($exp))
                        <svg class="w-3.5 h-3.5 text-surface-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        @endif
                    </div>
                    <span class="font-bold {{ $score >= 7 ? 'text-emerald-400' : ($score >= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $score }}</span>
                </div>
                <div class="w-full bg-surface-700 rounded-full h-1.5 mb-1">
                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                </div>
                @if(!empty($exp))
                <div x-show="open" x-transition class="mt-3 bg-surface-900 border border-surface-700 rounded-xl p-4 text-sm space-y-2" style="display:none;">
                    @if(!empty($exp['why']))
                    <p class="text-surface-300"><span class="text-surface-500 text-xs uppercase tracking-wider">Why:</span> {{ $exp['why'] }}</p>
                    @endif
                    @if(!empty($exp['tip']))
                    <p class="text-emerald-400 text-xs">
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        Tip: {{ $exp['tip'] }}
                    </p>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Strengths + Improvements --}}
    @if(!empty($strengths) || !empty($improvements))
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if(!empty($strengths))
        <div class="card p-5">
            <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">What You Did Well</h2>
            <ul class="space-y-2">
                @foreach($strengths as $s)
                <li class="flex items-start gap-2 text-sm text-surface-300">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $s }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @if(!empty($improvements))
        <div class="card p-5">
            <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Areas to Improve</h2>
            <ul class="space-y-2">
                @foreach($improvements as $imp)
                <li class="flex items-start gap-2 text-sm text-surface-300">
                    <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    {{ $imp }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    {{-- Errors on essay --}}
    @if(!empty($errors))
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider">Error Analysis</h2>
            <div class="flex flex-wrap gap-2 text-xs">
                @if(!empty($error_summary['grammar']))   <span class="tag bg-red-500/10 text-red-400 border-red-500/30">{{ $error_summary['grammar'] }} grammar</span> @endif
                @if(!empty($error_summary['vocabulary'])) <span class="tag bg-orange-500/10 text-orange-400 border-orange-500/30">{{ $error_summary['vocabulary'] }} vocab</span> @endif
                @if(!empty($error_summary['cohesion']))  <span class="tag bg-emerald-500/10 text-emerald-400 border-emerald-500/30">{{ $error_summary['cohesion'] }} cohesion</span> @endif
                @if(!empty($error_summary['punctuation'])) <span class="tag bg-blue-500/10 text-blue-400 border-blue-500/30">{{ $error_summary['punctuation'] }} punctuation</span> @endif
            </div>
        </div>
        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
            @foreach(array_slice($errors, 0, 8) as $err)
            <div class="bg-surface-900 border border-surface-700 rounded-xl p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <span class="text-xs font-medium px-2 py-0.5 rounded
                        {{ $err['type'] === 'grammar' ? 'bg-red-500/15 text-red-400' :
                           ($err['type'] === 'vocabulary' ? 'bg-orange-500/15 text-orange-400' :
                           ($err['type'] === 'cohesion' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-blue-500/15 text-blue-400')) }}">
                        {{ ucfirst($err['type']) }}
                    </span>
                    <span class="text-xs text-surface-600 capitalize">{{ $err['severity'] ?? 'medium' }}</span>
                </div>
                <p class="text-sm text-surface-400 mb-1 line-through">{{ $err['text'] }}</p>
                <p class="text-sm text-emerald-400 mb-2">→ {{ $err['correction'] }}</p>
                @if(!empty($err['explanation']))
                <p class="text-xs text-surface-500 leading-relaxed">{{ $err['explanation'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Topic vocabulary --}}
    @if(!empty($topic_vocabulary))
    <div class="card p-5">
        <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Advanced Vocabulary for This Topic</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($topic_vocabulary as $word)
            <span class="bg-surface-800 border border-surface-600 text-surface-300 text-sm px-3 py-1.5 rounded-lg font-medium">{{ $word }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Signup CTA --}}
    <div class="card p-6 sm:p-8 border border-brand-500/20 bg-gradient-to-br from-brand-500/5 to-surface-900 text-center">
        <div class="w-14 h-14 rounded-2xl bg-brand-500/15 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-surface-50 mb-2">Track your progress over time</h2>
        <p class="text-surface-400 text-sm mb-6 max-w-md mx-auto">
            Create a free account to save all your scores, see your band trend per module, and get Speaking + Listening + Reading tests too.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('register') }}" class="btn-primary px-6 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Create Free Account — 3 Tests Included
            </a>
            <a href="{{ route('demo') }}" class="btn-secondary px-6 py-3">Try Another Essay</a>
        </div>
        <p class="text-xs text-surface-600 mt-4">No credit card required · Cancel anytime</p>
    </div>

    {{-- Institute CTA --}}
    <div class="card p-5 border border-indigo-500/20">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-surface-100 text-sm">Running an IELTS institute?</p>
                <p class="text-surface-400 text-xs mt-0.5">Assign AI-scored tests to batches of students. Track progress. Export reports.</p>
            </div>
            <a href="mailto:hello@ieltsbandai.com?subject=Institute%20Enquiry" class="shrink-0 btn-secondary text-sm">Get a Demo →</a>
        </div>
    </div>

</div>

</body>
</html>
