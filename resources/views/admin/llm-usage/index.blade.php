<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">LLM Usage & Cost</h1>
            <p class="text-surface-400 text-sm mt-1">Last {{ $days }} days · first-party LLM call log</p>
        </div>
        <div class="flex gap-2">
            @foreach([1,7,14,30,60,90] as $d)
                <a href="?days={{ $d }}" class="px-3 py-1.5 rounded text-xs font-semibold {{ $days==$d ? 'bg-brand-500 text-white' : 'bg-surface-900 text-surface-400 hover:bg-surface-800' }}">{{ $d }}d</a>
            @endforeach
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm ml-2">← Dashboard</a>
        </div>
    </div>

    {{-- Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">Spend</div>
            <div class="text-2xl font-bold text-emerald-400 mt-1">${{ number_format($totals['cost_usd'], 4) }}</div>
            <div class="text-xs text-surface-500 mt-1">≈ ₹{{ number_format($totals['cost_usd'] * 83.5, 2) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">Calls</div>
            <div class="text-2xl font-bold text-sky-400 mt-1">{{ number_format($totals['calls_total']) }}</div>
            <div class="text-xs text-surface-500 mt-1">{{ $totals['calls_ok'] }} ok ({{ $totals['calls_total'] > 0 ? round($totals['calls_ok']/$totals['calls_total']*100) : 0 }}%)</div>
        </div>
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">Tokens in</div>
            <div class="text-2xl font-bold text-surface-200 mt-1">{{ number_format($totals['tokens_in']) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">Tokens out</div>
            <div class="text-2xl font-bold text-surface-200 mt-1">{{ number_format($totals['tokens_out']) }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">Avg latency</div>
            <div class="text-2xl font-bold text-amber-400 mt-1">{{ $totals['avg_latency_ms'] }}<span class="text-xs">ms</span></div>
        </div>
        <div class="card p-4">
            <div class="text-xs uppercase text-surface-500 font-semibold">$ / call</div>
            <div class="text-2xl font-bold text-emerald-300 mt-1">${{ $totals['calls_ok'] > 0 ? number_format($totals['cost_usd'] / $totals['calls_ok'], 5) : '0' }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">

        {{-- By provider --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">By provider</h2>
            @if($byProvider->isEmpty())
                <p class="text-sm text-surface-500">No LLM calls logged yet in this window.</p>
            @else
                @php $maxCost = max($byProvider->max('cost'), 0.0001); @endphp
                <div class="space-y-3">
                    @foreach($byProvider as $row)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-surface-200 font-semibold capitalize">{{ $row->provider }}</span>
                                <span class="text-surface-400">${{ number_format($row->cost, 4) }} · {{ $row->calls }} calls</span>
                            </div>
                            <div class="h-2 bg-surface-900 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400" style="width: {{ ($row->cost / $maxCost) * 100 }}%"></div>
                            </div>
                            <div class="text-[11px] text-surface-500 mt-1">{{ number_format($row->tin) }} in / {{ number_format($row->tout) }} out</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- By model --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">By model</h2>
            @if($byModel->isEmpty())
                <p class="text-sm text-surface-500">No data.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs text-surface-500 uppercase">
                        <tr><th class="text-left py-2">Provider</th><th class="text-left">Model</th><th class="text-right">Calls</th><th class="text-right">Cost</th></tr>
                    </thead>
                    <tbody class="divide-y divide-surface-800">
                        @foreach($byModel as $row)
                            <tr>
                                <td class="py-2 capitalize text-surface-300">{{ $row->provider }}</td>
                                <td class="font-mono text-xs text-surface-400">{{ $row->model }}</td>
                                <td class="text-right text-surface-300">{{ $row->calls }}</td>
                                <td class="text-right font-semibold text-emerald-400">${{ number_format($row->cost, 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Daily spend --}}
        <div class="card p-6 lg:col-span-2">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Daily spend</h2>
            @if($byDay->isEmpty())
                <p class="text-sm text-surface-500">No data.</p>
            @else
                @php $maxDayCost = max($byDay->max('cost'), 0.0001); @endphp
                <div class="grid grid-cols-7 md:grid-cols-14 gap-1 items-end h-32">
                    @foreach($byDay as $d)
                        <div class="flex flex-col items-center gap-1 group">
                            <div class="text-[10px] text-surface-500 opacity-0 group-hover:opacity-100 transition">${{ number_format($d->cost, 4) }}</div>
                            <div class="w-full bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-t"
                                 style="height: {{ max(2, ($d->cost / $maxDayCost) * 100) }}%"></div>
                            <div class="text-[9px] text-surface-600">{{ \Carbon\Carbon::parse($d->date)->format('M d') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Top users --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Top spenders (by user)</h2>
            @if($topUsers->isEmpty())
                <p class="text-sm text-surface-500">No data.</p>
            @else
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-surface-800">
                        @foreach($topUsers as $row)
                            <tr>
                                <td class="py-2">
                                    <div class="text-surface-200">{{ optional($row->user)->name ?? '—' }}</div>
                                    <div class="text-xs text-surface-500">{{ optional($row->user)->email }}</div>
                                </td>
                                <td class="text-right text-surface-300">{{ $row->calls }} calls</td>
                                <td class="text-right font-semibold text-emerald-400">${{ number_format($row->cost, 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Recent calls --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-surface-100 mb-4">Recent calls</h2>
            @if($recent->isEmpty())
                <p class="text-sm text-surface-500">No calls yet.</p>
            @else
                <div class="space-y-2 text-xs">
                    @foreach($recent as $row)
                        <div class="flex items-center justify-between gap-2 py-1.5 border-b border-surface-800/60">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $row->ok ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">{{ $row->http_status }}</span>
                                <span class="text-surface-300 truncate">{{ $row->provider }}/{{ $row->model }}</span>
                                @if($row->purpose)
                                    <span class="text-surface-500">· {{ $row->purpose }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-surface-500">{{ $row->latency_ms }}ms</span>
                                <span class="text-emerald-400 font-semibold">${{ number_format($row->cost_usd, 5) }}</span>
                                <span class="text-surface-600">{{ $row->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
</div>
</x-app-layout>
