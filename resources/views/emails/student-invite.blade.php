<x-mail::message>
# You've been added to {{ $institute->name }}

Hi {{ $student->name }},

Your teacher has added you to **{{ $batch->name }}** on IELTS Band AI.

<x-mail::panel>
**Your login details:**
- **Email:** {{ $student->email }}
- **Temporary password:** {{ $tempPassword }}

Please change your password after first login.
</x-mail::panel>

<x-mail::button :url="url('/login')">
Log In Now
</x-mail::button>

**Batch details:**
- Batch: {{ $batch->name }}
- Test type: {{ ucfirst($batch->test_type) }} Training
@if($batch->target_band)
- Target band: {{ $batch->target_band }}
@endif
@if($batch->exam_date)
- Exam date: {{ $batch->exam_date->format('d M Y') }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
