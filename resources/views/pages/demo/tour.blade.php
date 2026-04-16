<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Institute Platform Demo — IELTS Band AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Tour screens ── */
        .tour-screen { display: none; }
        .tour-screen.active { display: block; }

        /* ── Highlight pulse ── */
        .tour-highlight {
            outline: 2px solid #4f46e5 !important;
            outline-offset: 6px;
            border-radius: 10px;
            animation: tourPulse 1.8s ease-in-out infinite;
        }
        @keyframes tourPulse {
            0%, 100% { outline-color: #4f46e5; box-shadow: 0 0 0 0 rgba(79,70,229,.25); }
            50%       { outline-color: #818cf8; box-shadow: 0 0 0 8px rgba(79,70,229,0); }
        }

        /* ── Simulated table ── */
        .sim-th {
            font-size: .7rem; color: #94a3b8; text-transform: uppercase;
            letter-spacing: .05em; font-weight: 600; padding-bottom: .6rem; text-align: left;
        }
        .sim-td {
            padding: .65rem 0; border-top: 1px solid #1e293b;
            font-size: .85rem; color: #e2e8f0;
        }
        .sim-td:first-child { padding-left: .5rem; }

        /* ── Admin nav item ── */
        .anav { font-size: .8rem; color: #64748b; cursor: default; transition: color .15s; }
        .anav:hover { color: #94a3b8; }
        .anav.on { color: #38bdf8; font-weight: 600; }

        /* ── Transition screen pipeline ── */
        .pipe-arrow { font-size: 1.4rem; color: #475569; }

        /* ── Scrollbar hide for locked demo ── */
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scroll::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="min-h-screen bg-surface-950 text-surface-200 font-sans antialiased">

{{-- Ambient glows --}}
<div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
    <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-brand-500/8 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-600/5 rounded-full blur-3xl"></div>
</div>

@php
    $calendlyUrl = 'https://calendly.com/nishantshekhawat2001';
    $demoEmail   = 'ieltsband25@gmail.com';
@endphp

{{-- Top banner --}}
<div class="bg-amber-500/10 border-b border-amber-500/30 px-4 py-2">
    <div class="max-w-5xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-xs text-amber-300">
            <span>🎓</span>
            <span><strong>Institute Demo</strong> — Interactive walkthrough of the full platform. No account needed.</span>
        </div>
        <a href="{{ $calendlyUrl }}" target="_blank"
           class="shrink-0 text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 px-3 py-1.5 rounded-lg transition-colors">
            Schedule Full Demo →
        </a>
    </div>
</div>

{{-- Header --}}
<header class="sticky top-0 z-40 bg-surface-900/90 backdrop-blur border-b border-surface-700/50">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
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
            <span class="hidden sm:inline text-xs text-surface-500" id="headerStepLabel">Step 1 of 7 — Admin Dashboard</span>
            <a href="{{ $calendlyUrl }}" target="_blank"
               class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                📅 Book Demo
            </a>
        </div>
    </div>
</header>

{{-- Errors --}}
@if(session('error'))
<div class="max-w-5xl mx-auto px-4 pt-4">
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     TOUR SCREENS
═══════════════════════════════════════════════════════════ --}}
<div class="max-w-5xl mx-auto px-4 pt-6 pb-32" id="tourContainer">

    {{-- ─────────────────────────────────────────────────────
         SCREEN 1 — Admin Dashboard
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen active" id="screen-1">

        {{-- Simulated admin nav --}}
        <div class="flex items-center justify-between bg-surface-800/60 border border-surface-700 rounded-xl px-5 py-3 mb-6">
            <div class="flex items-center gap-6">
                <span class="text-xs font-bold text-surface-100">Institute Admin</span>
                <div class="hidden sm:flex items-center gap-5">
                    <span class="anav on">Dashboard</span>
                    <span class="anav">Tests</span>
                    <span class="anav">Batches</span>
                    <span class="anav">Students</span>
                    <span class="anav">Reports</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">NS</div>
                <span class="text-xs text-surface-400 hidden sm:inline">Nishant Inst.</span>
            </div>
        </div>

        {{-- Headline --}}
        <div class="mb-5">
            <h1 class="text-xl font-bold text-surface-50">Dashboard Overview</h1>
            <p class="text-sm text-surface-400 mt-0.5">Welcome back. Here's what's happening across your institute today.</p>
        </div>

        {{-- Stats cards --}}
        <div id="dash-stats" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="card p-4">
                <div class="text-xs text-surface-400 uppercase tracking-wider mb-2">Active Students</div>
                <div class="text-3xl font-extrabold text-surface-50">340</div>
                <div class="text-xs text-emerald-400 mt-1">↑ 12 this month</div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-surface-400 uppercase tracking-wider mb-2">Tests Assigned</div>
                <div class="text-3xl font-extrabold text-surface-50">89</div>
                <div class="text-xs text-brand-400 mt-1">Across 5 batches</div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-surface-400 uppercase tracking-wider mb-2">Tests Taken</div>
                <div class="text-3xl font-extrabold text-surface-50">1,247</div>
                <div class="text-xs text-emerald-400 mt-1">↑ 47 today</div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-surface-400 uppercase tracking-wider mb-2">Avg Band Score</div>
                <div class="text-3xl font-extrabold text-amber-400">6.4</div>
                <div class="text-xs text-emerald-400 mt-1">↑ 0.3 from last month</div>
            </div>
        </div>

        {{-- Two column section --}}
        <div class="grid sm:grid-cols-3 gap-5">
            {{-- Recent Activity --}}
            <div class="sm:col-span-2 card p-5">
                <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Recent Activity</h2>
                <div class="space-y-3">
                    @foreach([
                        ['🟢', 'Priya Sharma scored Band 7.0 on Mock Test #12',      '2 min ago'],
                        ['🔵', 'Batch B: 18 students assigned to Reading Mock #3',   '15 min ago'],
                        ['🟡', 'Rahul Kumar improved +0.5 from last attempt',        '1 hr ago'],
                        ['🟢', 'Batch A monthly report auto-generated',              '3 hrs ago'],
                        ['🔵', 'Amandeep Singh completed Listening Module #5',      'Yesterday'],
                    ] as [$dot, $msg, $time])
                    <div class="flex items-start gap-3">
                        <span class="text-xs mt-0.5">{{ $dot }}</span>
                        <div class="flex-1">
                            <p class="text-sm text-surface-200">{{ $msg }}</p>
                        </div>
                        <span class="text-xs text-surface-600 shrink-0">{{ $time }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card p-5">
                <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    @foreach([
                        ['Create New Test',    'brand'],
                        ['Add Batch',         'purple'],
                        ['Assign Test',       'amber'],
                        ['View Reports',      'emerald'],
                        ['Manage Students',   'sky'],
                    ] as [$label, $color])
                    <button class="w-full text-left px-3 py-2.5 rounded-lg bg-surface-800 border border-surface-700 text-sm text-surface-300 hover:border-surface-500 transition-colors cursor-default">
                        + {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

    </div>{{-- /screen-1 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 2 — Test Management
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-2">

        {{-- Simulated admin nav --}}
        <div class="flex items-center justify-between bg-surface-800/60 border border-surface-700 rounded-xl px-5 py-3 mb-6">
            <div class="flex items-center gap-6">
                <span class="text-xs font-bold text-surface-100">Institute Admin</span>
                <div class="hidden sm:flex items-center gap-5">
                    <span class="anav">Dashboard</span>
                    <span class="anav on">Tests</span>
                    <span class="anav">Batches</span>
                    <span class="anav">Students</span>
                    <span class="anav">Reports</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">NS</div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-surface-50">Tests</h1>
                <p class="text-sm text-surface-400 mt-0.5">Create and manage IELTS mock tests for your batches.</p>
            </div>
            <button class="btn-primary text-sm cursor-default">+ Create New Test</button>
        </div>

        {{-- Filter tabs --}}
        <div class="flex items-center gap-2 mb-5">
            @foreach(['All Tests', 'Writing', 'Reading', 'Listening', 'Speaking', 'Full Mock'] as $tab)
            <span class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $loop->first ? 'bg-brand-500/20 text-brand-300 border border-brand-500/40' : 'bg-surface-800 text-surface-400 border border-surface-700' }} cursor-default">{{ $tab }}</span>
            @endforeach
        </div>

        {{-- Tests table --}}
        <div id="tests-table" class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-surface-800/50 px-5">
                    <tr class="px-5">
                        <th class="sim-th pl-5">Test Name</th>
                        <th class="sim-th hidden sm:table-cell">Sections</th>
                        <th class="sim-th hidden sm:table-cell">Batches</th>
                        <th class="sim-th">Attempts</th>
                        <th class="sim-th">Status</th>
                        <th class="sim-th"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['IELTS Mock Test #12',          'Writing + Reading', 'Batch A, Batch B', '40',  'active'],
                        ['Academic Task 2 Practice',     'Writing',           'Batch C',          '28',  'active'],
                        ['Full Listening Mock #3',       'Listening',         'All Batches',      '15',  'draft'],
                        ['Reading Comprehension Set 2',  'Reading',           'Batch B',          '33',  'active'],
                        ['Speaking Module — Part 1',     'Speaking',          'Batch A',          '8',   'draft'],
                    ] as [$name, $sections, $batches, $attempts, $status])
                    <tr>
                        <td class="sim-td pl-5 font-medium">{{ $name }}</td>
                        <td class="sim-td hidden sm:table-cell text-surface-400 text-xs">{{ $sections }}</td>
                        <td class="sim-td hidden sm:table-cell text-surface-400 text-xs">{{ $batches }}</td>
                        <td class="sim-td">{{ $attempts }}</td>
                        <td class="sim-td">
                            @if($status === 'active')
                            <span class="tag-green text-[10px]">Active</span>
                            @else
                            <span class="tag bg-surface-700 text-surface-400 border-surface-600 text-[10px]">Draft</span>
                            @endif
                        </td>
                        <td class="sim-td text-right pr-3">
                            <button class="text-xs text-brand-400 hover:text-brand-300 cursor-default">Manage</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- /screen-2 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 3 — Batch Management
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-3">

        {{-- Simulated admin nav --}}
        <div class="flex items-center justify-between bg-surface-800/60 border border-surface-700 rounded-xl px-5 py-3 mb-6">
            <div class="flex items-center gap-6">
                <span class="text-xs font-bold text-surface-100">Institute Admin</span>
                <div class="hidden sm:flex items-center gap-5">
                    <span class="anav">Dashboard</span>
                    <span class="anav">Tests</span>
                    <span class="anav on">Batches</span>
                    <span class="anav">Students</span>
                    <span class="anav">Reports</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">NS</div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-surface-50">Batches</h1>
                <p class="text-sm text-surface-400 mt-0.5">Group students into batches and manage training programs.</p>
            </div>
            <button class="btn-primary text-sm cursor-default">+ Create Batch</button>
        </div>

        {{-- Batch cards --}}
        <div id="batch-cards" style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:24px;">
            @php
            $batches = [
                ['Batch A','Beginner','22','5','5.8','🌱','#3b82f6','#1d3a6e','64'],
                ['Batch B','Advanced','18','8','7.1','🚀','#10b981','#0f3d2e','79'],
                ['Batch C','Weekend', '15','3','6.2','🌙','#8b5cf6','#2e1d5e','69'],
            ];
            @endphp
            @foreach($batches as [$bname,$blevel,$bstu,$btests,$bavg,$bicon,$bclr,$bclrbg,$bpct])
            <div style="background:#1e293b;border:1px solid #2d3f55;border-radius:14px;overflow:hidden;transition:box-shadow .2s;">
                {{-- Colored top stripe --}}
                <div style="height:4px;background:{{ $bclr }};opacity:.9;"></div>
                <div style="padding:20px;">
                    {{-- Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                        <div>
                            <div style="font-size:16px;font-weight:700;color:#f1f5f9;letter-spacing:-.01em;">{{ $bname }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:3px;">{{ $blevel }} group</div>
                        </div>
                        <div style="width:38px;height:38px;border-radius:10px;background:{{ $bclrbg }};display:flex;align-items:center;justify-content:center;font-size:18px;">{{ $bicon }}</div>
                    </div>
                    {{-- Divider --}}
                    <div style="height:1px;background:#2d3f55;margin-bottom:14px;"></div>
                    {{-- Stats row --}}
                    <div style="display:flex;margin-bottom:16px;">
                        <div style="flex:1;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#f1f5f9;line-height:1.1;">{{ $bstu }}</div>
                            <div style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Students</div>
                        </div>
                        <div style="width:1px;background:#2d3f55;"></div>
                        <div style="flex:1;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#f1f5f9;line-height:1.1;">{{ $btests }}</div>
                            <div style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Tests</div>
                        </div>
                        <div style="width:1px;background:#2d3f55;"></div>
                        <div style="flex:1;text-align:center;">
                            <div style="font-size:22px;font-weight:800;color:#f59e0b;line-height:1.1;">{{ $bavg }}</div>
                            <div style="font-size:9px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Avg Band</div>
                        </div>
                    </div>
                    {{-- Band progress bar --}}
                    <div style="margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;font-size:10px;color:#64748b;margin-bottom:5px;">
                            <span>Band progress</span><span>{{ $bavg }} / 9.0</span>
                        </div>
                        <div style="height:5px;background:#0f172a;border-radius:99px;overflow:hidden;">
                            <div style="height:5px;width:{{ $bpct }}%;background:{{ $bclr }};border-radius:99px;opacity:.85;"></div>
                        </div>
                    </div>
                    {{-- Button --}}
                    <button style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid #2d3f55;background:transparent;color:{{ $bclr }};font-size:12px;font-weight:600;cursor:default;transition:background .15s;">
                        View Batch Details →
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Students quick list --}}
        <div style="background:#1e293b;border:1px solid #2d3f55;border-radius:14px;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid #2d3f55;">
                <div>
                    <span style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Batch A — Students</span>
                </div>
                <button style="font-size:12px;color:#38bdf8;background:none;border:none;cursor:default;">View All 22 →</button>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#162032;">
                        <th style="text-align:left;padding:10px 20px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Student</th>
                        <th style="text-align:left;padding:10px 16px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Last Test</th>
                        <th style="text-align:center;padding:10px 16px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Tests</th>
                        <th style="text-align:center;padding:10px 20px 10px 0;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Band</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Priya Sharma',   'PS','#4f46e5','Mock Test #12','12','7.0','↑ +0.5','#10b981'],
                        ['Rahul Kumar',    'RK','#0891b2','Mock Test #11','9', '6.5','↑ +0.5','#10b981'],
                        ['Amandeep Singh', 'AS','#7c3aed','Task 2 Pract.','7', '5.5','↓ -0.5','#ef4444'],
                        ['Shreya Patel',   'SP','#be185d','Mock Test #12','10','6.0','→ same','#94a3b8'],
                    ] as [$sname,$sinit,$sclr,$slast,$stests,$sband,$strend,$strendclr])
                    <tr style="border-top:1px solid #1e293b;">
                        <td style="padding:11px 20px;display:flex;align-items:center;gap:10px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:{{ $sclr }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;">{{ $sinit }}</div>
                            <span style="font-size:13px;font-weight:500;color:#e2e8f0;">{{ $sname }}</span>
                        </td>
                        <td style="padding:11px 16px;font-size:12px;color:#64748b;">{{ $slast }}</td>
                        <td style="padding:11px 16px;font-size:13px;color:#94a3b8;text-align:center;">{{ $stests }}</td>
                        <td style="padding:11px 20px 11px 0;text-align:center;">
                            <span style="font-size:15px;font-weight:800;color:#f59e0b;">{{ $sband }}</span>
                            <span style="font-size:10px;font-weight:600;color:{{ $strendclr }};margin-left:5px;">{{ $strend }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- /screen-3 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 4 — Assign Test to Batch
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-4">

        {{-- Simulated admin nav --}}
        <div class="flex items-center justify-between bg-surface-800/60 border border-surface-700 rounded-xl px-5 py-3 mb-6">
            <div class="flex items-center gap-6">
                <span class="text-xs font-bold text-surface-100">Institute Admin</span>
                <div class="hidden sm:flex items-center gap-5">
                    <span class="anav">Dashboard</span>
                    <span class="anav on">Tests</span>
                    <span class="anav">Batches</span>
                    <span class="anav">Students</span>
                    <span class="anav">Reports</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">NS</div>
            </div>
        </div>

        <div class="mb-5">
            <h1 class="text-xl font-bold text-surface-50">Assign Test to Batch</h1>
            <p class="text-sm text-surface-400 mt-0.5">Schedule any test for a batch — students are notified automatically.</p>
        </div>

        <div class="grid sm:grid-cols-5 gap-5">

            {{-- Assignment form --}}
            <div id="assign-form" class="sm:col-span-2 card p-6 space-y-4">
                <div>
                    <label class="text-xs text-surface-400 uppercase tracking-wider block mb-1.5">Select Test</label>
                    <div class="flex items-center justify-between bg-surface-800 border border-surface-600 rounded-xl px-3 py-2.5 text-sm text-surface-200 cursor-default">
                        <span>IELTS Mock Test #12</span>
                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-surface-400 uppercase tracking-wider block mb-1.5">Assign to Batch</label>
                    <div class="flex items-center justify-between bg-surface-800 border border-surface-600 rounded-xl px-3 py-2.5 text-sm text-surface-200 cursor-default">
                        <span>Batch A — Beginner (22 students)</span>
                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-surface-400 uppercase tracking-wider block mb-1.5">Schedule</label>
                    <div class="flex items-center justify-between bg-surface-800 border border-surface-600 rounded-xl px-3 py-2.5 text-sm text-surface-200 cursor-default">
                        <span>Immediate (send now)</span>
                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="pt-1">
                    <div class="flex items-center gap-2 text-xs text-surface-400 mb-3">
                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        22 students will be notified
                    </div>
                    <button class="btn-primary w-full text-sm cursor-default">Assign Test →</button>
                </div>
            </div>

            {{-- Recent Assignments --}}
            <div class="sm:col-span-3 card p-5">
                <h2 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-4">Recent Assignments</h2>
                <table class="w-full">
                    <thead><tr>
                        <th class="sim-th">Test</th>
                        <th class="sim-th">Batch</th>
                        <th class="sim-th hidden sm:table-cell">Completion</th>
                        <th class="sim-th">Avg Band</th>
                    </tr></thead>
                    <tbody>
                        @foreach([
                            ['Mock Test #11',          'Batch B', '18/18', '7.0'],
                            ['Task 2 Practice',        'Batch C', '14/15', '6.2'],
                            ['Listening Mock #2',      'Batch A', '20/22', '6.5'],
                            ['Reading Set 1',          'Batch B', '17/18', '6.8'],
                            ['Speaking Module #2',     'Batch A', '22/22', '6.0'],
                        ] as [$test, $batch, $completion, $band])
                        <tr>
                            <td class="sim-td font-medium text-xs">{{ $test }}</td>
                            <td class="sim-td text-xs text-surface-400">{{ $batch }}</td>
                            <td class="sim-td hidden sm:table-cell text-xs text-surface-400">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-surface-700 rounded-full h-1.5 max-w-[60px]">
                                        @php $pct = explode('/', $completion); $w = $pct[0] / $pct[1] * 100; @endphp
                                        <div class="bg-brand-500 h-1.5 rounded-full" style="width:{{ $w }}%"></div>
                                    </div>
                                    <span>{{ $completion }}</span>
                                </div>
                            </td>
                            <td class="sim-td font-bold text-amber-400">{{ $band }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- /screen-4 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 5 — Results & Reports
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-5">

        {{-- Simulated admin nav --}}
        <div class="flex items-center justify-between bg-surface-800/60 border border-surface-700 rounded-xl px-5 py-3 mb-6">
            <div class="flex items-center gap-6">
                <span class="text-xs font-bold text-surface-100">Institute Admin</span>
                <div class="hidden sm:flex items-center gap-5">
                    <span class="anav">Dashboard</span>
                    <span class="anav">Tests</span>
                    <span class="anav">Batches</span>
                    <span class="anav">Students</span>
                    <span class="anav on">Reports</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300 text-[10px] font-bold">NS</div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-surface-50">Batch A — Results</h1>
                <p class="text-sm text-surface-400 mt-0.5">IELTS Mock Test #12 · Writing · Completed 2 days ago</p>
            </div>
            <button class="text-sm border border-surface-600 text-surface-300 px-4 py-2 rounded-xl hover:bg-surface-800 transition-colors cursor-default">
                Export CSV
            </button>
        </div>

        {{-- Summary stat cards --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
            @foreach([
                ['Total Students','22',  '#3b82f6','👥','↑ 0 absent'],
                ['Avg Band Score','6.2', '#f59e0b','📊','↑ +0.3 vs last'],
                ['Highest Band',  '7.5', '#10b981','🏆','Kiran Mehta'],
                ['Lowest Band',   '5.0', '#ef4444','⚠️','Deepak Verma'],
            ] as [$slabel,$sval,$sclr,$sicon,$ssub])
            <div style="background:#1e293b;border:1px solid #2d3f55;border-radius:12px;padding:16px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:{{ $sclr }};"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">{{ $slabel }}</span>
                    <span style="font-size:16px;">{{ $sicon }}</span>
                </div>
                <div style="font-size:28px;font-weight:800;color:{{ $sclr }};line-height:1;margin-bottom:5px;">{{ $sval }}</div>
                <div style="font-size:10px;color:#475569;">{{ $ssub }}</div>
            </div>
            @endforeach
        </div>

        {{-- Band distribution chart (two-column layout) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

            {{-- Bar chart --}}
            <div style="background:#1e293b;border:1px solid #2d3f55;border-radius:12px;padding:18px;">
                <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Band Distribution</div>
                <div style="display:flex;align-items:flex-end;gap:8px;height:90px;padding:0 4px;">
                    @foreach([
                        ['5.0',2,'#ef4444'],
                        ['5.5',4,'#f97316'],
                        ['6.0',6,'#f59e0b'],
                        ['6.5',5,'#3b82f6'],
                        ['7.0',3,'#10b981'],
                        ['7.5',2,'#34d399'],
                    ] as [$bband,$bcount,$bclr])
                    @php $bh = (int)(($bcount / 6) * 75); @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;">
                        <span style="font-size:10px;color:#64748b;font-weight:500;">{{ $bcount }}</span>
                        <div style="width:100%;height:{{ $bh }}px;background:{{ $bclr }};border-radius:4px 4px 0 0;opacity:.85;min-height:4px;"></div>
                        <span style="font-size:9px;color:#475569;">{{ $bband }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Module breakdown --}}
            <div style="background:#1e293b;border:1px solid #2d3f55;border-radius:12px;padding:18px;">
                <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Avg per Criterion</div>
                @foreach([
                    ['Task Achievement',  '6.3','#3b82f6',70],
                    ['Coherence',         '6.5','#8b5cf6',72],
                    ['Lexical Resource',  '6.0','#f59e0b',67],
                    ['Grammar Range',     '6.1','#10b981',68],
                ] as [$crit,$cval,$cclr,$cpct])
                <div style="margin-bottom:10px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
                        <span style="color:#94a3b8;">{{ $crit }}</span>
                        <span style="color:{{ $cclr }};font-weight:700;">{{ $cval }}</span>
                    </div>
                    <div style="height:4px;background:#0f172a;border-radius:99px;overflow:hidden;">
                        <div style="height:4px;width:{{ $cpct }}%;background:{{ $cclr }};border-radius:99px;"></div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>

        {{-- Student results table --}}
        <div id="results-table" style="background:#1e293b;border:1px solid #2d3f55;border-radius:12px;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #2d3f55;">
                <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Student Results</span>
                <span style="font-size:11px;color:#475569;">Showing 8 of 22</span>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#162032;">
                        <th style="text-align:left;padding:10px 20px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Student</th>
                        <th style="text-align:center;padding:10px 12px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Overall</th>
                        <th style="text-align:left;padding:10px 12px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Band Breakdown</th>
                        <th style="text-align:center;padding:10px 20px 10px 0;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $students5 = [
                        ['Priya Sharma',  'PS','#4f46e5','7.0','78','#10b981','done',  '↑ +0.5'],
                        ['Kiran Mehta',   'KM','#0891b2','7.5','83','#10b981','done',  '↑ +1.0'],
                        ['Rahul Kumar',   'RK','#7c3aed','6.5','72','#f59e0b','done',  '↑ +0.5'],
                        ['Neha Joshi',    'NJ','#be185d','6.5','72','#f59e0b','done',  '→ same'],
                        ['Shreya Patel',  'SP','#0d6e5f','6.0','67','#f59e0b','done',  '→ same'],
                        ['Amandeep S.',   'AS','#b45309','5.5','61','#f97316','done',  '↓ -0.5'],
                        ['Deepak Verma',  'DV','#991b1b','5.0','56','#ef4444','done',  '↓ -0.5'],
                        ['Arjun Nair',    'AN','#374151','—',  '0', '#475569','pending','—'],
                    ];
                    @endphp
                    @foreach($students5 as [$sn,$si,$sc,$so,$spct,$sbclr,$sstat,$strnd])
                    <tr style="border-top:1px solid #1a2535;">
                        <td style="padding:10px 20px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;border-radius:50%;background:{{ $sc }};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;">{{ $si }}</div>
                                <span style="font-size:13px;font-weight:500;color:#e2e8f0;">{{ $sn }}</span>
                            </div>
                        </td>
                        <td style="padding:10px 12px;text-align:center;">
                            <span style="font-size:16px;font-weight:800;color:{{ $sbclr }};">{{ $so }}</span>
                        </td>
                        <td style="padding:10px 12px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($so !== '—')
                                <div style="flex:1;max-width:100px;height:5px;background:#0f172a;border-radius:99px;overflow:hidden;">
                                    <div style="height:5px;width:{{ $spct }}%;background:{{ $sbclr }};border-radius:99px;"></div>
                                </div>
                                @endif
                                <span style="font-size:10px;font-weight:600;color:{{ $so !== '—' ? ($strnd[0] === '↑' ? '#10b981' : ($strnd[0] === '↓' ? '#ef4444' : '#94a3b8')) : '#475569' }};">{{ $strnd }}</span>
                            </div>
                        </td>
                        <td style="padding:10px 20px 10px 0;text-align:center;">
                            @if($sstat === 'done')
                            <span style="font-size:10px;font-weight:600;color:#10b981;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);padding:2px 8px;border-radius:20px;">Done</span>
                            @else
                            <span style="font-size:10px;font-weight:600;color:#f59e0b;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);padding:2px 8px;border-radius:20px;">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- /screen-5 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 6 — Transition: AI Writing Evaluation
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-6">

        <div class="min-h-[60vh] flex items-center justify-center py-12">
            <div id="transition-card" class="max-w-2xl w-full">

                {{-- Headline --}}
                <div class="text-center mb-10">
                    <div class="inline-flex items-center gap-2 bg-brand-500/10 border border-brand-500/20 px-4 py-1.5 rounded-full mb-5">
                        <div class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse"></div>
                        <span class="text-xs text-brand-300 font-medium uppercase tracking-wider">The Core Feature</span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-surface-50 mb-3 leading-tight">
                        How AI Evaluates<br class="hidden sm:block">Student Writing
                    </h2>
                    <p class="text-surface-400 max-w-lg mx-auto leading-relaxed">
                        Every time a student submits an essay, our AI scores it instantly — across all 4 IELTS criteria — with the same accuracy as a trained examiner.
                    </p>
                </div>

                {{-- Pipeline visual --}}
                <div class="flex items-center justify-center gap-3 sm:gap-4 mb-10 flex-wrap">
                    @foreach([
                        ['📝', 'Student submits essay'],
                        ['→',  null],
                        ['🤖', 'GPT-4 evaluates it'],
                        ['→',  null],
                        ['📊', 'Band score + feedback'],
                    ] as [$icon, $label])
                    @if($label === null)
                    <span class="pipe-arrow hidden sm:block">→</span>
                    @else
                    <div class="text-center">
                        <div class="w-14 h-14 rounded-2xl bg-surface-800 border border-surface-700 flex items-center justify-center text-2xl mb-2 mx-auto">{{ $icon }}</div>
                        <p class="text-[10px] text-surface-400 max-w-[80px] leading-tight text-center">{{ $label }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>

                {{-- Feature bullets --}}
                <div class="grid sm:grid-cols-2 gap-3 mb-8">
                    @foreach([
                        ['Band 0–9 score across all 4 criteria',       'brand'],
                        ['Highlighted grammar & vocabulary errors',     'amber'],
                        ['Examiner-style written comments',             'purple'],
                        ['Improvement suggestions & topic vocabulary',  'emerald'],
                    ] as [$feat, $color])
                    <div class="flex items-center gap-3 bg-surface-800/50 border border-surface-700 rounded-xl px-4 py-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-400 shrink-0"></div>
                        <span class="text-sm text-surface-300">{{ $feat }}</span>
                    </div>
                    @endforeach
                </div>

                <p class="text-center text-surface-500 text-sm">
                    Click <strong class="text-surface-300">Next</strong> to try it live with a real essay.
                </p>

            </div>
        </div>

    </div>{{-- /screen-6 --}}


    {{-- ─────────────────────────────────────────────────────
         SCREEN 7 — Interactive Writing Evaluation
    ───────────────────────────────────────────────────── --}}
    <div class="tour-screen" id="screen-7">

        <div class="mb-7 text-center">
            <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 px-4 py-1.5 rounded-full mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs text-emerald-300 font-medium uppercase tracking-wider">Live — Try It Now</span>
            </div>
            <h2 class="text-2xl font-bold text-surface-50 mb-2">Submit a Real Essay</h2>
            <p class="text-surface-400 text-sm max-w-md mx-auto">
                Write or paste an IELTS essay below. The same GPT-4 engine that powers the full platform will score it across all 4 criteria.
            </p>
        </div>

        {{-- Mode Selector --}}
        <div class="mb-6 max-w-3xl mx-auto">
            <div class="flex items-center gap-1 bg-surface-800 border border-surface-700 p-1 rounded-xl w-fit">
                <button type="button" id="modePracticeBtn" onclick="setDemoMode('practice')"
                    class="mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-surface-700 text-surface-50 border border-surface-500">
                    📝 Practice Mode
                </button>
                <button type="button" id="modeExamBtn" onclick="setDemoMode('exam')"
                    class="mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200">
                    🎓 Exam Simulation
                </button>
            </div>
            <div id="practiceDesc" class="mt-2 text-xs text-surface-500">
                Dark practice interface with detailed AI feedback. Great for everyday practice.
            </div>
            <div id="examDesc" class="mt-2 text-xs text-surface-500 hidden">
                White IELTS computer-based test UI. Identical to real exam conditions.
            </div>
        </div>

        @if($used ?? false)

        {{-- ── Locked Gate ── --}}
        <div class="card p-8 text-center border border-amber-500/25 bg-gradient-to-br from-amber-500/5 to-surface-900 max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/15 flex items-center justify-center mx-auto mb-4 text-3xl">🔒</div>
            <h3 class="text-xl font-bold text-surface-50 mb-2">You've Already Used the Demo</h3>
            <p class="text-surface-400 text-sm mb-6 max-w-md mx-auto leading-relaxed">
                The free demo allows one essay submission per visitor. Schedule a demo call to see the full platform and get access for your entire institute.
            </p>
            <a href="{{ $calendlyUrl }}" target="_blank"
               class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-black font-bold px-6 py-3 rounded-xl text-sm transition-colors shadow-lg mb-3">
                📅 Schedule Full Demo for My Institute
            </a>
            <p class="text-surface-600 text-xs">
                Individual student?
                <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-semibold">Create a free account</a>
                for unlimited practice.
            </p>
        </div>

        @else

        <form method="POST" action="{{ route('demo.submit') }}" id="demoForm">
            @csrf
            <input type="hidden" name="demo_mode" id="demoModeInput" value="practice">

            {{-- Form wrapper — restyled by setDemoMode() --}}
            <div id="formWrapper" class="max-w-3xl mx-auto" style="transition:background .2s;">

                {{-- Exam UI: header bar (hidden in practice) --}}
                <div id="examUiHeader" style="display:none;background:#003087;height:50px;align-items:center;padding:0 20px;font-family:Arial,sans-serif;border-radius:6px 6px 0 0;margin-bottom:0;">
                    <span style="font-size:11px;font-weight:bold;color:#fff;letter-spacing:.06em;text-transform:uppercase;min-width:160px;">IELTS Band AI</span>
                    <span style="flex:1;text-align:center;font-size:14px;font-weight:bold;color:#fff;">Academic Writing Test — Task 2</span>
                    <span style="min-width:160px;text-align:right;font-size:12px;color:rgba(255,255,255,0.7);">Demo Mode</span>
                </div>

                {{-- Exam UI: toolbar (hidden in practice) --}}
                <div id="examUiToolbar" style="display:none;background:#F0F2F7;border-bottom:1px solid #D0D3DC;padding:7px 20px;font-family:Arial,sans-serif;align-items:center;gap:16px;">
                    <span style="font-size:12px;color:#333;">Minimum words: <strong>250</strong></span>
                    <span style="width:1px;height:14px;background:#C0C3CC;display:inline-block;"></span>
                    <span style="font-size:12px;color:#555;">Word count: <strong id="wordCountExam">0</strong></span>
                    <span style="width:1px;height:14px;background:#C0C3CC;display:inline-block;"></span>
                    <span style="font-size:12px;color:#777;">⏱ 40:00 remaining (demo)</span>
                </div>

                {{-- Question Card --}}
                <div id="questionCard" class="card p-6 mb-5">
                    {{-- Practice label --}}
                    <div id="practiceLabelRow" class="flex items-start gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-surface-500 uppercase tracking-wider">IELTS Writing Task 2 — Academic</span>
                                <span class="tag-cyan text-[10px]">Demo Question</span>
                            </div>
                            <p class="text-surface-100 font-medium leading-snug">{{ $question->title }}</p>
                        </div>
                    </div>
                    {{-- Exam label (hidden in practice) --}}
                    <div id="examLabelRow" style="display:none;margin-bottom:12px;">
                        <div style="font-size:11px;font-weight:bold;color:#003087;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Writing Task 2</div>
                        <p style="font-size:15px;font-weight:bold;color:#1a1a1a;line-height:1.4;">{{ $question->title }}</p>
                    </div>
                    <div id="questionInstruction" class="bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-xs text-surface-400">
                        Write <strong class="text-surface-300">at least 250 words</strong> in about 40 minutes. Address all parts of the question.
                    </div>
                </div>

                {{-- Editor --}}
                <div id="editorCard" class="card p-5 mb-5">
                    <div id="practiceEditorHeader" class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-surface-400 uppercase tracking-wider">Your Essay</span>
                        <div class="flex items-center gap-3 text-xs">
                            <span id="wordCount" class="text-surface-400">0 words</span>
                            <span id="wordCountStatus" class="tag bg-surface-700 text-surface-500 border-surface-600">need 250+</span>
                        </div>
                    </div>
                    <div id="examEditorHeader" style="display:none;padding:10px 0 8px;border-bottom:1px solid #E0E2EE;margin-bottom:8px;">
                        <span style="font-size:12px;color:#555;">Type your answer below</span>
                    </div>
                    <textarea
                        name="answer"
                        id="essayTextarea"
                        rows="16"
                        placeholder="Start writing your essay here…&#10;&#10;Remember to:&#10;• Discuss both views clearly&#10;• Give and support your own opinion&#10;• Use varied vocabulary and grammar&#10;• Aim for 250–350 words"
                        class="w-full bg-surface-900 border border-surface-700 rounded-xl px-4 py-3.5 text-surface-100 text-sm leading-relaxed focus:outline-none focus:border-brand-500 resize-none transition-colors placeholder:text-surface-600"
                        oninput="updateWordCount(this)"
                        required
                    >{{ old('answer') }}</textarea>
                    @error('answer')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit row --}}
                <div id="submitRow" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <p id="practiceSubmitHint" class="text-xs text-surface-500">
                        Uses the same GPT-4 engine as the full product. Results appear in ~15 seconds.
                    </p>
                    <p id="examWordBar" style="display:none;font-size:12px;color:#555;">
                        <span id="wordCountBarExam">0</span> words
                    </p>
                    <button type="submit" id="submitBtn"
                        class="shrink-0 inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-semibold px-6 py-3 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span id="submitText">Score My Essay</span>
                    </button>
                </div>

            </div>{{-- /formWrapper --}}
        </form>

        @endif

        {{-- Scroll spacer so the submit button clears the taskbar --}}
        <div style="height:120px;"></div>

    </div>{{-- /screen-7 --}}

</div>{{-- /tourContainer --}}


{{-- ═══════════════════════════════════════════════════════════
     TOUR GUIDE CARD (fixed bottom — compact horizontal bar)
═══════════════════════════════════════════════════════════ --}}
<div id="tourGuide" style="position:fixed;bottom:16px;left:0;right:0;display:flex;justify-content:center;padding:0 16px;z-index:9999;">
    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.45);overflow:hidden;width:100%;max-width:680px;">

        {{-- Progress bar --}}
        <div style="height:3px;background:#f3f4f6;">
            <div id="tourProgress" style="height:3px;width:14.28%;background:#4f46e5;transition:width .5s ease;"></div>
        </div>

        {{-- Main row --}}
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;">

            {{-- Step badge --}}
            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <div style="width:30px;height:30px;border-radius:50%;background:#4f46e5;display:flex;align-items:center;justify-content:center;">
                    <span id="tourStepNum" style="color:#fff;font-size:12px;font-weight:700;">1</span>
                </div>
                <span style="font-size:11px;color:#9ca3af;white-space:nowrap;">/ 7</span>
            </div>

            {{-- Divider --}}
            <div style="width:1px;height:30px;background:#e5e7eb;flex-shrink:0;"></div>

            {{-- Text content --}}
            <div style="flex:1;min-width:0;">
                <p id="tourTitle" style="font-size:13px;font-weight:700;color:#111827;margin:0 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Admin Dashboard</p>
                <p id="tourDesc" style="font-size:11px;color:#6b7280;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    The institute dashboard gives you a real-time overview of all students, tests, and performance metrics.
                </p>
            </div>

            {{-- Next button --}}
            <div id="tourNextRow" style="flex-shrink:0;">
                <button id="tourNext" onclick="tourNext()"
                    style="background:#4f46e5;color:#fff;font-size:13px;font-weight:700;padding:9px 20px;border-radius:10px;border:none;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;"
                    onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                    Next
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            {{-- Step 7 hint (replaces Next button) --}}
            <div id="tourStep7Hint" style="display:none;flex-shrink:0;">
                <p style="font-size:11px;color:#6b7280;text-align:right;line-height:1.5;margin:0;">Write essay above &amp; click<br><strong style="color:#374151;">Score My Essay</strong></p>
            </div>

        </div>

        {{-- Progress dots --}}
        <div style="display:flex;justify-content:center;gap:6px;padding:4px 0 10px;">
            @for($i = 0; $i < 7; $i++)
            <div class="step-dot" data-dot="{{ $i }}"
                style="width:6px;height:6px;border-radius:50%;background:{{ $i === 0 ? '#4f46e5' : '#e5e7eb' }};transition:background .3s;"></div>
            @endfor
        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     LOADING OVERLAY (essay scoring)
═══════════════════════════════════════════════════════════ --}}
<div id="loadingOverlay" class="fixed inset-0 z-[70] bg-surface-950/97 backdrop-blur-sm hidden flex items-center justify-center">
    <div class="text-center px-6 max-w-lg mx-auto">
        <div class="w-20 h-20 rounded-full bg-brand-500/15 border border-brand-500/30 flex items-center justify-center mx-auto mb-7">
            <svg class="w-10 h-10 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-surface-50 mb-1">Scoring your essay…</h3>
        <p class="text-surface-500 text-xs mb-8">This takes about 10–20 seconds</p>
        <div id="featureSlider" class="relative h-28">
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700" data-index="0">
                <div class="text-2xl mb-2">✍️</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">GPT-4 is reading your writing</p>
                <p class="text-surface-500 text-xs leading-relaxed">Applying official IELTS band descriptors across Task Achievement, Coherence, Lexical Resource, and Grammar.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="1">
                <div class="text-2xl mb-2">🏫</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">Built for IELTS coaching institutes</p>
                <p class="text-surface-500 text-xs leading-relaxed">Manage students, create batches, assign tests, and track every student's band progress — all from one dashboard.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="2">
                <div class="text-2xl mb-2">📊</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">Detailed results for every student</p>
                <p class="text-surface-500 text-xs leading-relaxed">Error highlights, examiner comments, vocabulary suggestions, and improvement tips — all in one AI report.</p>
            </div>
            <div class="feature-slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 opacity-0" data-index="3">
                <div class="text-2xl mb-2">🎓</div>
                <p class="text-brand-300 font-semibold text-sm mb-1">Exam Simulation Mode available</p>
                <p class="text-surface-500 text-xs leading-relaxed">Pixel-accurate IELTS computer-based test UI — fullscreen, anti-cheat, strict timer. Identical to the real exam.</p>
            </div>
        </div>
        <div class="flex justify-center gap-2 mt-4">
            <div class="dot w-1.5 h-1.5 rounded-full bg-brand-400" data-dot="0"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="1"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="2"></div>
            <div class="dot w-1.5 h-1.5 rounded-full bg-surface-600" data-dot="3"></div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ --}}
<script>
// ── Tour step definitions ─────────────────────────────────
const TOUR_STEPS = [
    {
        screen:    1,
        highlight: 'dash-stats',
        title:     'Admin Dashboard',
        desc:      'The institute dashboard gives you a real-time overview: active students, tests assigned, tests taken, and average band score across all batches.',
        label:     'Dashboard',
    },
    {
        screen:    2,
        highlight: 'tests-table',
        title:     'Test Management',
        desc:      'Create custom IELTS mock tests. Choose which modules to include — Writing, Reading, Listening, or Speaking — and publish them to specific batches.',
        label:     'Tests',
    },
    {
        screen:    3,
        highlight: 'batch-cards',
        title:     'Batch Management',
        desc:      'Organise students into batches — beginner, advanced, weekend groups. Each batch has its own tests, progress tracking, and reporting.',
        label:     'Batches',
    },
    {
        screen:    4,
        highlight: 'assign-form',
        title:     'Assign Tests to Batches',
        desc:      'Select any test and assign it to one or more batches instantly. Students receive automatic notifications and the test appears in their dashboard.',
        label:     'Assign',
    },
    {
        screen:    5,
        highlight: 'results-table',
        title:     'Results & Analytics',
        desc:      'Track every student\'s performance. See band scores across all 4 IELTS criteria, identify weak students, and monitor batch-level progress over time.',
        label:     'Reports',
    },
    {
        screen:    6,
        highlight: 'transition-card',
        title:     'AI Writing Evaluation',
        desc:      'Now let\'s see the feature that powers it all — how the AI automatically evaluates student writing with examiner-level accuracy.',
        label:     'AI Eval',
        nextLabel: 'Try It Live →',
    },
    {
        screen:    7,
        highlight: 'demo-form-area',
        title:     'Your Turn — Try It Live',
        desc:      'Write an IELTS essay in the box above and click "Score My Essay". The AI will evaluate it in about 15 seconds.',
        label:     'Write',
        noNext:    true,
    },
];

let currentStep = 0;

function applyHighlight(id) {
    document.querySelectorAll('.tour-highlight').forEach(function(el) {
        el.classList.remove('tour-highlight');
    });
    if (id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('tour-highlight');
    }
}

function renderStep(idx) {
    var step  = TOUR_STEPS[idx];
    var total = TOUR_STEPS.length;

    // Show correct screen
    document.querySelectorAll('.tour-screen').forEach(function(s) {
        s.classList.remove('active');
    });
    document.getElementById('screen-' + step.screen).classList.add('active');

    // Update header label
    var hl = document.getElementById('headerStepLabel');
    if (hl) hl.textContent = 'Step ' + (idx + 1) + ' of ' + total + ' — ' + step.label;

    // Tour card — text content
    document.getElementById('tourStepNum').textContent  = idx + 1;
    document.getElementById('tourTitle').textContent    = step.title;
    document.getElementById('tourDesc').textContent     = step.desc;
    document.getElementById('tourProgress').style.width = (((idx + 1) / total) * 100) + '%';

    // Dot indicators (only the dots inside #tourGuide)
    document.querySelectorAll('#tourGuide .step-dot').forEach(function(dot, i) {
        dot.style.backgroundColor = i === idx ? '#4f46e5' : '#e5e7eb';
    });

    var nextRow = document.getElementById('tourNextRow');
    var hint    = document.getElementById('tourStep7Hint');
    var nextBtn = document.getElementById('tourNext');

    var guide = document.getElementById('tourGuide');

    if (step.noNext) {
        // Step 7: hide the entire tour card so it never blocks the submit button
        if (guide) guide.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        if (guide) guide.style.display = 'flex';
        nextRow.style.display = 'block';
        hint.style.display    = 'none';
        var label = step.nextLabel || 'Next';
        nextBtn.innerHTML = label +
            ' <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-left:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    applyHighlight(step.highlight);
    currentStep = idx;
}

function tourNext() {
    if (currentStep < TOUR_STEPS.length - 1) {
        renderStep(currentStep + 1);
    }
}

// Init
renderStep(0);

// ── Exam / Practice mode toggle ───────────────────────────
function setDemoMode(mode) {
    var isExam = mode === 'exam';

    // Buttons
    document.getElementById('modePracticeBtn').className = isExam
        ? 'mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200'
        : 'mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-surface-700 text-surface-50 border border-surface-500';
    document.getElementById('modeExamBtn').className = isExam
        ? 'mode-btn active px-5 py-2 text-sm font-semibold rounded-lg transition-all bg-[#003087] text-white border border-[#003087]'
        : 'mode-btn px-5 py-2 text-sm font-semibold rounded-lg transition-all text-surface-400 hover:text-surface-200';

    // Descriptions
    document.getElementById('practiceDesc').classList.toggle('hidden', isExam);
    document.getElementById('examDesc').classList.toggle('hidden', !isExam);

    // Hidden input
    document.getElementById('demoModeInput').value = mode;

    // Form wrapper
    var fw = document.getElementById('formWrapper');
    if (isExam) {
        fw.style.cssText = 'background:#fff;border:1px solid #D0D3DC;border-radius:6px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;max-width:768px;margin:0 auto;transition:background .2s;';
    } else {
        fw.style.cssText = 'transition:background .2s;max-width:768px;margin:0 auto;';
    }

    // Exam header / toolbar
    var eHeader  = document.getElementById('examUiHeader');
    var eToolbar = document.getElementById('examUiToolbar');
    eHeader.style.display  = isExam ? 'flex' : 'none';
    eToolbar.style.display = isExam ? 'flex' : 'none';

    // Question card
    var qCard = document.getElementById('questionCard');
    if (isExam) {
        qCard.style.cssText = 'background:#F5F6FA;border:none;border-bottom:1px solid #D0D3DC;border-radius:0;margin:0;padding:20px 24px;';
    } else {
        qCard.style.cssText = '';
        qCard.className = 'card p-6 mb-5';
    }
    document.getElementById('practiceLabelRow').style.display = isExam ? 'none' : '';
    document.getElementById('examLabelRow').style.display     = isExam ? 'block' : 'none';
    var qInstr = document.getElementById('questionInstruction');
    if (isExam) {
        qInstr.style.cssText = 'background:#EEF0F8;border:none;border-left:3px solid #003087;border-radius:0;padding:10px 14px;font-size:12px;color:#333;';
        qInstr.innerHTML = 'Write <strong>at least 250 words</strong> in about 40 minutes. Address all parts of the question.';
    } else {
        qInstr.style.cssText = '';
        qInstr.className = 'bg-surface-900 border border-surface-700 rounded-xl px-4 py-3 text-xs text-surface-400';
        qInstr.innerHTML = 'Write <strong class="text-surface-300">at least 250 words</strong> in about 40 minutes. Address all parts of the question.';
    }

    // Editor card
    var eCard = document.getElementById('editorCard');
    if (isExam) {
        eCard.style.cssText = 'background:#FAFBFE;border:none;border-radius:0;margin:0;padding:0;';
    } else {
        eCard.style.cssText = '';
        eCard.className = 'card p-5 mb-5';
    }
    document.getElementById('practiceEditorHeader').style.display = isExam ? 'none' : '';
    document.getElementById('examEditorHeader').style.display     = isExam ? 'block' : 'none';

    // Textarea
    var ta = document.getElementById('essayTextarea');
    if (isExam) {
        ta.style.cssText = 'width:100%;background:#FAFBFE;border:none;border-bottom:1px solid #E0E2EE;border-radius:0;padding:16px 20px;color:#1a1a1a;font-size:14px;font-family:Arial,sans-serif;line-height:1.75;resize:none;outline:none;box-sizing:border-box;';
    } else {
        ta.style.cssText = '';
        ta.className = 'w-full bg-surface-900 border border-surface-700 rounded-xl px-4 py-3.5 text-surface-100 text-sm leading-relaxed focus:outline-none focus:border-brand-500 resize-none transition-colors placeholder:text-surface-600';
    }

    // Submit row
    var sr  = document.getElementById('submitRow');
    var btn = document.getElementById('submitBtn');
    if (isExam) {
        sr.style.cssText  = 'background:#F0F2F7;border-top:1px solid #D0D3DC;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;';
        btn.style.cssText = 'background:#003087;color:#fff;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;padding:8px 22px;border:none;border-radius:2px;cursor:pointer;';
        btn.className = '';
    } else {
        sr.style.cssText  = '';
        sr.className  = 'flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4';
        btn.style.cssText = '';
        btn.className = 'shrink-0 inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-semibold px-6 py-3 rounded-xl shadow-glow transition-all disabled:opacity-50 disabled:cursor-not-allowed';
    }
    document.getElementById('practiceSubmitHint').style.display = isExam ? 'none' : '';
    document.getElementById('examWordBar').style.display        = isExam ? 'block' : 'none';
    document.getElementById('submitText').textContent           = isExam ? 'Submit Writing →' : 'Score My Essay';
}

// ── Word count ────────────────────────────────────────────
function updateWordCount(textarea) {
    const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
    // Sync exam count displays
    var examEl = document.getElementById('wordCountExam');
    var barEl  = document.getElementById('wordCountBarExam');
    if (examEl) examEl.textContent = words;
    if (barEl)  barEl.textContent  = words;
    const el     = document.getElementById('wordCount');
    const status = document.getElementById('wordCountStatus');
    if (!el) return;
    el.textContent = words + ' word' + (words !== 1 ? 's' : '');
    if (words >= 250) {
        el.className = 'text-emerald-400 text-xs';
        status.className = 'tag tag-green text-[10px]';
        status.textContent = 'ready';
    } else if (words >= 150) {
        el.className = 'text-amber-400 text-xs';
        status.className = 'tag bg-amber-500/15 text-amber-400 border-amber-500/30 text-[10px]';
        status.textContent = 'need ' + (250 - words) + ' more';
    } else {
        el.className = 'text-surface-400 text-xs';
        status.className = 'tag bg-surface-700 text-surface-500 border-surface-600 text-[10px]';
        status.textContent = 'need 250+';
    }
}

// ── Loading slider ────────────────────────────────────────
let sliderIndex    = 0;
let sliderInterval = null;

function startSlider() {
    const slides = document.querySelectorAll('#loadingOverlay .feature-slide');
    const dots   = document.querySelectorAll('#loadingOverlay .dot');
    if (!slides.length) return;
    sliderInterval = setInterval(function () {
        slides[sliderIndex].style.opacity      = '0';
        slides[sliderIndex].style.pointerEvents = 'none';
        dots[sliderIndex].classList.replace('bg-brand-400', 'bg-surface-600');
        sliderIndex = (sliderIndex + 1) % slides.length;
        slides[sliderIndex].style.opacity      = '1';
        slides[sliderIndex].style.pointerEvents = '';
        dots[sliderIndex].classList.replace('bg-surface-600', 'bg-brand-400');
    }, 3500);
}

// ── Form submit ───────────────────────────────────────────
const demoForm = document.getElementById('demoForm');
if (demoForm) {
    demoForm.addEventListener('submit', function (e) {
        const textarea = document.getElementById('essayTextarea');
        const words = textarea.value.trim().split(/\s+/).length;
        if (words < 50) {
            e.preventDefault();
            textarea.focus();
            return;
        }
        document.getElementById('loadingOverlay').classList.remove('hidden');
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitText').textContent = 'Scoring…';
        startSlider();
    });
}
</script>

</body>
</html>
