<x-app-layout>
<div class="min-h-screen bg-surface-950 py-12 px-4">
<div class="max-w-2xl mx-auto">

    <div class="text-center mb-10">
        <div class="w-20 h-20 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-5"
            style="box-shadow:0 0 40px rgba(16,185,129,0.2)">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-surface-50 mb-2">Full Mock Test Complete</h1>
        <p class="text-surface-400">{{ ucfirst($mock->test_type) }} · Completed {{ $mock->completed_at?->format('d M Y, h:i A') }}</p>
    </div>

    {{-- Overall band --}}
    @if($mock->overall_band)
    <div class="card p-8 text-center mb-6" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(6,182,212,0.08))">
        <p class="text-surface-400 text-sm uppercase tracking-wider mb-2">Overall IELTS Band</p>
        <div class="text-7xl font-bold text-brand-400 mb-2">{{ $mock->overall_band }}</div>
        <p class="text-surface-500 text-sm">Average of all 4 modules, rounded to nearest 0.5</p>
    </div>
    @endif

    {{-- Module breakdown --}}
    <div class="card p-6 mb-6">
        <h2 class="font-semibold text-surface-200 mb-5">Module Scores</h2>
        <div class="space-y-4">
            @foreach([
                ['module'=>'listening','label'=>'Listening','icon'=>'🎧','relation'=>'listening'],
                ['module'=>'reading', 'label'=>'Reading',  'icon'=>'📖','relation'=>'reading'],
                ['module'=>'writing', 'label'=>'Writing',  'icon'=>'✍️','relation'=>'writing'],
                ['module'=>'speaking','label'=>'Speaking', 'icon'=>'🎤','relation'=>'speaking'],
            ] as $row)
            @php
                $band = $mock->{$row['module'] . '_band'};
                $testId = $mock->{$row['module'] . '_test_id'};
            @endphp
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">{{ $row['icon'] }}</span>
                    <span class="font-medium text-surface-200">{{ $row['label'] }}</span>
                </div>
                <div class="flex items-center gap-4">
                    @if($band)
                    <span class="text-2xl font-bold {{ $band >= 7 ? 'text-emerald-400' : ($band >= 6 ? 'text-amber-400' : 'text-red-400') }}">
                        {{ $band }}
                    </span>
                    @else
                    <span class="text-surface-600">—</span>
                    @endif
                    @if($testId)
                    <a href="{{ $mock->routeForModule($row['module']) }}"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                        View Feedback →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Band interpretation --}}
    @if($mock->overall_band)
    @php
    $band = $mock->overall_band;
    $level = $band >= 8 ? 'Expert User' : ($band >= 7 ? 'Good User' : ($band >= 6 ? 'Competent User' : ($band >= 5 ? 'Modest User' : 'Limited User')));
    $desc  = $band >= 8 ? 'Full operational command of English. Only unsystematic inaccuracies.' :
             ($band >= 7 ? 'Operational command with occasional inaccuracies. Complex language handled well.' :
             ($band >= 6 ? 'Effective command in most situations with some inaccuracies.' :
             ($band >= 5 ? 'Partial command. Can handle overall meaning in most situations.' :
             'Incomplete command. Can handle basic communication in familiar situations.')));
    @endphp
    <div class="card p-5 mb-6">
        <p class="text-sm text-surface-400 mb-1">IELTS Band {{ $mock->overall_band }} — <span class="text-surface-200 font-semibold">{{ $level }}</span></p>
        <p class="text-surface-500 text-sm">{{ $desc }}</p>
    </div>
    @endif

    <div class="flex flex-col gap-3">
        <a href="{{ route('dashboard') }}" class="btn-primary justify-center py-3 text-base font-bold">
            Go to Dashboard
        </a>
        <a href="{{ route('mock-test.index') }}" class="btn-secondary justify-center py-3">
            Start Another Mock Test
        </a>
    </div>

</div>
</div>
</x-app-layout>
