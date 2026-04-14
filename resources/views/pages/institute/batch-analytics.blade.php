<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('institute.batch.show', $batch) }}" class="text-surface-500 hover:text-surface-300 text-sm">← {{ $batch->name }}</a>
            </div>
            <h1 class="text-2xl font-bold text-surface-50">Batch Analytics</h1>
            <p class="text-surface-400 text-sm mt-1">
                {{ $students->count() }} students &middot; {{ ucfirst($batch->test_type) }}
                @if($batch->target_band) &middot; Target: {{ $batch->target_band }} @endif
            </p>
        </div>
    </div>

    {{-- Batch Average Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach($modules as $m)
        @php $avg = $batchAverages[$m]; $isWeak = $weakestSkill === $m; @endphp
        <div class="card p-5 text-center {{ $isWeak ? 'border border-red-500/30' : '' }}">
            <div class="text-xs text-surface-400 uppercase tracking-wide mb-1">{{ ucfirst($m) }}</div>
            <div class="text-3xl font-bold {{ $avg ? 'text-surface-50' : 'text-surface-600' }}">
                {{ $avg ?? '—' }}
            </div>
            @if($isWeak && $avg)
            <div class="text-xs text-red-400 mt-1">Weakest skill</div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Score Distribution Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($modules as $m)
        @php $dist = $distribution[$m]; $hasData = array_sum($dist) > 0; @endphp
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-300 mb-4">{{ ucfirst($m) }} — Score Distribution</h3>
            @if($hasData)
            <canvas id="dist_{{ $m }}" height="120"></canvas>
            @else
            <div class="text-center text-surface-500 text-sm py-8">No {{ $m }} tests completed yet.</div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Assignment Completion --}}
    @if($assignments->isNotEmpty())
    <div class="card overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-surface-800">
            <h3 class="font-semibold text-surface-200">Assignment Completion</h3>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[480px]">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Assignment</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Progress</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @foreach($assignments as $a)
                @php $pct = $a->student_records_count ? round(($a->completed_count / $a->student_records_count) * 100) : 0; @endphp
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3 text-surface-200">{{ $a->title }}</td>
                    <td class="px-4 py-3"><span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ str_replace('_',' ', $a->template->type ?? '—') }}</span></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-surface-800 rounded-full h-1.5">
                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-surface-400">{{ $a->completed_count }}/{{ $a->student_records_count }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $a->due_date?->format('d M Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>{{-- /overflow-x-auto --}}
    </div>
    @endif

    {{-- Student Performance Table --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-800 flex items-center justify-between">
            <h3 class="font-semibold text-surface-200">Student Performance</h3>
            <span class="text-surface-500 text-xs">Latest band per module</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[540px]">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Student</th>
                    @foreach($modules as $m)
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">{{ ucfirst($m) }}</th>
                    @endforeach
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Avg</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Tests</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($studentRows as $row)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <div class="text-surface-200">{{ $row['student']->name }}</div>
                        <div class="text-surface-500 text-xs">{{ $row['student']->email }}</div>
                    </td>
                    @foreach($modules as $m)
                    @php $b = $row['bands'][$m]; @endphp
                    <td class="px-4 py-3">
                        @if($b['band'])
                        <span class="font-semibold text-surface-100">{{ number_format($b['band'], 1) }}</span>
                        @if($b['delta'] !== null)
                            <span class="text-xs ml-1 {{ $b['delta'] > 0 ? 'text-emerald-400' : ($b['delta'] < 0 ? 'text-red-400' : 'text-surface-500') }}">
                                {{ $b['delta'] > 0 ? '↑' : ($b['delta'] < 0 ? '↓' : '→') }}{{ abs($b['delta']) > 0 ? number_format(abs($b['delta']), 1) : '' }}
                            </span>
                        @endif
                        @else<span class="text-surface-600">—</span>@endif
                    </td>
                    @endforeach
                    <td class="px-4 py-3">
                        @if($row['avg_band'])
                        <span class="font-semibold text-cyan-400">{{ number_format($row['avg_band'], 1) }}</span>
                        @else<span class="text-surface-600">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-surface-400">{{ $row['total_tests'] }}</td>
                </tr>
                @empty
                <tr><td colspan="{{ count($modules) + 3 }}" class="px-4 py-10 text-center text-surface-500">No students in this batch yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /overflow-x-auto --}}
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const distData = @json($distribution);
const modules  = @json($modules);
const colors   = { writing:'#6366f1', speaking:'#06b6d4', listening:'#f59e0b', reading:'#10b981' };

modules.forEach(m => {
    const el = document.getElementById('dist_' + m);
    if (!el) return;
    const labels = Object.keys(distData[m]);
    const values = Object.values(distData[m]);
    new Chart(el, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Students',
                data: values,
                backgroundColor: colors[m] + '80',
                borderColor: colors[m],
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', font: { size: 11 } } },
                y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', stepSize: 1 }, beginAtZero: true },
            }
        }
    });
});
</script>
</x-app-layout>
