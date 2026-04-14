<x-mail::message>
# Reminder: Test Due Soon

Hi {{ $student->name }},

This is a reminder that your test **"{{ $assignment->title }}"** is due in **{{ $hoursRemaining }} hour{{ $hoursRemaining !== 1 ? 's' : '' }}**.

<x-mail::panel>
- **Test:** {{ $assignment->title }}
@if($assignment->due_date)
- **Due:** {{ $assignment->due_date->format('d M Y, g:i A') }}
@endif
@if($assignment->is_mandatory)
- **Mandatory:** Yes
@endif
</x-mail::panel>

<x-mail::button :url="route('institute.my-tests')">
Complete Test Now
</x-mail::button>

Don't leave it to the last minute — good luck!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
