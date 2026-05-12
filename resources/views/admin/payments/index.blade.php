<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Payments</h1>
            <p class="text-surface-400 text-sm mt-1">
                Verified revenue: <span class="text-emerald-400 font-semibold">₹{{ number_format($totalRevenue, 2) }}</span>
                &middot; Pending verification: <span class="text-amber-400 font-semibold">{{ $pendingVerification ?? 0 }}</span>
                &middot; Granted today: <span class="text-brand-400 font-semibold">{{ $grantedToday ?? 0 }}</span>
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="status" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All statuses</option>
            <option value="pending"               @selected(request('status')=='pending')>Pending</option>
            <option value="pending_verification"  @selected(request('status')=='pending_verification')>Pending verification</option>
            <option value="completed"             @selected(request('status')=='completed')>Verified</option>
            <option value="failed"                @selected(request('status')=='failed')>Failed</option>
            <option value="refunded"              @selected(request('status')=='refunded')>Refunded</option>
        </select>
        <button type="submit" class="btn-primary text-sm px-4">Filter</button>
        <a href="{{ route('admin.payments', ['status' => 'pending_verification']) }}" class="text-xs text-amber-400 self-center ml-2 hover:underline">Show only UTRs awaiting verify →</a>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr class="border-b border-surface-800 text-xs uppercase tracking-wider">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Order</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">User</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Plan</th>
                    <th class="text-right px-4 py-3 text-surface-400 font-medium">Amount</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Method</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">UTR</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Date</th>
                    <th class="text-right px-4 py-3 text-surface-400 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($payments as $payment)
                <tr class="hover:bg-surface-900/60">
                    <td class="px-4 py-3 font-mono text-xs text-surface-300">{{ $payment->order_id }}</td>
                    <td class="px-4 py-3">
                        <div class="text-surface-200">{{ optional($payment->user)->name ?? '—' }}</div>
                        <div class="text-surface-500 text-xs">{{ optional($payment->user)->email }}</div>
                    </td>
                    <td class="px-4 py-3 text-surface-300">
                        <span class="text-xs px-2 py-0.5 rounded bg-surface-800">{{ $payment->plan }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-400">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-4 py-3 text-xs text-surface-400 uppercase">{{ $payment->method ?? '—' }}</td>
                    <td class="px-4 py-3 font-mono text-[11px] text-surface-300">{{ $payment->proof_id ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusColor = match($payment->status) {
                                'completed'             => 'emerald',
                                'pending_verification'  => 'amber',
                                'pending'               => 'surface',
                                'failed', 'refunded'    => 'red',
                                default                 => 'surface',
                            };
                            $statusLabel = match($payment->status) {
                                'pending_verification' => 'Verify UTR',
                                'completed'            => 'Verified',
                                default                => ucfirst($payment->status),
                            };
                        @endphp
                        <span class="text-xs bg-{{ $statusColor }}-500/20 text-{{ $statusColor }}-400 px-2 py-0.5 rounded-full">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-500 text-xs whitespace-nowrap">{{ $payment->created_at->format('d M, H:i') }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($payment->method === 'manual' && $payment->status === 'pending_verification')
                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30">&check; Verify</button>
                            </form>
                            <button type="button" data-revoke="{{ $payment->id }}" class="text-xs px-2.5 py-1 rounded bg-red-500/10 text-red-300 border border-red-500/20 hover:bg-red-500/20">Revoke</button>
                            <form method="POST" action="{{ route('admin.payments.revoke', $payment) }}" id="revokeForm-{{ $payment->id }}" class="hidden">
                                @csrf
                                <input type="hidden" name="reason" value="">
                            </form>
                        @elseif($payment->method === 'manual' && $payment->status === 'completed')
                            <span class="text-[10px] text-emerald-500">verified {{ optional($payment->verified_at)->diffForHumans() }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-10 text-center text-surface-500 text-sm">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>

</div>
</div>

<script>
document.querySelectorAll('[data-revoke]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-revoke');
        const reason = window.prompt('Revoke this payment — what is the reason? (fake UTR / chargeback / fraud / mistake)');
        if (!reason || reason.trim().length < 3) return;
        const form = document.getElementById('revokeForm-' + id);
        form.querySelector('input[name="reason"]').value = reason.trim();
        form.submit();
    });
});
</script>
</x-app-layout>
