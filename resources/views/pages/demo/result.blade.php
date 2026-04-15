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
        /* ── Blur gate ── */
        .blur-gate {
            position: relative;
        }
        .blur-gate-inner {
            filter: blur(5px);
            user-select: none;
            pointer-events: none;
        }
        .blur-gate-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(15,23,42,0.0) 0%, rgba(15,23,42,0.85) 40%, rgba(15,23,42,0.97) 100%);
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 24px;
            border-radius: 16px;
        }

        /* ── Exam mode result styles ── */
        .exam-result-panel {
            display: none;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            border: 1px solid #D0D3DC;
            border-radius: 8px;
            overflow: hidden;
        }
        .exam-result-panel.active { display: block; }
        .practice-panel { display: block; }
        .practice-panel.hidden-panel { display: none; }

        /* Mode toggle */
        .mode-tab {
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 8px;
        }
        .mode-tab.active-dark {
            background: rgba(6,182,212,0.15);
            color: #67e8f9;
            border-color: rgba(6,182,212,0.35);
        }
        .mode-tab.active-white {
            background: #003087;
            color: #fff;
            border-color: #003087;
        }
        .mode-tab:not(.active-dark):not(.active-white) {
            color: #64748b;
        }
        .mode-tab:not(.active-dark):not(.active-white):hover {
            color: #94a3b8;
            background: rgba(255,255,255,0.05);
        }
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
        ['Task Achievement',       'task_achievement',  $ta, 'TA'],
        ['Coherence & Cohesion',   'coherence_cohesion',$cc, 'CC'],
        ['Lexical Resource',       'lexical_resource',  $lr, 'LR'],
        ['Grammar Range',          'grammar',           $gr, 'GR'],
    ];

    $summary           = $scoring['summary'] ?? [];
    $strengths         = $scoring['strengths'] ?? [];
    $improvements      = $scoring['improvements'] ?? [];
    $examiner_comments = $scoring['examiner_comments'] ?? [];
    $band_explanations = $scoring['band_explanations'] ?? [];
    $topic_vocabulary  = $scoring['topic_vocabulary'] ?? [];
    $errors            = $scoring['errors'] ?? [];
    $error_summary     = $scoring['error_summary'] ?? [];

    $demoEmail = 'hello@ieltsbandai.com';
    $demoSubject = urlencode('Institute Demo Request — Saw AI Writing Evaluation');
    $demoBody = urlencode("Hi,\n\nI tried the IELTS Band AI demo and would like to see a full walkthrough for my institute.\n\nInstitute name:\nCity:\nApprox. number of students:\n\nBest time to connect:\n");
@endphp

{{-- Top banner --}}
<div class="bg-amber-500/10 border-b border-amber-500/30 px-4 py-2.5">
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-sm text-amber-300">
            <span>🎓</span>
            <span><strong>Institute Demo</strong> — You're seeing a live AI evaluation. Full report unlocks after a demo call.</span>
        </div>
        <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
           class="shrink-0 text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 px-3 py-1.5 rounded-lg transition-colors">
            Schedule Full Demo →
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
        {{-- Mode toggle --}}
        <div class="flex items-center gap-1 bg-surface-800 border border-surface-700 p-1 rounded-xl">
            <button class="mode-tab" id="tabPractice" onclick="setMode('practice')">
                📝 Practice Mode
            </button>
            <button class="mode-tab" id="tabExam" onclick="setMode('exam')">
                🎓 Exam Simulation
            </button>
        </div>
        <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
           class="shrink-0 text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 px-3 py-1.5 rounded-lg transition-colors hidden sm:inline-flex">
            📅 Schedule Demo
        </a>
    </div>
</header>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    {{-- ══════════════════════════════════
         PRACTICE MODE VIEW
    ══════════════════════════════════ --}}
    <div id="practiceView" class="practice-panel space-y-6">

        {{-- Overall Score Hero --}}
        <div class="card p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6 sm:gap-8">
            <div class="shrink-0">
                <svg class="w-32 h-32" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#1e293b" stroke-width="10"/>
                    @php $pct = min(($overall / 9) * 314, 314); @endphp
                    <circle cx="60" cy="60" r="50" fill="none"
                        stroke="{{ $ringColor }}" stroke-width="10"
                        stroke-dasharray="{{ $pct }} 314"
                        stroke-dashoffset="0"
                        stroke-linecap="round"
                        style="filter:drop-shadow(0 0 8px {{ $ringColor }}88);transform-origin:center;transform:rotate(-90deg)"/>
                    <text x="60" y="55" text-anchor="middle" font-size="28" font-weight="700" fill="#f1f5f9" font-family="Figtree">{{ $overall }}</text>
                    <text x="60" y="73" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="Figtree">OVERALL</text>
                </svg>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <p class="text-surface-400 text-sm uppercase tracking-wider mb-1">AI Band Score</p>
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
            <div class="grid grid-cols-2 gap-3 shrink-0">
                @foreach($criteria as [$label, $key, $score, $abbr])
                <div class="bg-surface-800 border border-surface-600 rounded-xl p-3 text-center min-w-[90px]">
                    <div class="text-2xl font-bold {{ $score >= 7 ? 'text-emerald-400' : ($score >= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $score }}</div>
                    <div class="text-[10px] text-surface-500 mt-0.5">{{ $abbr }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Criteria Breakdown — visible --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider mb-5">Criteria Breakdown</h2>
            <div class="space-y-4">
                @foreach($criteria as [$label, $key, $score, $abbr])
                @php
                    $pct = ($score / 9) * 100;
                    $barColor = $score >= 7 ? 'bg-emerald-500' : ($score >= 6 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-sm text-surface-200">{{ $label }}</span>
                        <span class="font-bold {{ $score >= 7 ? 'text-emerald-400' : ($score >= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $score }}</span>
                    </div>
                    <div class="w-full bg-surface-700 rounded-full h-1.5">
                        <div class="{{ $barColor }} h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Strengths — 1 visible, rest blurred --}}
        @if(!empty($strengths))
        <div class="card p-5">
            <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">What You Did Well</h2>
            <li class="flex items-start gap-2 text-sm text-surface-300 mb-3">
                <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $strengths[0] ?? '' }}
            </li>
            @if(count($strengths) > 1)
            <div style="filter:blur(4px);user-select:none;pointer-events:none;" class="space-y-2 opacity-60">
                @foreach(array_slice($strengths, 1) as $s)
                <li class="flex items-start gap-2 text-sm text-surface-300">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $s }}
                </li>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- BLUR GATE — Error analysis, improvements, vocabulary, examiner comments --}}
        <div class="blur-gate rounded-2xl">
            <div class="blur-gate-inner space-y-4">

                {{-- Improvements --}}
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

                {{-- Error Analysis --}}
                @if(!empty($errors))
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-surface-400 uppercase tracking-wider">Error Analysis</h2>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if(!empty($error_summary['grammar']))   <span class="tag bg-red-500/10 text-red-400 border-red-500/30">{{ $error_summary['grammar'] }} grammar</span> @endif
                            @if(!empty($error_summary['vocabulary'])) <span class="tag bg-orange-500/10 text-orange-400 border-orange-500/30">{{ $error_summary['vocabulary'] }} vocab</span> @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach(array_slice($errors, 0, 4) as $err)
                        <div class="bg-surface-900 border border-surface-700 rounded-xl p-4">
                            <span class="text-xs font-medium px-2 py-0.5 rounded bg-red-500/15 text-red-400">{{ ucfirst($err['type']) }}</span>
                            <p class="text-sm text-surface-400 mt-2 line-through">{{ $err['text'] }}</p>
                            <p class="text-sm text-emerald-400">→ {{ $err['correction'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Examiner comments --}}
                @if(!empty($examiner_comments))
                <div class="card p-5">
                    <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Examiner Comments</h2>
                    <div class="space-y-3">
                        @foreach($examiner_comments as $comment)
                        <div class="flex items-start gap-3 bg-surface-900 border border-surface-700 rounded-xl p-4">
                            <span class="text-brand-400 text-lg shrink-0">💬</span>
                            <p class="text-sm text-surface-300 leading-relaxed">{{ $comment }}</p>
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

                {{-- Band-9 rewrite teaser --}}
                <div class="card p-5 border border-purple-500/20 bg-purple-500/5">
                    <h2 class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-3">Band 9 Rewrite</h2>
                    <p class="text-sm text-surface-400 leading-relaxed">{{ Str::limit($answer ?? '', 200) }}…</p>
                </div>

            </div>

            {{-- Blur overlay CTA --}}
            <div class="blur-gate-overlay">
                <div class="text-center px-6 py-5 bg-surface-900/90 backdrop-blur-sm rounded-xl border border-surface-700 max-w-md mx-4">
                    <div class="text-2xl mb-2">🔒</div>
                    <p class="text-surface-50 font-bold text-base mb-1">Full Report Locked</p>
                    <p class="text-surface-400 text-xs mb-3 leading-relaxed">
                        Error highlights · Examiner comments · Improvement tips · Vocabulary suggestions · Band-9 rewrite
                    </p>
                    <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
                       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-black font-bold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-lg">
                        📅 Schedule a Demo to Unlock
                    </a>
                    <p class="text-surface-600 text-[11px] mt-2">For IELTS coaching institutes only</p>
                </div>
            </div>
        </div>

    </div>{{-- end practiceView --}}


    {{-- ══════════════════════════════════
         EXAM SIMULATION VIEW
    ══════════════════════════════════ --}}
    <div id="examView" class="exam-result-panel space-y-0" style="display:none;">

        {{-- Exam header --}}
        <div style="background:#fff;border-bottom:2px solid #003087;height:50px;display:flex;align-items:center;padding:0 20px;font-family:Arial,sans-serif;">
            <span style="font-size:11px;font-weight:bold;color:#003087;letter-spacing:.06em;text-transform:uppercase;min-width:180px;">IELTS Band AI</span>
            <span style="flex:1;text-align:center;font-size:14px;font-weight:bold;color:#1a1a1a;">Academic Writing Test — Task 2 Result</span>
            <span style="min-width:180px;text-align:right;font-size:12px;color:#555;">Demo Report</span>
        </div>

        <div style="padding:24px 28px;font-family:Arial,sans-serif;background:#fff;">

            {{-- Score row --}}
            <div style="display:flex;align-items:center;gap:24px;padding:20px;background:#F5F6FA;border:1px solid #D0D3DC;border-radius:4px;margin-bottom:20px;">
                <div style="text-align:center;min-width:100px;">
                    <div style="font-size:48px;font-weight:bold;color:#003087;line-height:1;">{{ $overall }}</div>
                    <div style="font-size:11px;color:#555;text-transform:uppercase;letter-spacing:.06em;margin-top:4px;">Overall Band</div>
                </div>
                <div style="width:1px;height:60px;background:#D0D3DC;"></div>
                <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    @foreach($criteria as [$label, $key, $score, $abbr])
                    <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border:1px solid #D0D3DC;padding:8px 12px;">
                        <span style="font-size:12px;color:#333;">{{ $label }}</span>
                        <span style="font-size:16px;font-weight:bold;color:#003087;">{{ $score }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Examiner summary --}}
            @if(!empty($scoring['feedback']))
            <div style="background:#EEF0F8;border-left:3px solid #003087;padding:12px 16px;margin-bottom:20px;">
                <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Examiner Summary</div>
                <p style="font-size:13px;color:#1a1a1a;line-height:1.65;">{{ $scoring['feedback'] }}</p>
            </div>
            @endif

            {{-- Strength visible --}}
            @if(!empty($strengths[0]))
            <div style="margin-bottom:16px;">
                <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Key Strength</div>
                <div style="display:flex;align-items:flex-start;gap:8px;padding:10px 14px;background:#F0FFF4;border:1px solid #B7EBC6;">
                    <span style="color:#006600;font-size:14px;">✓</span>
                    <p style="font-size:13px;color:#1a1a1a;margin:0;">{{ $strengths[0] }}</p>
                </div>
            </div>
            @endif

            {{-- BLUR GATE — exam style --}}
            <div style="position:relative;border-radius:4px;overflow:hidden;margin-top:8px;">
                <div style="filter:blur(5px);user-select:none;pointer-events:none;">
                    {{-- Error analysis preview --}}
                    <div style="margin-bottom:16px;">
                        <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Error Analysis</div>
                        @foreach(array_slice($errors, 0, 3) as $err)
                        <div style="background:#FFF5F5;border:1px solid #FFCCCC;padding:10px 14px;margin-bottom:6px;">
                            <span style="font-size:11px;font-weight:bold;color:#CC0000;text-transform:uppercase;">{{ $err['type'] }}</span>
                            <p style="font-size:12px;color:#555;text-decoration:line-through;margin:4px 0;">{{ $err['text'] }}</p>
                            <p style="font-size:12px;color:#006600;">→ {{ $err['correction'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    {{-- Vocabulary --}}
                    @if(!empty($topic_vocabulary))
                    <div>
                        <div style="font-size:10px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Suggested Vocabulary</div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach($topic_vocabulary as $word)
                            <span style="padding:4px 10px;background:#EEF0F8;border:1px solid #C8CADC;font-size:12px;color:#003087;">{{ $word }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Overlay CTA --}}
                <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(255,255,255,0) 0%,rgba(255,255,255,0.9) 40%,rgba(255,255,255,0.98) 100%);display:flex;align-items:flex-end;justify-content:center;padding-bottom:20px;">
                    <div style="text-align:center;max-width:380px;">
                        <div style="font-size:20px;margin-bottom:6px;">🔒</div>
                        <p style="font-size:14px;font-weight:bold;color:#1a1a1a;margin-bottom:4px;">Full Report Available in Demo</p>
                        <p style="font-size:12px;color:#555;margin-bottom:14px;line-height:1.5;">Error highlights · Examiner comments · Band-9 rewrite · Vocabulary suggestions</p>
                        <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
                           style="display:inline-block;padding:10px 24px;background:#003087;color:#fff;font-size:13px;font-weight:bold;text-decoration:none;font-family:Arial,sans-serif;border-radius:2px;">
                            📅 Schedule Full Demo
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Exam nav bar --}}
        <div style="background:#F0F2F7;border-top:2px solid #003087;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;font-family:Arial,sans-serif;">
            <span style="font-size:12px;color:#555;">This report was generated by IELTS Band AI — Institute Demo</span>
            <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
               style="padding:6px 18px;background:#003087;color:#fff;font-size:12px;font-weight:bold;text-decoration:none;border-radius:2px;">
                Get Full Access →
            </a>
        </div>

    </div>{{-- end examView --}}


    {{-- ══════════════════════════════════
         BOTTOM CTA (both modes)
    ══════════════════════════════════ --}}
    <div class="card p-6 border border-amber-500/25 bg-gradient-to-br from-amber-500/5 to-surface-900">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 flex items-center justify-center shrink-0 text-2xl">🎓</div>
            <div class="flex-1">
                <h3 class="font-bold text-surface-50 mb-1">Running an IELTS coaching institute?</h3>
                <p class="text-surface-400 text-sm">See the full product — student management, batch assignment, institute dashboard, and complete AI reports for every student.</p>
                <div class="flex flex-wrap gap-3 mt-2 text-xs text-surface-500">
                    <span>✓ Manage students & batches</span>
                    <span>✓ Assign tests</span>
                    <span>✓ Track progress</span>
                    <span>✓ INR pricing</span>
                </div>
            </div>
            <a href="mailto:{{ $demoEmail }}?subject={{ $demoSubject }}&body={{ $demoBody }}"
               class="shrink-0 inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-black font-bold px-5 py-3 rounded-xl text-sm transition-colors shadow-lg">
                📅 Schedule Institute Demo
            </a>
        </div>
    </div>

    {{-- B2C fallback --}}
    <div class="text-center py-2">
        <p class="text-surface-500 text-sm">Individual student?
            <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-semibold">Create a free account →</a>
        </p>
    </div>

</div>

<script>
function setMode(mode) {
    const practiceView = document.getElementById('practiceView');
    const examView     = document.getElementById('examView');
    const tabPractice  = document.getElementById('tabPractice');
    const tabExam      = document.getElementById('tabExam');

    if (mode === 'practice') {
        practiceView.style.display = 'block';
        examView.style.display     = 'none';
        tabPractice.className = 'mode-tab active-dark';
        tabExam.className     = 'mode-tab';
    } else {
        practiceView.style.display = 'none';
        examView.style.display     = 'block';
        tabPractice.className = 'mode-tab';
        tabExam.className     = 'mode-tab active-white';
    }
}

// Default to the mode the user chose on the demo page
setMode('{{ $demoMode ?? 'practice' }}');
</script>

</body>
</html>
