<x-mail::message>
# New Test Assigned: {{ $assignment->title }}

Hi {{ $student->name }},

Your teacher has assigned you a new test on **IELTS Band AI**.

<x-mail::panel>
**Assignment details:**
- **Title:** {{ $assignment->title }}
- **Institute:** {{ $assignment->institute->name ?? '' }}
@if($assignment->due_date)
- **Due date:** {{ $assignment->due_date->format('d M Y, g:i A') }}
@endif
@if($assignment->is_mandatory)
- **Status:** Mandatory
@endif
@if($assignment->instructions)

**Instructions:** {{ $assignment->instructions }}
@endif
</x-mail::panel>

<x-mail::button :url="route('institute.my-tests')">
Start Test
</x-mail::button>

Log in to your account and navigate to **My Tests** to complete this assignment.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
