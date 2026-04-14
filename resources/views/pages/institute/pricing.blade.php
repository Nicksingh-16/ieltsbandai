<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-6xl mx-auto px-4 py-12">

    {{-- Header --}}
    <div class="text-center mb-12">
        <span class="inline-block text-xs font-semibold text-brand-400 bg-brand-500/10 border border-brand-500/20 rounded-full px-4 py-1.5 mb-4 uppercase tracking-widest">Institute Plans</span>
        <h1 class="text-4xl font-bold text-surface-50 mb-4">Simple, transparent pricing<br>for institutions</h1>
        <p class="text-surface-400 text-lg max-w-2xl mx-auto">Empower your students with AI-powered IELTS preparation. All plans include full access to writing, speaking, reading, and listening modules.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-5 py-4 text-emerald-400 text-sm mb-8 text-center">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-5 py-4 text-red-400 text-sm mb-8 text-center">{{ session('error') }}</div>
    @endif

    {{-- Current plan badge --}}
    @if($institute)
    <div class="flex justify-center mb-8">
        <div class="inline-flex items-center gap-2 bg-surface-800 border border-surface-700 rounded-full px-4 py-2 text-sm">
            <span class="text-surface-400">Current plan:</span>
            <span class="font-semibold text-brand-400 capitalize">{{ $institute->plan ?? 'Free' }}</span>
            <span class="text-surface-500">&middot;</span>
            <span class="text-surface-400">{{ $institute->seats_used ?? 0 }} / {{ $institute->seat_limit ?? 0 }} seats used</span>
        </div>
    </div>
    @endif

    {{-- Pricing cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
        @foreach($plans as $key => $plan)
        @php $isCurrent = $institute && ($institute->plan ?? '') === $key; @endphp
        <div class="relative rounded-2xl border {{ $plan['badge'] === 'Most Popular' ? 'border-brand-500/50 bg-surface-900' : 'border-surface-700 bg-surface-900' }} p-6 flex flex-col">

            @if($plan['badge'])
            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                <span class="bg-brand-500 text-white text-xs font-bold px-4 py-1 rounded-full">{{ $plan['badge'] }}</span>
            </div>
            @endif

            <div class="mb-6">
                <h3 class="text-lg font-bold text-surface-100 mb-1">{{ $plan['name'] }}</h3>
                <div class="flex items-baseline gap-1 mb-1">
                    <span class="text-3xl font-bold text-surface-50">₹{{ number_format($plan['price']) }}</span>
                    <span class="text-surface-500 text-sm">/ month</span>
                </div>
                <p class="text-xs text-surface-500">Up to {{ number_format($plan['seat_limit']) }} students</p>
            </div>

            <ul class="space-y-2.5 mb-8 flex-1">
                @foreach($plan['features'] as $feature)
                <li class="flex items-start gap-2 text-sm text-surface-300">
                    <svg class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>

            @if($isCurrent)
            <div class="w-full text-center py-2.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold">
                Current Plan
            </div>
            @elseif(!$institute)
            <a href="{{ route('institute.landing') }}" class="w-full text-center py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold transition-colors block">
                Register Institute First
            </a>
            @else
            <button onclick="startPayment('{{ $key }}', {{ $plan['price'] * 100 }}, '{{ $plan['name'] }}')"
                class="w-full py-2.5 rounded-xl {{ $plan['badge'] === 'Most Popular' ? 'bg-brand-500 hover:bg-brand-600 text-white' : 'bg-surface-700 hover:bg-surface-600 text-surface-100' }} text-sm font-semibold transition-colors">
                Upgrade to {{ $plan['name'] }}
            </button>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Feature comparison table --}}
    <div class="card overflow-hidden mb-12">
        <div class="px-6 py-4 border-b border-surface-800">
            <h3 class="font-semibold text-surface-100">Full feature comparison</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-800">
                        <th class="text-left px-6 py-3 text-surface-400 font-medium w-1/2">Feature</th>
                        <th class="text-center px-4 py-3 text-surface-400 font-medium">Starter</th>
                        <th class="text-center px-4 py-3 text-brand-400 font-medium">Pro</th>
                        <th class="text-center px-4 py-3 text-surface-400 font-medium">Enterprise</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-800">
                    @foreach([
                        ['Students', '30', '100', '500'],
                        ['IELTS Modules', 'All 4', 'All 4', 'All 4'],
                        ['Question Bank', '50 questions', 'Unlimited', 'Unlimited'],
                        ['Batch Management', '✓', '✓', '✓'],
                        ['Assignment Tracking', '✓', '✓', '✓'],
                        ['Batch Analytics', '✓', '✓', '✓'],
                        ['PDF Score Reports', '—', '✓', '✓'],
                        ['Student Progress Delta', '—', '✓', '✓'],
                        ['Mock Test Builder', '—', '✓', '✓'],
                        ['Priority Support', '—', '✓', '✓'],
                        ['Dedicated Account Manager', '—', '—', '✓'],
                        ['API Access', '—', '—', '✓'],
                    ] as $row)
                    <tr class="hover:bg-surface-900/50">
                        <td class="px-6 py-3 text-surface-300">{{ $row[0] }}</td>
                        @foreach(array_slice($row, 1) as $val)
                        <td class="px-4 py-3 text-center {{ $val === '✓' ? 'text-emerald-400' : ($val === '—' ? 'text-surface-600' : 'text-surface-300') }} font-medium">{{ $val }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="max-w-2xl mx-auto">
        <h3 class="text-xl font-bold text-surface-100 text-center mb-6">Frequently Asked Questions</h3>
        <div class="space-y-4" x-data="{open:null}">
            @foreach([
                ['Can I upgrade or downgrade my plan?', 'Yes. Upgrades take effect immediately. For downgrades, the change applies at the next billing cycle.'],
                ['What happens when I exceed my seat limit?', 'You can\'t add new students beyond your seat limit. Upgrade your plan to increase capacity.'],
                ['Is there a free trial?', 'Yes — every new institute gets a 14-day free trial with Starter features (up to 10 students).'],
                ['What payment methods are accepted?', 'We accept all major credit/debit cards, UPI, net banking, and wallets via Razorpay.'],
                ['Can I get a demo first?', 'Yes — email us at support@ieltsbandai.com and we\'ll set up a walkthrough call.'],
            ] as [$q, $a])
            <div class="border border-surface-700 rounded-xl overflow-hidden">
                <button @click="open = open === '{{ $loop->index }}' ? null : '{{ $loop->index }}'"
                    class="w-full text-left px-5 py-4 flex items-center justify-between text-surface-200 font-medium text-sm hover:bg-surface-800 transition-colors">
                    <span>{{ $q }}</span>
                    <svg class="w-4 h-4 text-surface-400 transition-transform" :class="open === '{{ $loop->index }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === '{{ $loop->index }}'" x-collapse class="px-5 pb-4 text-sm text-surface-400 leading-relaxed">
                    {{ $a }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
</div>

{{-- Razorpay Checkout Script --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
async function startPayment(planKey, amountPaise, planName) {
    try {
        const res = await fetch('{{ route('payment.institute.initiate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ plan: planKey, amount: amountPaise }),
        });

        const data = await res.json();
        if (!data.success) {
            alert(data.message || 'Failed to initiate payment.');
            return;
        }

        const options = {
            key:         data.razorpay_key,
            amount:      data.amount,
            currency:    'INR',
            name:        'IELTS Band AI',
            description: planName + ' — Institute Plan',
            order_id:    data.order_id,
            prefill:     data.prefill ?? {},
            theme:       { color: '#06b6d4' },
            handler: function(response) {
                window.location.href = '{{ route('payment.institute.success') }}?payment_id=' + response.razorpay_payment_id + '&order_id=' + response.razorpay_order_id;
            },
        };

        const rzp = new Razorpay(options);
        rzp.open();

    } catch (err) {
        console.error(err);
        alert('Something went wrong. Please try again.');
    }
}
</script>
</x-app-layout>
