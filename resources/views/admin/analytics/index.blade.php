<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Analytics</h1>
            <p class="text-surface-400 text-sm mt-1">Last {{ $days }} days · first-party events</p>
        </div>
        <div class="flex gap-2">
            @foreach([7,14,30,60,90] as $d)
                <a href="?days={{ $d }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $days==$d ? 'bg-brand-500 text-white' : 'bg-surface-900 text-surface-400 hover:bg-surface-800' }}">{{ $d }}d</a>
            @endforeach
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm ml-2">← Dashboard</a>
        </div>
    </div>

    {{-- Funnel --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach([
            'Signups'        => ['signups',        'sky'],
            'Started a test' => ['started_test',   'amber'],
            'Completed'      => ['completed_test', 'emerald'],
            'Sent feedback'  => ['submitted_fb',   'rose'],
        ] as $label => [$key, $color])
            <div class="card p-5">
                <div class="text-xs uppercase tracking-wider text-surface-500 font-semibold">{{ $label }}</div>
                <div class="text-3xl font-bold text-{{ $color }}-400 mt-2">{{ $funnel[$key] }}</div>
                @if($key !== 'signups' && $funnel['signups'] > 0)
                    <div class="text-xs text-surface-500 mt-1">{{ round($funnel[$key] / $funnel['signups'] * 100) }}% of signups</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Signup sources --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Signup sources</h2>
            @if($signupsBySource->isEmpty())
                <p class="text-sm text-surface-500">No signups in this window.</p>
            @else
                @php $maxSource = $signupsBySource->max(); @endphp
                <div class="space-y-2">
                    @foreach($signupsBySource as $source => $count)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-surface-300 font-mono">{{ $source }}</span>
                                <span class="text-surface-400">{{ $count }}</span>
                            </div>
                            <div class="h-2 bg-surface-900 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-brand-500 to-brand-400" style="width: {{ ($count / $maxSource) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tests by type --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Tests by type</h2>
            @if($testsByType->isEmpty())
                <p class="text-sm text-surface-500">No tests in this window.</p>
            @else
                @php $maxType = $testsByType->max(); @endphp
                <div class="space-y-2">
                    @foreach($testsByType as $type => $count)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-surface-300 capitalize">{{ $type }}</span>
                                <span class="text-surface-400">{{ $count }}</span>
                            </div>
                            <div class="h-2 bg-surface-900 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400" style="width: {{ ($count / $maxType) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Event counts --}}
        <div class="card p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Events tracked</h2>
            @if($eventCounts->isEmpty())
                <p class="text-sm text-surface-500">No events in this window. Run the migration if you just deployed.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($eventCounts as $event => $count)
                        <div class="bg-surface-900 rounded-lg p-3">
                            <div class="text-xs text-surface-500 font-mono truncate">{{ $event }}</div>
                            <div class="text-xl font-bold text-surface-100 mt-1">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
</div>
</x-app-layout>
