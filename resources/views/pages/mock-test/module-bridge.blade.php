<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4">
<div class="max-w-lg w-full">

    @php
    $configs = [
        'listening' => ['icon'=>'🎧','color'=>'amber','time'=>'40 minutes','desc'=>'4 sections · 40 questions. Listen carefully — audio plays once.','route'=>'listening.start'],
        'reading'   => ['icon'=>'📖','color'=>'rose','time'=>'60 minutes','desc'=>'3 passages · 40 questions. Read the passage carefully before answering.','route'=>'reading.start'],
        'writing'   => ['icon'=>'✍️','color'=>'purple','time'=>'60 minutes','desc'=>'Task 1 (20 min) + Task 2 (40 min). Manage your time carefully.','route'=>'writing.start'],
        'speaking'  => ['icon'=>'🎤','color'=>'brand','time'=>'11–14 minutes','desc'=>'Parts 1, 2 & 3. Speak clearly and at a natural pace.','route'=>'speaking.test'],
    ];
    $cfg = $configs[$module];
    $progress = array_search($module, \App\Models\MockTest::MODULES) + 1;
    @endphp

    {{-- Progress indicator --}}
    <div class="flex items-center gap-2 mb-8 justify-center">
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

    <div class="card p-8 text-center">
        <div class="text-5xl mb-4">{{ $cfg['icon'] }}</div>
        <h1 class="text-2xl font-bold text-surface-50 mb-2">
            Module {{ $progress }}: {{ ucfirst($module) }}
        </h1>
        <p class="text-surface-400 mb-1">⏱ {{ $cfg['time'] }}</p>
        <p class="text-surface-500 text-sm mb-8">{{ $cfg['desc'] }}</p>

        {{-- Auto-submitting form to the module's start route --}}
        <form id="moduleForm" method="POST" action="{{ route($cfg['route']) }}">
            @csrf
            <input type="hidden" name="test_type" value="{{ $mock->test_type }}">
            @if($module === 'writing')
            {{-- Writing needs task selection — show buttons --}}
            <input type="hidden" name="task" value="task1" id="taskInput">
            <div class="grid grid-cols-2 gap-3 mb-6 text-left">
                <label class="cursor-pointer">
                    <input type="radio" name="task" value="task1" class="sr-only peer" checked>
                    <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                        <p class="font-semibold text-surface-100 text-sm">Task 1</p>
                        <p class="text-surface-500 text-xs mt-1">Graph, chart, map or process</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="task" value="task2" class="sr-only peer">
                    <div class="card p-4 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 hover:border-surface-500 transition-all">
                        <p class="font-semibold text-surface-100 text-sm">Task 2</p>
                        <p class="text-surface-500 text-xs mt-1">Essay / argument / discussion</p>
                    </div>
                </label>
            </div>
            @endif
            <button type="submit" class="btn-primary w-full py-3 text-base font-bold justify-center">
                Begin {{ ucfirst($module) }} →
            </button>
        </form>

        <form method="POST" action="{{ route('mock-test.abandon', $mock) }}" class="mt-4"
            onsubmit="return confirm('Abandon the full mock test?')">
            @csrf
            <button class="text-sm text-surface-600 hover:text-surface-400 transition-colors">Abandon mock test</button>
        </form>
    </div>

    {{-- Restore mock test session in case of browser navigation --}}
    <script>
        // Ensure mock test session context is maintained
        sessionStorage.setItem('mock_test_id', '{{ $mock->id }}');
    </script>

</div>
</div>
</x-app-layout>
