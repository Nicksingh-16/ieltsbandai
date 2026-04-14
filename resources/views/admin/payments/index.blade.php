<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Payments</h1>
            <p class="text-surface-400 text-sm mt-1">Total revenue: <span class="text-emerald-400 font-semibold">₹{{ number_format($totalRevenue, 2) }}</span></p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    <form method="GET" class="flex gap-3 mb-6">
        <select name="status" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All statuses</option>
            <option value="completed" @selected(request('status')=='completed')>Completed</option>
            <option value="pending" @selected(request('status')=='pending')>Pending</option>
            <option value="failed" @selected(request('status')=='failed')>Failed</option>
        </select>
        <button type="submit" class="btn-primary text-sm px-4">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">User</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Plan</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Amount</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Payment ID</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @foreach($payments as $payment)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <div class="text-surface-200">{{ optional($payment->user)->name ?? '—' }}</div>
                        <div class="text-surface-500 text-xs">{{ optional($payment->user)->email }}</div>
                    </td>
                    <td class="px-4 py-3 text-surface-300 capitalize">{{ $payment->plan }}</td>
                    <td class="px-4 py-3 font-semibold text-emerald-400">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-4 py-3">
                        @php $colors = ['completed'=>'emerald','pending'=>'amber','failed'=>'red']; $c = $colors[$payment->status] ?? 'surface'; @endphp
                        <span class="text-xs bg-{{ $c }}-500/20 text-{{ $c }}-400 px-2 py-0.5 rounded-full capitalize">{{ $payment->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-500 font-mono text-xs">{{ $payment->payment_id ?? '—' }}</td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $payment->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>

</div>
</div>
</x-app-layout>
