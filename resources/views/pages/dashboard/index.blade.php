<x-app-layout>
<div class="min-h-screen bg-surface-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ── Welcome + Hero Row ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 animate-fade-in">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-surface-50">
                    Hello, {{ auth()->user()->name }}
                </h1>
                <p class="text-surface-400 mt-1 text-sm">Here's where you stand. Let's get better today.</p>
            </div>
            @if(!auth()->user()->hasActiveSubscription())
            <a href="{{ route('pricing') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-glow transition-all shrink-0">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Upgrade to Pro
            </a>
            @endif
        </div>

        {{-- ── Pending Assignments Banner (institute students only) ── --}}
        @if($pendingAssignments->isNotEmpty())
        <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-xl px-5 py-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="text-indigo-300 font-semibold text-sm mb-0.5">
                    {{ $pendingAssignments->count() }} pending {{ Str::plural('test', $pendingAssignments->count()) }} assigned to you
                </div>
                <div class="text-surface-400 text-xs">
                    {{ $pendingAssignments->map(fn($r) => $r->assignment->title)->take(3)->implode(', ') }}
                    @if($pendingAssignments->count() > 3) and {{ $pendingAssignments->count() - 3 }} more @endif
                </div>
            </div>
            <a href="{{ route('institute.my-tests') }}" class="btn-primary text-sm shrink-0">View My Tests →</a>
        </div>
        @endif

        {{-- ── Latest Band Score + Stats ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            {{-- Band Score card --}}
            @if($tests->isNotEmpty() && $tests->first()->band)
            @php
                $band = $tests->first()->band;
                $prev = $tests->count() > 1 ? ($tests->skip(1)->first()->overall_band ?? null) : null;
                $diff = $prev ? $band - $prev : null;
            @endphp
            <div class="col-span-2 lg:col-span-2 card p-6 border-glow flex items-center gap-6">
                <div class="relative shrink-0">
                    <svg class="w-28 h-28" viewBox="0 0 120 120" style="overflow:visible;">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#1e293b" stroke-width="10"/>
                        @php $pct = min(($band / 9) * 314, 314); @endphp
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#06b6d4" stroke-width="10"
                            stroke-dasharray="{{ $pct }} 314"
                            stroke-dashoffset="0"
                            stroke-linecap="round"
                            class="progress-ring"
                            style="filter: drop-shadow(0 0 6px rgba(6,182,212,0.6));"/>
                        <text x="60" y="55" text-anchor="middle" font-size="26" font-weight="700" fill="#f1f5f9" font-family="Figtree">{{ $band }}</text>
                        <text x="60" y="73" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="Figtree">BAND SCORE</text>
                    </svg>
                </div>
                <div>
                    <p class="text-surface-400 text-xs uppercase tracking-wider mb-1">Latest Overall</p>
                    <p class="text-4xl font-bold text-surface-50">{{ $band }}</p>
                    @if($diff !== null)
                        <div class="flex items-center gap-1 mt-2">
                            @if($diff > 0)
                                <span class="tag-green">+{{ number_format($diff, 1) }} from last</span>
                            @elseif($diff < 0)
                                <span class="tag-red">{{ number_format($diff, 1) }} from last</span>
                            @else
                                <span class="tag bg-surface-700 text-surface-400 border-surface-600">No change</span>
                            @endif
                        </div>
                    @else
                        <p class="text-surface-500 text-xs mt-2">First test completed</p>
                    @endif
                </div>
            </div>
            @else
            <div class="col-span-2 lg:col-span-2 card p-6 flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-surface-700 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-surface-300 font-semibold">No score yet</p>
                    <p class="text-surface-500 text-sm mt-1">Complete a test to see your band score here.</p>
                </div>
            </div>
            @endif

            {{-- Stat: Total Tests --}}
            <div class="card p-5 flex items-center gap-4 card-hover">
                <div class="w-11 h-11 rounded-xl bg-brand-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-surface-50">{{ $tests->count() }}</p>
                    <p class="text-xs text-surface-400">Tests Taken</p>
                </div>
            </div>

            {{-- Stat: Credits --}}
            <div class="card p-5 flex items-center gap-4 card-hover">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    @if(auth()->user()->hasActiveSubscription())
                        <p class="text-2xl font-bold text-surface-50">∞</p>
                    @else
                        <p class="text-2xl font-bold text-surface-50">{{ auth()->user()->test_credits ?? 0 }}</p>
                    @endif
                    <p class="text-xs text-surface-400">Credits Left</p>
                </div>
            </div>
        </div>

        {{-- ── 4-Skill Test Cards ── --}}
        <div class="mb-8">
            <h2 class="section-title mb-4">Start a Test</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Writing --}}
                <a href="{{ route('writing.index') }}"
                   class="card card-hover p-5 group flex flex-col gap-4">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/15 flex items-center justify-center group-hover:bg-purple-500/25 transition-colors">
                        <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-surface-100 group-hover:text-white transition-colors">Writing</p>
                        <p class="text-xs text-surface-400 mt-0.5">Task 1 & Task 2</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-surface-500 mt-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        60 min
                    </div>
                </a>

                {{-- Speaking --}}
                <a href="{{ route('speaking.index') }}"
                   class="card card-hover p-5 group flex flex-col gap-4">
                    <div class="w-11 h-11 rounded-xl bg-brand-500/15 flex items-center justify-center group-hover:bg-brand-500/25 transition-colors">
                        <svg class="w-5 h-5 text-brand-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-surface-100 group-hover:text-white transition-colors">Speaking</p>
                        <p class="text-xs text-surface-400 mt-0.5">3 Parts</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-surface-500 mt-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        11–14 min
                    </div>
                </a>

                {{-- Listening --}}
                <a href="{{ route('listening.index') }}"
                   class="card card-hover p-5 group flex flex-col gap-4 relative overflow-hidden">
                    <span class="absolute top-3 right-3 tag-cyan text-[10px]">New</span>
                    <div class="w-11 h-11 rounded-xl bg-amber-500/15 flex items-center justify-center group-hover:bg-amber-500/25 transition-colors">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M8.464 8.464a5 5 0 000 7.072"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-surface-100 group-hover:text-white transition-colors">Listening</p>
                        <p class="text-xs text-surface-400 mt-0.5">4 Sections</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-surface-500 mt-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        30 min
                    </div>
                </a>

                {{-- Reading --}}
                <a href="{{ route('reading.index') }}"
                   class="card card-hover p-5 group flex flex-col gap-4 relative overflow-hidden">
                    <span class="absolute top-3 right-3 tag-cyan text-[10px]">New</span>
                    <div class="w-11 h-11 rounded-xl bg-rose-500/15 flex items-center justify-center group-hover:bg-rose-500/25 transition-colors">
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-surface-100 group-hover:text-white transition-colors">Reading</p>
                        <p class="text-xs text-surface-400 mt-0.5">Academic & General</p>
                    </div>
                    <div class="flex items-center gap-1 text-xs text-surface-500 mt-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        60 min
                    </div>
                </a>

            </div>
        </div>

        {{-- ── Combined Band Score ── --}}
        @if(count($latestBands) > 0)
        <div class="card p-6 mb-8">
            <h2 class="section-title mb-5">Your IELTS Band</h2>
            <div class="flex flex-wrap items-center gap-6">
                {{-- Overall --}}
                @if($overallBand)
                <div class="text-center">
                    <div class="text-5xl font-bold text-brand-400">{{ $overallBand }}</div>
                    <div class="text-xs text-surface-400 mt-1 uppercase tracking-wider">Overall</div>
                </div>
                <div class="w-px h-16 bg-surface-700 hidden sm:block"></div>
                @endif
                {{-- Per module --}}
                @foreach(['listening'=>'L','reading'=>'R','writing'=>'W','speaking'=>'S'] as $mod => $abbr)
                @if(isset($latestBands[$mod]))
                <div class="text-center">
                    <div class="text-3xl font-bold {{ $latestBands[$mod] >= 7 ? 'text-emerald-400' : ($latestBands[$mod] >= 6 ? 'text-amber-400' : 'text-red-400') }}">
                        {{ $latestBands[$mod] }}
                    </div>
                    <div class="text-xs text-surface-400 mt-1 uppercase tracking-wider">{{ ucfirst($mod) }}</div>
                </div>
                @endif
                @endforeach
                <p class="text-xs text-surface-600 ml-auto self-end">Based on most recent test per module</p>
            </div>
        </div>
        @endif

        {{-- ── Progress Over Time ── --}}
        @php
        $hasAnyScore = collect($chartData)->contains(fn($d) => count($d['scores']) >= 1);
        $moduleColors = ['listening'=>'#f59e0b','reading'=>'#f43f5e','writing'=>'#a855f7','speaking'=>'#06b6d4'];
        $criteriaLabels = [
            'writing'  => ['task_achievement'=>'Task Achievement','coherence_cohesion'=>'Coherence & Cohesion','lexical_resource'=>'Lexical Resource','grammatical_range'=>'Grammar Range'],
            'speaking' => ['fluency_coherence'=>'Fluency & Coherence','lexical_resource'=>'Lexical Resource','grammatical_range'=>'Grammar Range','pronunciation'=>'Pronunciation'],
        ];
        @endphp
        @if($hasAnyScore)
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Progress Over Time</h2>
                {{-- Target Band Setter --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-surface-400">Target Band:</span>
                    <select id="targetBandSelect"
                        onchange="setTargetBand(this.value)"
                        class="bg-surface-800 border border-surface-600 text-surface-200 text-xs rounded-lg px-2 py-1 focus:outline-none focus:border-brand-500">
                        <option value="">— set goal —</option>
                        @foreach([5,5.5,6,6.5,7,7.5,8,8.5,9] as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Combined 4-module chart --}}
            @php $combinedModules = collect($chartData)->filter(fn($d) => count($d['scores']) >= 1); @endphp
            @if($combinedModules->count() > 1)
            <div class="card p-5 mb-4">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">All Modules — IELTS Journey</p>
                <canvas id="chart_combined" height="60"></canvas>
                <div class="flex flex-wrap gap-4 mt-3">
                    @foreach(['listening'=>'Listening','reading'=>'Reading','writing'=>'Writing','speaking'=>'Speaking'] as $key => $label)
                    @if(count($chartData[$key]['scores']) >= 1)
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full" style="background:{{ $moduleColors[$key] }}"></span>
                        <span class="text-xs text-surface-400">{{ $label }}</span>
                    </div>
                    @endif
                    @endforeach
                    <div class="flex items-center gap-1.5 hidden" id="targetLegend">
                        <span class="w-3 h-3 rounded-full border-2 border-dashed" style="border-color:#6366f1;background:transparent"></span>
                        <span class="text-xs text-surface-400">Target</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Per-module mini charts --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(['listening'=>'Listening','reading'=>'Reading','writing'=>'Writing','speaking'=>'Speaking'] as $key => $label)
                @if(count($chartData[$key]['scores']) >= 1)
                <div class="card p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">{{ $label }}</p>
                        @if(isset($improvementDeltas[$key]) && $improvementDeltas[$key] !== null)
                            @if($improvementDeltas[$key] > 0)
                                <span class="tag-green text-[10px]">+{{ $improvementDeltas[$key] }}</span>
                            @elseif($improvementDeltas[$key] < 0)
                                <span class="tag-red text-[10px]">{{ $improvementDeltas[$key] }}</span>
                            @else
                                <span class="tag text-[10px] bg-surface-700 text-surface-400 border-surface-600">→ same</span>
                            @endif
                        @elseif(count($chartData[$key]['scores']) === 1)
                            <span class="tag text-[10px] bg-surface-700 text-surface-500 border-surface-600">Baseline</span>
                        @endif
                    </div>
                    <canvas id="chart_{{ $key }}" height="90"></canvas>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Criteria breakdown for Writing + Speaking --}}
            @if(!empty($criteriaBreakdown))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                @foreach(['writing','speaking'] as $mod)
                @if(!empty($criteriaBreakdown[$mod]))
                @php
                    $modColor = $moduleColors[$mod];
                    $labels   = $criteriaLabels[$mod] ?? [];
                @endphp
                <div class="card p-5">
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">
                        {{ ucfirst($mod) }} — Skill Breakdown
                        <span class="text-surface-600 normal-case font-normal">(most recent test)</span>
                    </p>
                    <div class="space-y-3">
                        @foreach($criteriaBreakdown[$mod] as $criterion => $score)
                        @php
                            $niceName = $labels[$criterion] ?? ucwords(str_replace('_', ' ', $criterion));
                            $pct = ($score / 9) * 100;
                            $barColor = $score >= 7 ? 'bg-emerald-500' : ($score >= 6 ? 'bg-amber-500' : 'bg-red-500');
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-surface-300">{{ $niceName }}</span>
                                <span class="font-semibold text-surface-100">{{ $score }}</span>
                            </div>
                            <div class="w-full bg-surface-700 rounded-full h-1.5">
                                <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif

        </div>
        @endif

        {{-- ── Referral / Earn Free Credits ── --}}
        <div class="card p-5 mb-8 border border-brand-500/20 bg-gradient-to-r from-brand-500/5 to-surface-900">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-brand-500/15 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-0.5">
                        <h3 class="font-semibold text-surface-50 text-sm">Earn Free Credits</h3>
                        @if(auth()->user()->referral_code)
                            <span class="tag-green text-[10px]">Active</span>
                        @endif
                    </div>
                    <p class="text-xs text-surface-400">Invite friends — earn 2 free credits for every friend who signs up using your link.</p>
                    @if(auth()->user()->referral_code)
                        <div class="flex items-center gap-2 mt-2">
                            <code class="flex-1 min-w-0 truncate text-xs bg-surface-800 border border-surface-700 rounded-lg px-3 py-1.5 text-brand-300 font-mono">
                                {{ route('referral.track', auth()->user()->referral_code) }}
                            </code>
                            <button onclick="navigator.clipboard.writeText('{{ route('referral.track', auth()->user()->referral_code) }}').then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy', 1500); })"
                                class="shrink-0 text-xs bg-surface-700 hover:bg-surface-600 border border-surface-600 text-surface-200 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                Copy
                            </button>
                        </div>
                    @endif
                </div>
                <a href="{{ route('referral.show') }}"
                   class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors whitespace-nowrap">
                    @if(auth()->user()->referral_code)
                        View details
                    @else
                        Generate my link
                    @endif
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- ── Recent Tests ── --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-surface-600 flex items-center justify-between">
                <h2 class="section-title">Recent Tests</h2>
                @if($tests->isNotEmpty())
                    <span class="text-xs text-surface-500">{{ $tests->total() }} total</span>
                @endif
            </div>

            @if($tests->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-surface-700 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-surface-300 font-semibold mb-1">No tests yet</p>
                    <p class="text-surface-500 text-sm">Start a test above to track your progress.</p>
                </div>
            @else
                <div class="divide-y divide-surface-600">
                    @foreach($tests as $test)
                    @php
                        $resultRoute = $test->result_route;
                        $typeColors = [
                            'writing'  => ['bg' => 'bg-purple-500/15', 'text' => 'text-purple-400', 'border' => 'border-purple-500/30'],
                            'speaking' => ['bg' => 'bg-brand-500/15',  'text' => 'text-brand-400',  'border' => 'border-brand-500/30'],
                            'listening'=> ['bg' => 'bg-amber-500/15',  'text' => 'text-amber-400',  'border' => 'border-amber-500/30'],
                            'reading'  => ['bg' => 'bg-rose-500/15',   'text' => 'text-rose-400',   'border' => 'border-rose-500/30'],
                        ];
                        $colors = $typeColors[$test->type] ?? $typeColors['writing'];
                        $score = $test->band ?? $test->overall_band ?? $test->score;
                    @endphp
                    <a href="{{ $resultRoute }}" class="flex items-center gap-4 px-6 py-4 hover:bg-surface-700/50 transition-colors group">
                        <div class="w-9 h-9 rounded-xl {{ $colors['bg'] }} flex items-center justify-center shrink-0">
                            @if($test->type === 'speaking')
                                <svg class="w-4 h-4 {{ $colors['text'] }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                            @elseif($test->type === 'listening')
                                <svg class="w-4 h-4 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M8.464 8.464a5 5 0 000 7.072"/></svg>
                            @elseif($test->type === 'reading')
                                <svg class="w-4 h-4 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                            @else
                                <svg class="w-4 h-4 {{ $colors['text'] }}" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-surface-100">{{ ucfirst($test->type) }} Test</span>
                                @if($test->test_type)
                                    <span class="tag bg-surface-700 text-surface-400 border-surface-600 text-[10px]">{{ $test->test_type }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-surface-500 mt-0.5">{{ $test->created_at->format('M d, Y · h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if($score)
                                <div class="text-right">
                                    <p class="text-xl font-bold text-surface-50">{{ $score }}</p>
                                    <p class="text-xs text-surface-500">Band</p>
                                </div>
                            @else
                                <span class="tag-amber">Processing</span>
                            @endif
                            <svg class="w-4 h-4 text-surface-500 group-hover:text-brand-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>

                @if(method_exists($tests, 'hasPages') && $tests->hasPages())
                <div class="px-6 py-4 border-t border-surface-600">
                    {{ $tests->links() }}
                </div>
                @endif
            @endif
        </div>

    </div>
</div>

@if($hasAnyScore ?? false)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartData   = @json($chartData);
const colors      = { listening:'#f59e0b', reading:'#f43f5e', writing:'#a855f7', speaking:'#06b6d4' };
const moduleOrder = ['listening','reading','writing','speaking'];
let targetBand    = parseFloat(localStorage.getItem('ielts_target_band')) || null;

// Restore saved target in select
const sel = document.getElementById('targetBandSelect');
if (sel && targetBand) sel.value = targetBand;

function setTargetBand(val) {
    targetBand = val ? parseFloat(val) : null;
    if (targetBand) localStorage.setItem('ielts_target_band', targetBand);
    else localStorage.removeItem('ielts_target_band');
    // Re-render combined chart
    buildCombinedChart();
    // Update target legend visibility
    const leg = document.getElementById('targetLegend');
    if (leg) leg.classList.toggle('hidden', !targetBand);
}

const chartOptions = (color) => ({
    responsive: true,
    plugins: { legend: { display: false }, tooltip: {
        callbacks: { label: ctx => ' Band ' + ctx.parsed.y }
    }},
    scales: {
        x: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: '#1e293b' } },
        y: { min: 1, max: 9, ticks: { color: '#64748b', font: { size: 9 }, stepSize: 1 }, grid: { color: '#1e293b' } },
    },
});

// Per-module mini charts
moduleOrder.forEach(key => {
    const canvas = document.getElementById('chart_' + key);
    if (!canvas || chartData[key].scores.length < 1) return;

    const labels = chartData[key].labels.length ? chartData[key].labels : ['Test 1'];
    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: chartData[key].scores,
                borderColor: colors[key],
                backgroundColor: colors[key] + '22',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: colors[key],
            }],
        },
        options: chartOptions(colors[key]),
    });
});

// Combined 4-module chart
let combinedChart = null;
function buildCombinedChart() {
    const canvas = document.getElementById('chart_combined');
    if (!canvas) return;

    // Build a unified label set (union of all dates)
    const allLabels = [...new Set(
        moduleOrder.flatMap(k => chartData[k].labels)
    )].sort();

    if (allLabels.length === 0) return;

    const datasets = moduleOrder
        .filter(k => chartData[k].scores.length >= 1)
        .map(k => ({
            label: k.charAt(0).toUpperCase() + k.slice(1),
            data: chartData[k].labels.map((lbl, i) => ({ x: lbl, y: chartData[k].scores[i] })),
            borderColor: colors[k],
            backgroundColor: 'transparent',
            tension: 0.35,
            pointRadius: 4,
            pointBackgroundColor: colors[k],
            borderWidth: 2,
        }));

    // Target band line
    if (targetBand) {
        datasets.push({
            label: 'Target',
            data: allLabels.map(l => ({ x: l, y: targetBand })),
            borderColor: '#6366f1',
            borderDash: [6, 3],
            borderWidth: 1.5,
            backgroundColor: 'transparent',
            pointRadius: 0,
            tension: 0,
        });
    }

    if (combinedChart) combinedChart.destroy();
    combinedChart = new Chart(canvas, {
        type: 'line',
        data: { labels: allLabels, datasets },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: {
                callbacks: { label: ctx => ' ' + ctx.dataset.label + ': Band ' + ctx.parsed.y }
            }},
            scales: {
                x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#1e293b' } },
                y: { min: 1, max: 9, ticks: { color: '#64748b', font: { size: 10 }, stepSize: 1 }, grid: { color: '#1e293b' } },
            },
        },
    });

    // Show target legend if target is set
    const leg = document.getElementById('targetLegend');
    if (leg) leg.classList.toggle('hidden', !targetBand);
}

buildCombinedChart();
</script>
@endif

</x-app-layout>
