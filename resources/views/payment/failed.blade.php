<x-app-layout>
<div class="min-h-screen bg-surface-950 flex items-center justify-center px-4 py-16">
    <div class="max-w-md w-full text-center">

        {{-- Icon --}}
        <div class="w-24 h-24 rounded-full bg-red-500/15 border border-red-500/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-surface-50 mb-2">Payment Failed</h1>
        <p class="text-surface-400 mb-8">{{ $message ?? 'Your payment could not be processed. You have not been charged.' }}</p>

        <div class="card p-5 mb-8 text-left">
            <p class="text-sm text-surface-300 font-semibold mb-2">What to do next:</p>
            <ul class="space-y-2 text-sm text-surface-400">
                <li class="flex items-start gap-2"><span class="text-amber-400 shrink-0 mt-0.5">→</span> Check your card details and try again</li>
                <li class="flex items-start gap-2"><span class="text-amber-400 shrink-0 mt-0.5">→</span> Make sure your bank hasn't blocked the transaction</li>
                <li class="flex items-start gap-2"><span class="text-amber-400 shrink-0 mt-0.5">→</span> Try a different payment method</li>
                <li class="flex items-start gap-2"><span class="text-amber-400 shrink-0 mt-0.5">→</span> Contact us if the problem persists</li>
            </ul>
        </div>

        <div class="flex flex-col gap-3">
            <a href="{{ route('pricing') }}" class="btn-primary justify-center py-3 text-base font-bold">
                Try Again
            </a>
            <a href="{{ route('contact') }}" class="btn-secondary justify-center py-3">
                Contact Support
            </a>
        </div>

    </div>
</div>
</x-app-layout>
