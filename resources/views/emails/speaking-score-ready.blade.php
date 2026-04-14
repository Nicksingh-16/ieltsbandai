<x-mail::message>
# Your Speaking Score is Ready

Hi {{ $test->user->name }},

Your IELTS Speaking test has been evaluated by our AI examiner.

<x-mail::panel>
**Overall Band Score: {{ $test->overall_band ?? 'Processing' }}**
</x-mail::panel>

@if($test->testScores && $test->testScores->count())
**Criterion Breakdown:**

@foreach($test->testScores as $score)
- **{{ ucwords(str_replace('_', ' ', $score->criteria)) }}:** Band {{ $score->band }}
@endforeach
@endif

<x-mail::button :url="url('/test/' . $test->id . '/result')">
View Full Feedback
</x-mail::button>

Your result includes strengths, areas to improve, and the specific descriptor phrases the AI examiner used.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
