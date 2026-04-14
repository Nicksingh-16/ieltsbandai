<x-mail::message>
# Welcome to IELTS Band AI, {{ $user->name }}!

Your AI-powered IELTS examiner is ready. Here's what you can do right now:

<x-mail::panel>
**Your free credits:** 3 tests across all modules — Listening, Reading, Writing, Speaking
</x-mail::panel>

**What makes our scoring different:**
- Official IELTS band descriptors (Bands 4–9) embedded in every evaluation
- AI cites the exact descriptor phrase used to score you — fully transparent
- Band 9 model answer for every Writing test
- Speaking transcription + criterion-by-criterion breakdown

<x-mail::button :url="url('/dashboard')">
Go to Your Dashboard
</x-mail::button>

**Quick start:** Take a Writing or Speaking test first — these give the most detailed AI feedback.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
