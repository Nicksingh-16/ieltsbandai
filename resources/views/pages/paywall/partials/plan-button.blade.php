{{--
    Plan CTA button. When RAZORPAY_KEY is set, renders a button that triggers
    the Razorpay Standard Checkout modal client-side. When unset, falls back
    to the legacy form-POST that creates a manual UPI payment.

    Vars: $plan (string), $label (string), $class (string), $razorpayKey (nullable string).
--}}
@if($razorpayKey)
    <button type="button"
            data-pay-plan="{{ $plan }}"
            class="{{ $class }} flex items-center justify-center">
        <span class="pay-label">{{ $label }}</span>
        <svg class="pay-spinner hidden w-4 h-4 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
    </button>
@else
    <form method="POST" action="{{ route('paywall.start') }}" class="block">
        @csrf
        <input type="hidden" name="plan" value="{{ $plan }}">
        <button type="submit" class="{{ $class }}">{{ $label }}</button>
    </form>
@endif
