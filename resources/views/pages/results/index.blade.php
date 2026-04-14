<x-app-layout>
@php
    $feedbackText = is_array($feedback ?? null)
        ? implode(' ', array_filter($feedback))
        : ($feedback ?? '');
    $overall      = $scores['overall_band'] ?? 0;
    $bandColor    = $overall >= 7.5 ? 'emerald' : ($overall >= 6.5 ? 'brand' : ($overall >= 5.5 ? 'amber' : 'red'));
    $bandLabel    = $overall >= 8 ? 'Expert' : ($overall >= 7 ? 'Good' : ($overall >= 6 ? 'Competent' : ($overall >= 5 ? 'Modest' : 'Limited')));
    $criteria = [
        'task_achievement'   => ['label' => 'Task Achievement',          'short' => 'TA'],
        'coherence_cohesion' => ['label' => 'Coherence & Cohesion',      'short' => 'CC'],
        'lexical_resource'   => ['label' => 'Lexical Resource',          'short' => 'LR'],
        'grammar'            => ['label' => 'Grammatical Range & Accuracy','short' => 'GRA'],
    ];
    $errors             = $errors             ?? [];
    $unpositioned_errors= $unpositioned_errors?? [];
    $band_explanations  = $band_explanations  ?? [];
    $examiner_comments  = $examiner_comments  ?? [];
    $topic_vocabulary   = $topic_vocabulary   ?? [];
    $strengths          = $strengths          ?? [];
    $improvements       = $improvements       ?? [];
    $error_summary      = $error_summary      ?? [];
    $question_title     = $question->title ?? ($question->content ?? '');
@endphp

<style>
.error-grammar     { background: rgba(239,68,68,0.18); border-bottom: 2px solid #ef4444; cursor: pointer; }
.error-vocabulary  { background: rgba(251,146,60,0.18); border-bottom: 2px solid #f97316; cursor: pointer; }
.error-cohesion    { background: rgba(34,197,94,0.18);  border-bottom: 2px solid #22c55e; cursor: pointer; }
.error-punctuation { background: rgba(59,130,246,0.18); border-bottom: 2px solid #3b82f6; cursor: pointer; }
.error-grammar:hover,.error-vocabulary:hover,.error-cohesion:hover,.error-punctuation:hover { opacity:.8; }
</style>

{{-- ── Print-only certificate (hidden on screen) ── --}}
<div id="print-report" style="display:none;">

    {{-- PAGE 1: Certificate of Achievement --}}
    <div style="position:relative;min-height:100vh;background:#ffffff;padding:0;page-break-after:always;">
        {{-- Outer border --}}
        <div style="position:absolute;inset:18px;border:3px solid #0f172a;pointer-events:none;"></div>
        <div style="position:absolute;inset:24px;border:1px solid #94a3b8;pointer-events:none;"></div>
        {{-- Corner marks --}}
        <div style="position:absolute;top:32px;left:32px;width:20px;height:20px;border-top:3px solid #b45309;border-left:3px solid #b45309;"></div>
        <div style="position:absolute;top:32px;right:32px;width:20px;height:20px;border-top:3px solid #b45309;border-right:3px solid #b45309;"></div>
        <div style="position:absolute;bottom:32px;left:32px;width:20px;height:20px;border-bottom:3px solid #b45309;border-left:3px solid #b45309;"></div>
        <div style="position:absolute;bottom:32px;right:32px;width:20px;height:20px;border-bottom:3px solid #b45309;border-right:3px solid #b45309;"></div>

        <div style="padding:60px 80px;text-align:center;">
            {{-- Logo + Title --}}
            <div style="margin-bottom:8px;">
                <div style="display:inline-block;width:52px;height:52px;background:#0f172a;color:white;border-radius:10px;font-weight:900;font-size:20px;line-height:52px;margin-bottom:10px;">AI</div>
                <h1 style="font-size:28px;font-weight:900;color:#0f172a;letter-spacing:5px;margin:0 0 4px;text-transform:uppercase;">IELTS Band AI</h1>
                <p style="font-size:11px;color:#64748b;letter-spacing:5px;text-transform:uppercase;margin:0;">Certificate of Achievement</p>
            </div>

            {{-- Diamond divider --}}
            <div style="display:flex;align-items:center;gap:12px;margin:28px auto;max-width:380px;">
                <div style="flex:1;height:1px;background:linear-gradient(to right,transparent,#94a3b8);"></div>
                <div style="width:7px;height:7px;background:#b45309;transform:rotate(45deg);"></div>
                <div style="flex:1;height:1px;background:linear-gradient(to left,transparent,#94a3b8);"></div>
            </div>

            <p style="font-size:12px;color:#475569;letter-spacing:4px;text-transform:uppercase;margin-bottom:18px;">This is to certify that</p>

            {{-- Candidate name --}}
            <p style="font-size:38px;font-family:Georgia,serif;font-style:italic;color:#0f172a;margin:0 0 6px;padding-bottom:10px;border-bottom:2px solid #e2e8f0;display:inline-block;min-width:340px;">{{ Auth::user()->name }}</p>

            <p style="font-size:13px;color:#475569;margin:20px 0 6px;line-height:1.8;">
                has successfully completed the <strong style="color:#0f172a;">IELTS Writing Assessment</strong><br>
                and achieved an Overall Band Score of
            </p>

            {{-- Score seal --}}
            <div style="display:inline-flex;flex-direction:column;align-items:center;margin:16px 0 24px;">
                <div style="position:relative;width:100px;height:100px;display:flex;align-items:center;justify-content:center;">
                    <div style="position:absolute;inset:0;border-radius:50%;border:3px solid #0f172a;"></div>
                    <div style="position:absolute;inset:6px;border-radius:50%;border:1px solid #cbd5e1;"></div>
                    <div style="text-align:center;">
                        <span style="font-size:38px;font-weight:900;color:#0f172a;font-family:Georgia,serif;line-height:1;">{{ number_format($scores['overall_band'] ?? 0, 1) }}</span>
                    </div>
                </div>
                <p style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:3px;margin-top:6px;">out of 9.0 bands</p>
            </div>

            {{-- Diamond divider --}}
            <div style="display:flex;align-items:center;gap:12px;margin:0 auto 28px;max-width:380px;">
                <div style="flex:1;height:1px;background:linear-gradient(to right,transparent,#94a3b8);"></div>
                <div style="width:7px;height:7px;background:#b45309;transform:rotate(45deg);"></div>
                <div style="flex:1;height:1px;background:linear-gradient(to left,transparent,#94a3b8);"></div>
            </div>

            {{-- 4 criteria --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;max-width:520px;margin:0 auto 36px;">
                <div style="border:1px solid #e2e8f0;border-top:3px solid #b45309;padding:12px 8px;background:#fafafa;border-radius:4px;">
                    <span style="display:block;font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Task Response</span>
                    <span style="display:block;font-size:26px;font-weight:900;color:#0f172a;font-family:Georgia,serif;">{{ number_format($scores['task_achievement'] ?? 0, 1) }}</span>
                </div>
                <div style="border:1px solid #e2e8f0;border-top:3px solid #b45309;padding:12px 8px;background:#fafafa;border-radius:4px;">
                    <span style="display:block;font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Coherence</span>
                    <span style="display:block;font-size:26px;font-weight:900;color:#0f172a;font-family:Georgia,serif;">{{ number_format($scores['coherence_cohesion'] ?? 0, 1) }}</span>
                </div>
                <div style="border:1px solid #e2e8f0;border-top:3px solid #b45309;padding:12px 8px;background:#fafafa;border-radius:4px;">
                    <span style="display:block;font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Lexical</span>
                    <span style="display:block;font-size:26px;font-weight:900;color:#0f172a;font-family:Georgia,serif;">{{ number_format($scores['lexical_resource'] ?? 0, 1) }}</span>
                </div>
                <div style="border:1px solid #e2e8f0;border-top:3px solid #b45309;padding:12px 8px;background:#fafafa;border-radius:4px;">
                    <span style="display:block;font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Grammar</span>
                    <span style="display:block;font-size:26px;font-weight:900;color:#0f172a;font-family:Georgia,serif;">{{ number_format($scores['grammar'] ?? 0, 1) }}</span>
                </div>
            </div>

            {{-- Feedback summary --}}
            <div style="max-width:540px;margin:0 auto 40px;padding:16px 20px;background:#f8fafc;border-left:3px solid #0f172a;text-align:left;border-radius:2px;">
                <p style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:2px;margin-bottom:6px;">Examiner Feedback</p>
                <p style="font-size:12px;color:#334155;font-style:italic;line-height:1.7;margin:0;">"{{ $feedbackText ?? 'No specific feedback available.' }}"</p>
            </div>

            {{-- Signature row --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;max-width:480px;margin:0 auto;">
                <div style="text-align:center;">
                    <div style="border-top:1px solid #94a3b8;padding-top:8px;">
                        <p style="font-size:11px;color:#64748b;letter-spacing:2px;text-transform:uppercase;margin-bottom:2px;">Date of Assessment</p>
                        <p style="font-size:13px;font-weight:700;color:#0f172a;">{{ now()->format('d F Y') }}</p>
                    </div>
                </div>
                <div style="text-align:center;">
                    <div style="border-top:1px solid #94a3b8;padding-top:8px;">
                        <p style="font-size:11px;color:#64748b;letter-spacing:2px;text-transform:uppercase;margin-bottom:2px;">Issued By</p>
                        <p style="font-size:13px;font-weight:700;color:#0f172a;">IELTS Band AI</p>
                    </div>
                </div>
            </div>

            {{-- Bottom test ID --}}
            <p style="font-size:9px;color:#94a3b8;margin-top:32px;letter-spacing:2px;">TEST ID: {{ $test->id ?? 'N/A' }} &nbsp;·&nbsp; {{ url('/') }}</p>
        </div>
    </div>

    {{-- PAGE 2: Detailed Report --}}
    <div style="background:#ffffff;padding:48px 60px;">
        {{-- Report header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #0f172a;padding-bottom:16px;margin-bottom:32px;">
            <div>
                <h2 style="font-size:20px;font-weight:900;color:#0f172a;letter-spacing:2px;text-transform:uppercase;margin:0 0 2px;">Detailed Assessment Report</h2>
                <p style="font-size:10px;color:#64748b;letter-spacing:3px;text-transform:uppercase;margin:0;">{{ Auth::user()->name }} &nbsp;·&nbsp; {{ now()->format('d F Y') }}</p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:0 0 2px;">Overall Band</p>
                <p style="font-size:28px;font-weight:900;color:#0f172a;margin:0;font-family:Georgia,serif;">{{ number_format($scores['overall_band'] ?? 0, 1) }}</p>
            </div>
        </div>

        {{-- Strengths & Improvements --}}
        @if(count($strengths) > 0 || count($improvements) > 0)
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;">
            @if(count($strengths) > 0)
            <div style="border:1px solid #bbf7d0;border-radius:8px;padding:18px;background:#f0fdf4;">
                <h3 style="font-weight:700;color:#14532d;margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Strengths</h3>
                @foreach($strengths as $strength)
                <p style="font-size:12px;color:#1f2937;margin:0 0 6px;display:flex;gap:8px;"><span style="color:#16a34a;font-weight:700;">✓</span><span>{{ $strength }}</span></p>
                @endforeach
            </div>
            @endif
            @if(count($improvements) > 0)
            <div style="border:1px solid #bfdbfe;border-radius:8px;padding:18px;background:#eff6ff;">
                <h3 style="font-weight:700;color:#1e3a5f;margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Areas for Improvement</h3>
                @foreach($improvements as $improvement)
                <p style="font-size:12px;color:#1f2937;margin:0 0 6px;display:flex;gap:8px;"><span style="color:#2563eb;font-weight:700;">→</span><span>{{ $improvement }}</span></p>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- Essay --}}
        <div style="margin-bottom:32px;break-inside:avoid;">
            <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:2px solid #e2e8f0;padding-bottom:6px;margin-bottom:14px;">Your Essay</h3>
            <div style="display:flex;gap:16px;margin-bottom:10px;font-size:10px;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                <span style="font-weight:700;color:#374151;">LEGEND:</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#fee2e2;border:1px solid #ef4444;display:inline-block;border-radius:2px;"></span>Grammar</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#ffedd5;border:1px solid #f97316;display:inline-block;border-radius:2px;"></span>Vocabulary</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#fef3c7;border:1px solid #f59e0b;display:inline-block;border-radius:2px;"></span>Cohesion</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:#dbeafe;border:1px solid #3b82f6;display:inline-block;border-radius:2px;"></span>Punctuation</span>
            </div>
            <div style="font-size:12px;color:#1f2937;line-height:1.8;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;text-align:justify;">{!! $highlightedEssay !!}</div>
        </div>

        {{-- Errors --}}
        @if(count($errors) > 0)
        <div style="break-before:page;">
            <h3 style="font-size:15px;font-weight:700;color:#0f172a;border-bottom:2px solid #e2e8f0;padding-bottom:6px;margin-bottom:14px;">Error Analysis</h3>
            @foreach($errors as $index => $error)
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;margin-bottom:10px;background:#fff;break-inside:avoid;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:26px;height:26px;border-radius:50%;background:#ede9fe;color:#5b21b6;font-weight:700;font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $index + 1 }}</div>
                    <div style="flex:1;">
                        <span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:1px;background:#f1f5f9;color:#374151;margin-bottom:6px;">{{ $error['category'] ?? $error['type'] ?? 'Issue' }}</span>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:6px;">
                            <div><span style="font-size:9px;color:#ef4444;font-weight:700;text-transform:uppercase;">Original</span><p style="font-size:11px;color:#b91c1c;background:#fef2f2;padding:4px 8px;border-radius:3px;margin:3px 0 0;">{{ $error['original_text'] ?? $error['text'] ?? 'N/A' }}</p></div>
                            <div><span style="font-size:9px;color:#16a34a;font-weight:700;text-transform:uppercase;">Correction</span><p style="font-size:11px;color:#15803d;background:#f0fdf4;padding:4px 8px;border-radius:3px;margin:3px 0 0;">{{ $error['correction'] ?? 'N/A' }}</p></div>
                        </div>
                        @if(!empty($error['explanation']))
                        <p style="font-size:11px;color:#475569;font-style:italic;margin:0;">{{ $error['explanation'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<div class="min-h-screen bg-surface-950 print:hidden" x-data="resultPageData()">
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('dashboard') }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Dashboard
        </a>
        <div class="flex items-center gap-3">
            <span class="tag-cyan">{{ $task_info['title'] ?? 'Writing Test' }}</span>
            <span class="text-surface-500 text-xs">· Test #{{ $test->id }}</span>
            <button @click="openShareModal = true" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                Share
            </button>
            <a href="{{ route('writing.pdf', $test->id) }}" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
            <button onclick="window.print()" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>
    </div>

    {{-- ── Band Hero ── --}}
    <div class="card overflow-hidden mb-6">
        <div class="bg-gradient-to-br from-brand-700/40 via-brand-800/30 to-surface-900 px-8 py-10 text-center relative">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent pointer-events-none"></div>
            <p class="text-surface-400 text-sm uppercase tracking-widest mb-2">Overall Band Score</p>
            <div class="text-8xl font-bold text-surface-50 mb-2 tabular-nums" style="font-variant-numeric:tabular-nums;">
                {{ number_format($overall, 1) }}
            </div>
            <p class="text-brand-300 text-lg font-semibold mb-1">{{ $bandLabel }} User</p>
            @if(!empty($scores['band_confidence_range']))
            <p class="text-surface-500 text-xs">Confidence range: {{ $scores['band_confidence_range'] }}</p>
            @endif
            {{-- Word count & time --}}
            <div class="flex items-center justify-center gap-6 mt-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-surface-100">{{ $word_count ?? '—' }}</p>
                    <p class="text-xs text-surface-500">words</p>
                </div>
                <div class="w-px h-8 bg-surface-700"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-surface-100">{{ $task_info['word_limit'] ?? '—' }}+</p>
                    <p class="text-xs text-surface-500">required</p>
                </div>
                @if($test->duration_seconds)
                <div class="w-px h-8 bg-surface-700"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-surface-100">{{ gmdate('i:s', $test->duration_seconds) }}</p>
                    <p class="text-xs text-surface-500">time taken</p>
                </div>
                @endif
            </div>
        </div>

        {{-- 4 criteria mini-bars ── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-surface-700 border-t border-surface-700">
            @foreach($criteria as $key => $info)
            @php $s = $scores[$key] ?? 0; $pct = min(100, ($s/9)*100); @endphp
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-surface-500 mb-1">{{ $info['short'] }}</p>
                <p class="text-2xl font-bold {{ $s >= 7 ? 'text-emerald-400' : ($s >= 6 ? 'text-brand-400' : ($s >= 5 ? 'text-amber-400' : 'text-red-400')) }}">{{ number_format($s,1) }}</p>
                <p class="text-[10px] text-surface-500 truncate mt-0.5">{{ $info['label'] }}</p>
                <div class="w-full bg-surface-700 rounded-full h-1 mt-2">
                    <div class="h-1 rounded-full {{ $s >= 7 ? 'bg-emerald-500' : ($s >= 6 ? 'bg-brand-500' : ($s >= 5 ? 'bg-amber-500' : 'bg-red-500')) }}" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left column ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Question --}}
            @if($question_title)
            <div class="card p-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-2">Question</p>
                <p class="text-surface-200 text-sm leading-relaxed">{{ $question_title }}</p>
            </div>
            @endif

            {{-- Highlighted Essay --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-700 flex items-center justify-between">
                    <p class="text-sm font-semibold text-surface-200">Your Essay</p>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="flex items-center gap-1 text-[10px] text-red-400"><span class="w-2.5 h-2.5 rounded-sm bg-red-500/30 border border-red-500 inline-block"></span>Grammar</span>
                        <span class="flex items-center gap-1 text-[10px] text-orange-400"><span class="w-2.5 h-2.5 rounded-sm bg-orange-500/30 border border-orange-500 inline-block"></span>Vocabulary</span>
                        <span class="flex items-center gap-1 text-[10px] text-emerald-400"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500/30 border border-emerald-500 inline-block"></span>Cohesion</span>
                        <span class="flex items-center gap-1 text-[10px] text-blue-400"><span class="w-2.5 h-2.5 rounded-sm bg-blue-500/30 border border-blue-500 inline-block"></span>Punctuation</span>
                    </div>
                </div>
                <div class="p-5 text-surface-200 text-sm leading-relaxed essay-text" id="essayText">
                    {!! $highlightedEssay !!}
                </div>
            </div>

            {{-- Error tooltip panel (appears on click) --}}
            <div id="errorTooltip" class="hidden card p-4 border border-surface-600 text-sm" style="display:none!important;">
                <div class="flex items-start gap-3">
                    <div id="tooltipDot" class="w-3 h-3 rounded-full shrink-0 mt-1"></div>
                    <div>
                        <p class="font-semibold text-surface-100" id="tooltipType"></p>
                        <p class="text-surface-400 text-xs mt-0.5" id="tooltipOriginal"></p>
                        <p class="text-emerald-400 text-xs mt-1" id="tooltipCorrection"></p>
                        <p class="text-surface-300 text-xs mt-2 leading-relaxed" id="tooltipExplanation"></p>
                    </div>
                </div>
            </div>

            {{-- Errors list --}}
            @if(count($errors) > 0 || count($unpositioned_errors) > 0)
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-700">
                    <p class="text-sm font-semibold text-surface-200">Error Analysis
                        <span class="ml-2 text-xs text-surface-500">({{ count($errors) + count($unpositioned_errors) }} found)</span>
                    </p>
                </div>
                <div class="divide-y divide-surface-700/50">
                    @php $allErrors = array_merge($errors, $unpositioned_errors); @endphp
                    @foreach($allErrors as $i => $err)
                    @php
                        $cat = strtolower($err['category'] ?? $err['type'] ?? 'grammar');
                        $dotColor = match(true) {
                            str_contains($cat,'gram')  => 'bg-red-500',
                            str_contains($cat,'vocab') || str_contains($cat,'word') || str_contains($cat,'spell') => 'bg-orange-500',
                            str_contains($cat,'cohes') || str_contains($cat,'coher') || str_contains($cat,'link') => 'bg-emerald-500',
                            str_contains($cat,'punct') => 'bg-blue-500',
                            default => 'bg-surface-500',
                        };
                        $sev = $err['severity'] ?? 'medium';
                    @endphp
                    <div class="px-5 py-4 flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg {{ $dotColor }}/20 border border-{{ str_contains($cat,'gram') ? 'red' : (str_contains($cat,'vocab') || str_contains($cat,'word') ? 'orange' : (str_contains($cat,'cohes') ? 'emerald' : (str_contains($cat,'punct') ? 'blue' : 'surface-5'))) }}-500/40 flex items-center justify-center text-xs font-bold text-surface-300 shrink-0">{{ $i+1 }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-[10px] font-semibold uppercase tracking-wide {{ $dotColor }} text-surface-50 px-1.5 py-0.5 rounded">{{ ucfirst($cat) }}</span>
                                @if($sev === 'high')
                                <span class="text-[10px] text-red-400 border border-red-500/30 px-1.5 py-0.5 rounded">High</span>
                                @elseif($sev === 'medium')
                                <span class="text-[10px] text-amber-400 border border-amber-500/30 px-1.5 py-0.5 rounded">Medium</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-xs mb-1">
                                <span class="line-through text-red-400/70">{{ $err['text'] ?? $err['original_text'] ?? '' }}</span>
                                @if(!empty($err['correction']))
                                <svg class="w-3 h-3 text-surface-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span class="text-emerald-400">{{ $err['correction'] }}</span>
                                @endif
                            </div>
                            @if(!empty($err['explanation']))
                            <p class="text-xs text-surface-400 leading-relaxed">{{ $err['explanation'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Examiner Comments --}}
            @if(count($examiner_comments) > 0)
            <div class="card p-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Examiner Comments</p>
                <div class="space-y-3">
                    @foreach($examiner_comments as $comment)
                    <div class="flex items-start gap-3 bg-brand-500/5 border border-brand-500/20 rounded-xl p-3">
                        <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-surface-300 leading-relaxed">{{ $comment }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Band 9 Rewrite --}}
            <div class="card overflow-hidden" id="band9Section">
                <div class="px-5 py-4 border-b border-surface-700 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-surface-200">Band 9 Model Answer</p>
                        <p class="text-xs text-surface-500 mt-0.5">AI-generated reference response for this question</p>
                    </div>
                    <button id="loadBand9Btn" onclick="loadBand9()"
                        class="btn-secondary text-xs px-3 py-1.5">
                        Generate
                    </button>
                </div>
                <div id="band9Content" class="p-5 text-surface-300 text-sm leading-relaxed hidden">
                    <div id="band9Loading" class="flex items-center gap-2 text-surface-400">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Generating Band 9 model answer…
                    </div>
                    <div id="band9Text" class="hidden"></div>
                </div>
                @if(!empty($band_9_rewrite))
                <div class="p-5 text-surface-300 text-sm leading-relaxed whitespace-pre-line">{{ $band_9_rewrite }}</div>
                @endif
            </div>

        </div>

        {{-- ── Right sidebar ── --}}
        <div class="space-y-5">

            {{-- Feedback --}}
            @if($feedbackText)
            <div class="card p-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Overall Feedback</p>
                <p class="text-sm text-surface-300 leading-relaxed">{{ $feedbackText }}</p>
            </div>
            @endif

            {{-- Strengths --}}
            @if(count($strengths) > 0)
            <div class="card p-5">
                <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-3">Strengths</p>
                <ul class="space-y-2">
                    @foreach($strengths as $s)
                    <li class="flex items-start gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $s }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Improvements --}}
            @if(count($improvements) > 0)
            <div class="card p-5">
                <p class="text-xs font-semibold text-amber-400 uppercase tracking-wider mb-3">To Improve</p>
                <ul class="space-y-2">
                    @foreach($improvements as $imp)
                    <li class="flex items-start gap-2 text-sm text-surface-300">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        {{ $imp }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Per-criterion breakdown --}}
            @foreach($criteria as $key => $info)
            @php
                $s    = $scores[$key] ?? 0;
                $expl = $band_explanations[$key] ?? [];
                $why  = $expl['why'] ?? null;
                $tip  = $expl['tip'] ?? null;
                $col  = $s >= 7 ? 'emerald' : ($s >= 6 ? 'brand' : ($s >= 5 ? 'amber' : 'red'));
            @endphp
            @if($why || $tip)
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider">{{ $info['short'] }} — {{ $info['label'] }}</p>
                    <span class="text-lg font-bold text-{{ $col }}-400">{{ number_format($s,1) }}</span>
                </div>
                @if($why)
                <p class="text-xs text-surface-300 leading-relaxed mb-2">{{ $why }}</p>
                @endif
                @if($tip)
                <div class="flex items-start gap-2 bg-brand-500/10 border border-brand-500/20 rounded-lg px-3 py-2">
                    <svg class="w-3.5 h-3.5 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    <p class="text-xs text-brand-300">{{ $tip }}</p>
                </div>
                @endif
            </div>
            @endif
            @endforeach

            {{-- Topic Vocabulary --}}
            @if(count($topic_vocabulary) > 0)
            <div class="card p-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Advanced Topic Vocabulary</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($topic_vocabulary as $word)
                    <span class="px-2.5 py-1 bg-surface-700 border border-surface-600 text-surface-200 rounded-lg text-xs font-medium">{{ $word }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Error breakdown summary --}}
            @if(!empty($error_summary))
            <div class="card p-5">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Error Patterns</p>
                @if(isset($error_summary['grammar_errors_per_100_words']))
                <div class="flex items-center justify-between py-2 border-b border-surface-700">
                    <span class="text-xs text-surface-400">Errors per 100 words</span>
                    <span class="text-sm font-bold {{ ($error_summary['grammar_errors_per_100_words'] ?? 0) <= 3 ? 'text-emerald-400' : (($error_summary['grammar_errors_per_100_words'] ?? 0) <= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $error_summary['grammar_errors_per_100_words'] }}</span>
                </div>
                @endif
                @if(!empty($error_summary['repeated_errors']))
                <p class="text-xs text-surface-500 mt-2 mb-1">Recurring patterns:</p>
                <div class="space-y-1">
                    @foreach(array_slice($error_summary['repeated_errors'], 0, 4) as $pattern)
                    <p class="text-xs text-surface-400 flex items-center gap-1.5">
                        <span class="w-1 h-1 rounded-full bg-red-500 shrink-0"></span>{{ $pattern }}
                    </p>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- Mock test continuation (writing) --}}
            @if(session('mock_test_id'))
            <div class="card p-5 border border-brand-500/30 bg-brand-500/5 mb-3">
                <p class="font-semibold text-brand-400 text-sm mb-3">✅ Writing complete — continue your mock test</p>
                <form method="POST" action="{{ route('mock-test.advance', ['mock' => session('mock_test_id'), 'module' => 'writing']) }}">
                    @csrf
                    <input type="hidden" name="test_id" value="{{ $test->id }}">
                    <button type="submit" class="btn-primary w-full justify-center py-2.5">Continue to Speaking →</button>
                </form>
            </div>
            @endif

            {{-- Try again --}}
            <a href="{{ route('writing.index') }}" class="btn-primary w-full justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Take Another Writing Test
            </a>

        </div>
    </div>

    {{-- ── Share Modal ── --}}
    <div x-show="openShareModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         style="display:none;">
        <div @click.away="openShareModal = false"
             class="bg-surface-900 border border-surface-700 rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="sticky top-0 z-10 flex items-center justify-between p-5 bg-surface-900 border-b border-surface-700">
                <h2 class="text-xl font-bold text-surface-100">Share Your Result</h2>
                <button @click="openShareModal = false" class="text-surface-400 hover:text-surface-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    {{-- Card preview --}}
                    <div class="flex justify-center bg-surface-800 rounded-xl border border-surface-700 p-4">
                        {{-- ── SHARE CARD ── --}}
                        <div id="ielts-result-card"
                             style="width:100%;max-width:400px;aspect-ratio:4/5;background:linear-gradient(145deg,#0a0f1e 0%,#0f172a 50%,#0d1530 100%);color:white;display:flex;flex-direction:column;position:relative;overflow:hidden;font-family:system-ui,sans-serif;box-shadow:0 25px 50px rgba(0,0,0,0.8);">

                            {{-- Radial glow behind score --}}
                            <div style="position:absolute;top:30%;left:50%;transform:translate(-50%,-50%);width:220px;height:220px;background:radial-gradient(circle,rgba(245,158,11,0.12) 0%,transparent 70%);pointer-events:none;z-index:0;"></div>

                            {{-- Outer gold border --}}
                            <div style="position:absolute;inset:10px;border:1.5px solid rgba(245,158,11,0.5);pointer-events:none;z-index:2;"></div>
                            {{-- Inner gold border --}}
                            <div style="position:absolute;inset:14px;border:1px solid rgba(245,158,11,0.15);pointer-events:none;z-index:2;"></div>

                            {{-- Corner ornaments --}}
                            <div style="position:absolute;top:16px;left:16px;width:16px;height:16px;border-top:2px solid #f59e0b;border-left:2px solid #f59e0b;z-index:3;"></div>
                            <div style="position:absolute;top:16px;right:16px;width:16px;height:16px;border-top:2px solid #f59e0b;border-right:2px solid #f59e0b;z-index:3;"></div>
                            <div style="position:absolute;bottom:16px;left:16px;width:16px;height:16px;border-bottom:2px solid #f59e0b;border-left:2px solid #f59e0b;z-index:3;"></div>
                            <div style="position:absolute;bottom:16px;right:16px;width:16px;height:16px;border-bottom:2px solid #f59e0b;border-right:2px solid #f59e0b;z-index:3;"></div>

                            {{-- Header --}}
                            <div style="position:relative;z-index:4;padding:28px 28px 16px;text-align:center;border-bottom:1px solid rgba(245,158,11,0.2);">
                                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:4px;">
                                    <div style="width:26px;height:26px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#0a0f1e;font-size:11px;letter-spacing:0;">AI</div>
                                    <span style="font-size:14px;font-weight:800;letter-spacing:0.15em;color:#f5f5f5;">IELTS BAND AI</span>
                                </div>
                                <p style="font-size:8px;color:#9ca3af;letter-spacing:0.3em;text-transform:uppercase;margin:0;">Official Performance Certificate</p>
                            </div>

                            {{-- Band Score Seal --}}
                            <div style="position:relative;z-index:4;display:flex;flex-direction:column;align-items:center;padding:20px 0 14px;">
                                <p style="font-size:8px;color:#9ca3af;letter-spacing:0.25em;text-transform:uppercase;margin-bottom:12px;">Overall Band Score</p>
                                {{-- Outer ring --}}
                                <div style="position:relative;width:110px;height:110px;display:flex;align-items:center;justify-content:center;">
                                    <div style="position:absolute;inset:0;border-radius:50%;border:2px solid rgba(245,158,11,0.6);"></div>
                                    <div style="position:absolute;inset:6px;border-radius:50%;border:1px solid rgba(245,158,11,0.2);"></div>
                                    <div style="position:absolute;inset:12px;border-radius:50%;background:radial-gradient(circle,rgba(245,158,11,0.08),transparent);"></div>
                                    <div style="text-align:center;">
                                        <div style="font-size:42px;font-weight:900;color:#f59e0b;line-height:1;font-variant-numeric:tabular-nums;">{{ number_format($scores['overall_band'] ?? 0, 1) }}</div>
                                        <div style="font-size:8px;color:#9ca3af;letter-spacing:0.15em;text-transform:uppercase;">/ 9.0</div>
                                    </div>
                                </div>
                                <p style="font-size:9px;color:#d1d5db;letter-spacing:0.1em;margin-top:8px;text-transform:uppercase;">{{ $bandLabel }} Proficiency</p>
                            </div>

                            {{-- Diamond divider --}}
                            <div style="position:relative;z-index:4;display:flex;align-items:center;gap:10px;padding:0 32px;margin-bottom:14px;">
                                <div style="flex:1;height:1px;background:linear-gradient(to right,transparent,rgba(245,158,11,0.3));"></div>
                                <div style="width:6px;height:6px;background:#f59e0b;transform:rotate(45deg);"></div>
                                <div style="flex:1;height:1px;background:linear-gradient(to left,transparent,rgba(245,158,11,0.3));"></div>
                            </div>

                            {{-- Candidate --}}
                            <div style="position:relative;z-index:4;text-align:center;padding:0 28px 14px;">
                                <p style="font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.2em;margin-bottom:3px;">Candidate</p>
                                <p style="font-size:15px;font-weight:700;color:#f9fafb;margin-bottom:2px;">{{ Auth::user()->name }}</p>
                                <p style="font-size:9px;color:#9ca3af;">{{ now()->format('d F Y') }}</p>
                            </div>

                            {{-- 4 Criteria --}}
                            <div style="position:relative;z-index:4;display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:0 24px;margin-bottom:14px;">
                                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(245,158,11,0.15);border-radius:8px;padding:8px 10px;">
                                    <p style="font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:2px;">Task Response</p>
                                    <p style="font-size:18px;font-weight:800;color:#fbbf24;line-height:1;">{{ number_format($scores['task_achievement'] ?? 0, 1) }}</p>
                                </div>
                                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(52,211,153,0.2);border-radius:8px;padding:8px 10px;">
                                    <p style="font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:2px;">Coherence</p>
                                    <p style="font-size:18px;font-weight:800;color:#34d399;line-height:1;">{{ number_format($scores['coherence_cohesion'] ?? 0, 1) }}</p>
                                </div>
                                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(96,165,250,0.2);border-radius:8px;padding:8px 10px;">
                                    <p style="font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:2px;">Lexical Resource</p>
                                    <p style="font-size:18px;font-weight:800;color:#60a5fa;line-height:1;">{{ number_format($scores['lexical_resource'] ?? 0, 1) }}</p>
                                </div>
                                <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(167,139,250,0.2);border-radius:8px;padding:8px 10px;">
                                    <p style="font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:2px;">Grammar</p>
                                    <p style="font-size:18px;font-weight:800;color:#a78bfa;line-height:1;">{{ number_format($scores['grammar'] ?? 0, 1) }}</p>
                                </div>
                            </div>

                            {{-- Examiner quote --}}
                            <div style="position:relative;z-index:4;margin:0 24px;padding:10px 12px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.15);border-radius:8px;margin-bottom:14px;">
                                <p style="font-size:10px;color:#f59e0b;margin-bottom:3px;letter-spacing:0.1em;text-transform:uppercase;">Examiner Note</p>
                                <p style="font-size:10px;color:#d1d5db;font-style:italic;line-height:1.5;margin:0;">"{{ \Illuminate\Support\Str::limit($feedbackText ?? 'Evaluation complete.', 80) }}"</p>
                            </div>

                            {{-- Footer --}}
                            <div style="position:relative;z-index:4;margin-top:auto;padding:10px 24px 22px;border-top:1px solid rgba(245,158,11,0.15);display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <p style="font-size:8px;color:#6b7280;margin-bottom:1px;">Test ID: {{ $test->id ?? 'GEN' }}-{{ strtoupper(\Illuminate\Support\Str::random(6)) }}</p>
                                    <p style="font-size:9px;color:#f59e0b;font-weight:600;">ieltsbandai.com</p>
                                </div>
                                <div style="width:36px;height:36px;background:white;padding:3px;border-radius:4px;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url()->current() }}" alt="QR" style="width:100%;height:100%;display:block;">
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Controls --}}
                    <div>
                        <h3 class="text-base font-semibold text-surface-200 mb-4">Choose Action</h3>
                        <button @click="downloadImage()" :disabled="generating"
                            class="w-full flex items-center justify-center gap-3 bg-surface-700 hover:bg-surface-600 border border-surface-600 text-surface-100 py-3.5 rounded-xl font-semibold transition-all mb-6">
                            <svg x-show="!generating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <svg x-show="generating" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="generating ? 'Generating…' : 'Download Certificate Image'"></span>
                        </button>
                        <div class="space-y-3">
                            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider">Or Share Directly</p>
                            @php
                                $shareText = "I just scored Band " . number_format($scores['overall_band'] ?? 0, 1) . " on IELTS Band AI! Check out my result:";
                                $shareUrl = url()->current();
                            @endphp
                            <a href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank"
                               class="flex items-center gap-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                                <div class="w-10 h-10 bg-[#25D366] text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-semibold text-surface-200 text-sm">Share via WhatsApp</span>
                                    <span class="text-xs text-surface-500">Send to friends & family</span>
                                </div>
                            </a>
                            <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank"
                               class="flex items-center gap-4 p-4 rounded-xl bg-sky-500/10 border border-sky-500/20 hover:bg-sky-500/20 transition-colors">
                                <div class="w-10 h-10 bg-[#0088cc] text-white rounded-full flex items-center justify-center shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 11.944 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </div>
                                <div>
                                    <span class="block font-semibold text-surface-200 text-sm">Share via Telegram</span>
                                    <span class="text-xs text-surface-500">Post to channels or chats</span>
                                </div>
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ $shareUrl }}').then(()=>{ this.querySelector('span').textContent='Copied!'; setTimeout(()=>this.querySelector('span').textContent='Copy Link',2000) })"
                               class="w-full flex items-center gap-4 p-4 rounded-xl bg-surface-700/50 border border-surface-600 hover:bg-surface-700 transition-colors text-left">
                                <div class="w-10 h-10 bg-surface-600 text-surface-200 rounded-full flex items-center justify-center shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </div>
                                <div>
                                    <span class="block font-semibold text-surface-200 text-sm">Copy Link</span>
                                    <span class="text-xs text-surface-500">Share manually anywhere</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Ask the Examiner (AI Clarification) ── --}}
    <div class="mt-8 print:hidden" x-data="examinerChat()" id="examiner-chat-section">
        <div class="bg-surface-900 border border-surface-700 rounded-2xl overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-surface-800">
                <div class="w-8 h-8 rounded-full bg-brand-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-surface-100">Ask the Examiner</h3>
                    <p class="text-xs text-surface-500">Ask any follow-up question about your score or how to improve</p>
                </div>
            </div>

            {{-- Chat history --}}
            <div class="px-6 py-4 space-y-4 max-h-80 overflow-y-auto" id="chat-messages" x-ref="chatMessages">
                {{-- Starter chips --}}
                <template x-if="messages.length === 0">
                    <div class="space-y-2">
                        <p class="text-xs text-surface-500 mb-3">Suggested questions:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'Why was my vocabulary score low?',
                                'How can I improve my coherence?',
                                'What did I do well in task achievement?',
                                'Give me 3 specific tips to reach band 7',
                            ] as $chip)
                            <button @click="sendMessage('{{ $chip }}')"
                                class="text-xs px-3 py-1.5 rounded-full bg-surface-800 border border-surface-700 text-surface-300 hover:border-brand-500/50 hover:text-brand-400 transition-colors">
                                {{ $chip }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </template>

                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user'
                            ? 'bg-brand-500/20 border border-brand-500/30 text-surface-100 rounded-2xl rounded-tr-sm px-4 py-2.5 max-w-[80%] text-sm'
                            : 'bg-surface-800 border border-surface-700 text-surface-200 rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[85%] text-sm leading-relaxed'">
                            <p x-html="msg.content.replace(/\n/g, '<br>')"></p>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex justify-start">
                        <div class="bg-surface-800 border border-surface-700 rounded-2xl rounded-tl-sm px-4 py-3">
                            <div class="flex gap-1 items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-bounce" style="animation-delay:0ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-bounce" style="animation-delay:150ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-bounce" style="animation-delay:300ms"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Input --}}
            <div class="px-6 pb-4 pt-2 border-t border-surface-800">
                <form @submit.prevent="sendMessage(question)" class="flex gap-2">
                    <input x-model="question"
                        type="text"
                        placeholder="e.g. Why was my lexical resource marked 6?"
                        :disabled="loading"
                        class="flex-1 bg-surface-800 border border-surface-700 rounded-xl px-4 py-2.5 text-sm text-surface-100 placeholder-surface-500 focus:outline-none focus:border-brand-500 disabled:opacity-50">
                    <button type="submit" :disabled="loading || !question.trim()"
                        class="btn-primary text-sm px-4 disabled:opacity-50 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
                <p class="text-xs text-surface-600 mt-2">Powered by AI · Responses are context-aware based on your essay and scores</p>
            </div>
        </div>
    </div>

</div>
</div>

<script>
// Highlight error on click
document.querySelectorAll('#essayText .error').forEach(span => {
    span.addEventListener('click', () => {
        const id = span.getAttribute('data-error-id');
        // scroll to error row if it exists
        const row = document.querySelector(`[data-error-row="${id}"]`);
        if (row) { row.scrollIntoView({ behavior:'smooth', block:'center' }); row.classList.add('ring-2','ring-brand-500/50'); setTimeout(()=>row.classList.remove('ring-2','ring-brand-500/50'),2000); }
    });
});

// Band 9 lazy load
async function loadBand9() {
    const btn = document.getElementById('loadBand9Btn');
    const content = document.getElementById('band9Content');
    const loading = document.getElementById('band9Loading');
    const text    = document.getElementById('band9Text');

    btn.disabled = true;
    btn.textContent = 'Loading…';
    content.classList.remove('hidden');
    loading.classList.remove('hidden');
    text.classList.add('hidden');

    try {
        const res  = await fetch('{{ route('writing.band9', $test->id) }}');
        const data = await res.json();
        if (data.rewrite) {
            loading.classList.add('hidden');
            text.classList.remove('hidden');
            text.textContent = data.rewrite;
            btn.textContent = 'Regenerate';
            btn.disabled = false;
        } else throw new Error();
    } catch {
        loading.textContent = 'Could not generate. Try again.';
        btn.textContent = 'Retry';
        btn.disabled = false;
    }
}

// If band9 already pre-loaded (from $band_9_rewrite variable), hide the button
@if(!empty($band_9_rewrite))
document.getElementById('loadBand9Btn').style.display = 'none';
@endif

function examinerChat() {
    return {
        messages: [],
        question: '',
        loading: false,

        async sendMessage(text) {
            if (!text || !text.trim()) return;
            this.messages.push({ role: 'user', content: text.trim() });
            this.question = '';
            this.loading  = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('{{ route('writing.clarify', $test->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ question: text.trim() }),
                });

                const data = await res.json();
                this.messages.push({
                    role: 'examiner',
                    content: data.answer || 'Sorry, I could not generate a response. Please try again.',
                });
            } catch {
                this.messages.push({ role: 'examiner', content: 'Something went wrong. Please try again.' });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        scrollToBottom() {
            const el = this.$refs.chatMessages;
            if (el) el.scrollTop = el.scrollHeight;
        },
    };
}
</script>
{{-- ── Print footer (shown only in print) ── --}}
<div id="print-footer">
    <span>Test ID: {{ $test->id ?? 'N/A' }}</span>
    <span>Verified by IELTS Band AI · {{ now()->format('d M Y') }}</span>
    <span>{{ url('/') }}</span>
</div>

<style>
@media print {
    #print-report {
        display: block !important;
        visibility: visible !important;
        position: absolute !important;
        top: 0 !important; left: 0 !important;
        width: 100% !important;
        min-height: 100vh !important;
        background: white !important;
        padding: 3rem !important;
        z-index: 9999 !important;
    }
    .print\:hidden { display: none !important; }
    body, html { background: white !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    .break-inside-avoid { break-inside: avoid !important; page-break-inside: avoid !important; }
    .break-before-page { break-before: page !important; page-break-before: always !important; }
    #print-footer {
        display: flex !important;
        position: fixed !important;
        bottom: 0 !important; left: 0 !important; right: 0 !important;
        background: white !important;
        border-top: 1px solid #e5e7eb !important;
        padding: 1rem 3rem !important;
        justify-content: space-between !important;
        font-size: 0.75rem !important;
        color: #9ca3af !important;
        z-index: 10000 !important;
    }
    body { margin-bottom: 2cm !important; }
    #print-report { padding-bottom: 3rem !important; }
}
#print-footer { display: none; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function resultPageData() {
    return {
        openShareModal: false,
        generating: false,

        downloadImage() {
            this.generating = true;
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
</x-app-layout>
