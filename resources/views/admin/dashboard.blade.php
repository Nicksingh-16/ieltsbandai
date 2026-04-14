<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Admin Dashboard</h1>
            <p class="text-surface-400 text-sm mt-1">Platform overview</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.users') }}" class="btn-secondary text-sm">Users</a>
            <a href="{{ route('admin.payments') }}" class="btn-secondary text-sm">Payments</a>
            <a href="{{ route('admin.questions') }}" class="btn-secondary text-sm">Questions</a>
            <a href="{{ route('admin.question-sets.index') }}" class="btn-secondary text-sm">Question Sets</a>
            <a href="{{ route('admin.institutes') }}" class="btn-secondary text-sm">Institutes</a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @php
        $cards = [
            ['label' => 'Total Users',    'value' => number_format($stats['total_users']),    'color' => 'text-blue-400'],
            ['label' => 'Active (30d)',   'value' => number_format($stats['active_users']),   'color' => 'text-emerald-400'],
            ['label' => 'Pro Users',      'value' => number_format($stats['pro_users']),      'color' => 'text-amber-400'],
            ['label' => 'Total Tests',    'value' => number_format($stats['total_tests']),    'color' => 'text-purple-400'],
            ['label' => 'Tests Today',    'value' => number_format($stats['tests_today']),    'color' => 'text-cyan-400'],
            ['label' => 'Total Revenue',  'value' => '₹' . number_format($stats['total_revenue']), 'color' => 'text-emerald-400'],
            ['label' => 'Revenue (Month)','value' => '₹' . number_format($stats['revenue_month']), 'color' => 'text-emerald-300'],
            ['label' => 'Institutes',     'value' => number_format($stats['total_institutes']),'color' => 'text-indigo-400'],
            ['label' => 'Questions',      'value' => number_format($stats['total_questions']), 'color' => 'text-surface-300'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            <div class="text-xs text-surface-400 mt-1">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-300 mb-4">Daily Registrations (14 days)</h3>
            <canvas id="regChart" height="120"></canvas>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-300 mb-4">Daily Revenue ₹ (14 days)</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    type: 'line',
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#1e293b' } },
            y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: '#1e293b' }, beginAtZero: true },
        },
    },
};

const regLabels = @json($registrations->keys());
const regData   = @json($registrations->values());
new Chart(document.getElementById('regChart'), {
    ...chartDefaults,
    data: {
        labels: regLabels,
        datasets: [{ data: regData, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.4, fill: true }],
    },
});

const revLabels = @json($dailyRevenue->keys());
const revData   = @json($dailyRevenue->values());
new Chart(document.getElementById('revenueChart'), {
    ...chartDefaults,
    data: {
        labels: revLabels,
        datasets: [{ data: revData, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true }],
    },
});
</script>
</x-app-layout>
