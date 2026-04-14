<x-mail::message>
# Payment Confirmed

Hi {{ $user->name }}, your payment was successful.

<x-mail::panel>
**Plan:** {{ ucfirst($payment->plan) }}
**Amount Paid:** ₹{{ number_format($payment->amount, 2) }}
@if($payment->payment_id)
**Payment ID:** {{ $payment->payment_id }}
@endif
**Date:** {{ $payment->updated_at->format('d M Y, h:i A') }}
</x-mail::panel>

Your subscription is now active. All credits have been added to your account.

<x-mail::button :url="url('/dashboard')">
Go to Dashboard
</x-mail::button>

Keep this email as your receipt. If you have questions, reply to this email or contact our support.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
