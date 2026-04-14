<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-16">
    <div class="max-w-md w-full text-center">

        {{-- Icon --}}
        <div class="w-24 h-24 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-6" style="box-shadow:0 0 40px rgba(16,185,129,0.2)">
            <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-surface-50 mb-2">Payment Successful!</h1>
        <p class="text-surface-400 mb-2">{{ $message ?? 'Your subscription is now active.' }}</p>

        @if(isset($payment) && $payment)
        <div class="card p-5 mb-8 text-left space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Plan</span>
                <span class="text-surface-100 font-semibold capitalize">{{ $payment->plan }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Amount paid</span>
                <span class="text-surface-100 font-semibold">₹{{ number_format($payment->amount, 2) }}</span>
            </div>
            @if($payment->payment_id)
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Payment ID</span>
                <span class="text-surface-500 text-xs font-mono">{{ $payment->payment_id }}</span>
            </div>
            @endif
        </div>
        @else
        <div class="mb-8"></div>
        @endif

        <div class="flex flex-col gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary justify-center py-3 text-base font-bold">
                Go to Dashboard
            </a>
            <a href="{{ route('speaking.index') }}" class="btn-secondary justify-center py-3">
                Start a Test
            </a>
        </div>

    </div>
</div>
</x-app-layout>
