<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-8">
<div class="max-w-xl w-full">

    @php
    $configs = [
        'listening' => [
            'icon'  => '🎧',
            'color' => 'amber',
            'time'  => '40 minutes (30 listen + 10 transfer)',
            'desc'  => '4 sections · 40 questions. Audio plays once per section.',
            'route' => 'listening.start',
            'rules' => [
                '<strong>4 sections, 40 questions.</strong> Difficulty increases each section.',
                '<strong>Audio plays ONCE per section</strong> — no pause, seek or replay.',
                'Write answers as you listen. Use the final 10 minutes to check spelling.',
                'Spelling counts — answers must match exactly.',
            ],
        ],
        'reading'   => [
            'icon'  => '📖',
            'color' => 'rose',
            'time'  => '60 minutes (no transfer time)',
            'desc'  => '3 passages · 40 questions. Aim for ~20 min per passage.',
            'route' => 'reading.start',
            'rules' => [
                '<strong>3 passages, 40 questions.</strong> Pace yourself at ~20 min per passage.',
                '<strong>All answers come from the passage</strong> — no external knowledge.',
                'Question types: T/F/Not Given, Y/N/Not Given, MCQ, matching headings, short answer.',
                '<strong>Spelling counts.</strong> Copy words exactly as they appear.',
            ],
        ],
        'writing'   => [
            'icon'  => '✍️',
            'color' => 'purple',
            'time'  => '60 minutes — split how you like',
            'desc'  => 'Task 1 (rec. 20 min) + Task 2 (rec. 40 min).',
            'route' => 'writing.start',
            'rules' => [
                '<strong>Task 1:</strong> 150 words minimum (graph/chart/letter description).',
                '<strong>Task 2:</strong> 250 words minimum (opinion/discussion essay).',
                '<strong>Under-length responses cap Task Achievement at Band 5.</strong>',
                'Plan before you write. Use formal academic English.',
            ],
        ],
        'speaking'  => [
            'icon'  => '🎤',
            'color' => 'brand',
            'time'  => '~7 minutes (3 recorded parts)',
            'desc'  => 'Parts 1, 2 & 3. Microphone access required.',
            'route' => 'speaking.test',
            'rules' => [
                '<strong>3 parts.</strong> Allow microphone access when your browser asks.',
                '<strong>Part 1:</strong> 5 short questions (30s each).',
                '<strong>Part 2:</strong> 1-min prep, then 2-min monologue from a cue card.',
                '<strong>Part 3:</strong> 5 discussion questions (30s each).',
            ],
        ],
    ];
    $cfg = $configs[$module];
    $progress = array_search($module, \App\Models\MockTest::MODULES) + 1;
    @endphp

    {{-- Module progress indicator --}}
    <div class="flex items-center gap-2 mb-6 justify-center">
        @foreach(['listening','reading','writing','speaking'] as $i => $m)
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                {{ $m === $module ? 'bg-brand-500 text-white' : (array_search($m, \App\Models\MockTest::MODULES) < array_search($module, \App\Models\MockTest::MODULES) ? 'bg-emerald-500 text-white' : 'bg-surface-800 text-surface-500') }}">
                {{ array_search($m, \App\Models\MockTest::MODULES) < array_search($module, \App\Models\MockTest::MODULES) ? '✓' : ($i + 1) }}
            </div>
            @if($i < 3)<div class="w-8 h-px bg-surface-700"></div>@endif
        </div>
        @endforeach
    </div>

    <div class="card p-6 sm:p-8">

        <div class="flex items-start gap-4 mb-5">
            <div class="text-5xl leading-none shrink-0">{{ $cfg['icon'] }}</div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-brand-400 uppercase tracking-widest mb-1">
                    Module {{ $progress }} of 4 — Instructions
                </p>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-surface-50 leading-tight">
                    {{ ucfirst($module) }}
                </h1>
                <p class="text-sm text-surface-400 mt-1.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong class="text-surface-200">{{ $cfg['time'] }}</strong></span>
                </p>
            </div>
        </div>

        <div class="bg-surface-900/60 border border-surface-700 rounded-xl p-4 sm:p-5 mb-5">
            <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Read before starting</p>
            <ul class="space-y-2.5">
                @foreach($cfg['rules'] as $rule)
                <li class="flex items-start gap-2.5 text-sm text-surface-200 leading-relaxed">
                    <svg class="w-4 h-4 text-brand-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{!! $rule !!}</span>
                </li>
                @endforeach
            </ul>
        </div>

        <form id="moduleForm" method="POST" action="{{ route($cfg['route']) }}">
            @csrf
            <input type="hidden" name="test_type" value="{{ $mock->test_type }}">
            <input type="hidden" name="exam_mode" value="1">

            @if($module === 'writing')
            {{-- Writing task selection — required before continuing --}}
            <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-2">Choose your task</p>
            <div class="grid grid-cols-2 gap-3 mb-5 text-left">
                <label class="cursor-pointer">
                    <input type="radio" name="task" value="task1" class="sr-only peer" checked>
                    <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                        <p class="font-semibold text-surface-100 text-sm">Task 1</p>
                        <p class="text-surface-500 text-xs mt-1">Graph, chart, map or process (150 words)</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="task" value="task2" class="sr-only peer">
                    <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                        <p class="font-semibold text-surface-100 text-sm">Task 2</p>
                        <p class="text-surface-500 text-xs mt-1">Essay / argument (250 words)</p>
                    </div>
                </label>
            </div>
            @endif

            <label class="flex items-start gap-3 p-3.5 rounded-xl bg-surface-700/60 border border-surface-600 mb-5 cursor-pointer hover:bg-surface-700/80 transition">
                <input type="checkbox" id="bridgeAck"
                    class="mt-0.5 w-4 h-4 rounded border-surface-500 bg-surface-800 text-brand-500 focus:ring-brand-500 focus:ring-offset-0 cursor-pointer shrink-0"
                    onchange="document.getElementById('beginModuleBtn').disabled = !this.checked;">
                <span class="text-sm text-surface-200 leading-relaxed">
                    I have read these instructions and I'm ready to start the <strong>{{ ucfirst($module) }}</strong> module timer.
                </span>
            </label>

            <button id="beginModuleBtn" type="submit" disabled
                class="w-full px-4 py-3 rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-bold text-base transition disabled:opacity-40 disabled:cursor-not-allowed shadow-glow disabled:shadow-none">
                Begin {{ ucfirst($module) }} →
            </button>
        </form>

        <p class="text-[11px] text-surface-500 text-center mt-4">
            The {{ $cfg['time'] }} timer starts the moment you tap Begin.
        </p>

        <form method="POST" action="{{ route('mock-test.abandon', $mock) }}" class="mt-6 text-center"
            onsubmit="return confirm('Abandon the full mock test? Your progress so far will be lost.')">
            @csrf
            <button class="text-xs text-surface-600 hover:text-red-400 transition-colors">Abandon mock test</button>
        </form>
    </div>

    <script>
        // Persist mock context across module transitions
        sessionStorage.setItem('mock_test_id', '{{ $mock->id }}');
    </script>

</div>
</div>
</x-app-layout>
