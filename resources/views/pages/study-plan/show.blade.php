<x-app-layout>
@php
$module  = $scores['module'] ?? 'writing';
$overall = $scores['overall'] ?? 0;
$target  = $plan['target_band'] ?? min(9, $overall + 0.5);
$weeks   = $plan['weeks'] ?? [];
$tips    = $plan['tips'] ?? [];
$focus   = $plan['focus_areas'] ?? [];
$hrs     = $plan['weekly_hours'] ?? 8;

$moduleColor = match($module) {
    'speaking'  => ['text-brand-400', 'bg-brand-500/15', 'border-brand-500/20'],
    'listening' => ['text-amber-400', 'bg-amber-500/15', 'border-amber-500/20'],
    'reading'   => ['text-rose-400',  'bg-rose-500/15',  'border-rose-500/20'],
    default     => ['text-purple-400','bg-purple-500/15','border-purple-500/20'],
};

$weekColors = ['border-brand-500/30 bg-brand-500/5','border-amber-500/30 bg-amber-500/5','border-purple-500/30 bg-purple-500/5','border-rose-500/30 bg-rose-500/5'];
@endphp

<div class="min-h-screen bg-surface-950 py-10 px-4">
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ url()->previous() }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('study-plan.regenerate', $test) }}">
                @csrf
                <button type="submit" class="btn-secondary text-sm px-4 py-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Regenerate Plan
                </button>
            </form>
            <span class="tag-cyan capitalize">{{ $module }} Test</span>
        </div>
    </div>

    @if(session('success'))
    <div class="card p-4 border border-emerald-500/20 bg-emerald-500/5 text-emerald-400 text-sm mb-6">
        {{ session('success') }}
    </div>
    @endif

    {{-- Hero card --}}
    <div class="card p-8 mb-6 text-center bg-gradient-to-br from-surface-800 to-surface-900 border-brand-500/20">
        <div class="inline-flex items-center gap-2 bg-brand-500/10 border border-brand-500/20 rounded-full px-3 py-1 text-brand-400 text-xs font-semibold uppercase tracking-wider mb-4">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            AI Study Plan
        </div>
        <h1 class="text-3xl font-bold text-surface-50 mb-2">Your 4-Week IELTS Plan</h1>
        <p class="text-surface-400 mb-6">Personalised based on your {{ ucfirst($module) }} Band {{ number_format($overall, 1) }} result</p>

        <div class="flex flex-wrap justify-center gap-6">
            <div class="text-center">
                <div class="text-4xl font-bold text-brand-400">{{ number_format($overall, 1) }}</div>
                <div class="text-xs text-surface-500 mt-1">Current Band</div>
            </div>
            <div class="w-px bg-surface-700 hidden sm:block"></div>
            <div class="text-center">
                <div class="text-4xl font-bold text-emerald-400">{{ number_format($target, 1) }}</div>
                <div class="text-xs text-surface-500 mt-1">Target Band</div>
            </div>
            <div class="w-px bg-surface-700 hidden sm:block"></div>
            <div class="text-center">
                <div class="text-4xl font-bold text-amber-400">{{ $hrs }}h</div>
                <div class="text-xs text-surface-500 mt-1">Per Week</div>
            </div>
            <div class="w-px bg-surface-700 hidden sm:block"></div>
            <div class="text-center">
                <div class="text-4xl font-bold text-purple-400">4</div>
                <div class="text-xs text-surface-500 mt-1">Weeks</div>
            </div>
        </div>
    </div>

    {{-- Focus areas --}}
    @if(count($focus))
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-surface-100 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Priority Focus Areas
        </h2>
        <div class="flex flex-wrap gap-3">
            @foreach($focus as $i => $area)
            @php $colors = ['bg-red-500/15 text-red-400 border-red-500/25','bg-amber-500/15 text-amber-400 border-amber-500/25','bg-brand-500/15 text-brand-400 border-brand-500/25']; @endphp
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl border {{ $colors[$i] ?? $colors[2] }} text-sm font-semibold">
                <span class="text-xs font-bold opacity-60">#{{ $i+1 }}</span>
                {{ $area }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Weekly plan --}}
    @if(count($weeks))
    <div class="space-y-4 mb-6">
        <h2 class="text-xl font-bold text-surface-100">Week-by-Week Plan</h2>

        @foreach($weeks as $i => $week)
        @php $wc = $weekColors[$i % 4]; @endphp
        <div class="card border {{ $wc }} overflow-hidden" x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-left">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-surface-700 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-surface-200">W{{ $week['week'] ?? $i+1 }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-surface-100">Week {{ $week['week'] ?? $i+1 }}: {{ $week['theme'] ?? '' }}</p>
                        <p class="text-xs text-surface-500 mt-0.5">{{ count($week['goals'] ?? []) }} goals · {{ count($week['daily_tasks'] ?? []) }} task blocks</p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-surface-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="border-t border-surface-700 p-5 space-y-5">

                {{-- Goals --}}
                @if(!empty($week['goals']))
                <div>
                    <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-2">Goals</p>
                    <ul class="space-y-1.5">
                        @foreach($week['goals'] as $goal)
                        <li class="flex items-start gap-2 text-sm text-surface-300">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $goal }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Daily tasks --}}
                @if(!empty($week['daily_tasks']))
                <div>
                    <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-3">Daily Schedule</p>
                    <div class="space-y-2">
                        @foreach($week['daily_tasks'] as $task)
                        <div class="flex items-start gap-3 bg-surface-900 rounded-xl p-3">
                            <span class="text-xs font-semibold text-brand-400 w-16 shrink-0 pt-0.5">{{ $task['day'] ?? '' }}</span>
                            <span class="text-sm text-surface-300 flex-1">{{ $task['task'] ?? '' }}</span>
                            <span class="text-xs text-surface-500 shrink-0 font-mono">{{ $task['duration'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Resources --}}
                @if(!empty($week['resources']))
                <div>
                    <p class="text-xs text-surface-500 uppercase tracking-wider font-semibold mb-2">Recommended Resources</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($week['resources'] as $res)
                        <span class="px-3 py-1 bg-surface-700 text-surface-300 rounded-lg text-xs border border-surface-600">{{ $res }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card p-8 text-center mb-6">
        <p class="text-surface-400">Plan generation in progress. <a href="{{ request()->url() }}" class="text-brand-400 underline">Refresh the page</a> in a moment.</p>
    </div>
    @endif

    {{-- Tips --}}
    @if(count($tips))
    <div class="card p-6 mb-6 border border-emerald-500/20 bg-emerald-500/5">
        <h2 class="font-semibold text-emerald-400 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Expert Tips
        </h2>
        <ul class="space-y-3">
            @foreach($tips as $tip)
            <li class="flex items-start gap-3 text-sm text-surface-300">
                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">{{ $loop->index + 1 }}</span>
                {{ $tip }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- CTA --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route(str_contains($test->type, 'speaking') ? 'speaking.index' : ($test->type . '.index')) }}" class="btn-primary flex-1 justify-center py-3">
            Practice {{ ucfirst($scores['module']) }} Again
        </a>
        <a href="{{ route('dashboard') }}" class="btn-secondary flex-1 justify-center py-3">Back to Dashboard</a>
    </div>

</div>
</div>
</x-app-layout>
