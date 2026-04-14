<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

    .header { background: #0f172a; color: #fff; padding: 24px 32px; margin-bottom: 24px; }
    .header h1 { font-size: 20px; font-weight: 700; color: #06b6d4; }
    .header p  { font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .header .meta { margin-top: 12px; display: flex; gap: 24px; font-size: 10px; color: #cbd5e1; }

    .section { padding: 0 32px; margin-bottom: 20px; }
    .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px; }

    .band-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 8px; }
    .band-cell { display: table-cell; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; width: 20%; }
    .band-cell .label { font-size: 9px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
    .band-cell .score { font-size: 22px; font-weight: 700; color: #0f172a; }
    .band-cell.overall { background: #0f172a; border-color: #0f172a; }
    .band-cell.overall .label { color: #06b6d4; }
    .band-cell.overall .score { color: #fff; font-size: 28px; }

    .feedback-box { background: #f8fafc; border-left: 3px solid #06b6d4; padding: 10px 14px; margin-bottom: 10px; border-radius: 0 4px 4px 0; }
    .feedback-box p { font-size: 11px; color: #334155; line-height: 1.6; }

    .list-section { margin-bottom: 10px; }
    .list-section ul { padding-left: 16px; }
    .list-section li { font-size: 11px; color: #334155; margin-bottom: 4px; line-height: 1.5; }
    .list-section .label { font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .strengths li::marker { color: #10b981; }
    .improvements li::marker { color: #f59e0b; }

    .essay-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; font-size: 10.5px; color: #334155; line-height: 1.8; }

    .footer { margin-top: 32px; padding: 16px 32px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }

    .tag { display: inline-block; background: #e2e8f0; color: #475569; font-size: 9px; padding: 2px 8px; border-radius: 10px; margin-right: 4px; }
    .tag-writing { background: #ede9fe; color: #6d28d9; }
    .tag-speaking { background: #cffafe; color: #0e7490; }
</style>
</head>
<body>

<div class="header">
    <h1>IELTS Band AI — Score Report</h1>
    <p>AI-Powered IELTS Writing Evaluation</p>
    <div class="meta">
        <span>Student: {{ $test->user->name }}</span>
        <span>Date: {{ $test->completed_at?->format('d M Y') }}</span>
        <span>Task Type: {{ str_replace('_', ' ', ucwords($test->category ?? 'Writing')) }}</span>
        <span>Words: {{ $word_count }}</span>
    </div>
</div>

{{-- Band Scores --}}
<div class="section">
    <div class="section-title">Band Scores</div>
    <div class="band-grid">
        <div class="band-cell overall">
            <div class="label">Overall Band</div>
            <div class="score">{{ number_format($test->overall_band, 1) }}</div>
        </div>
        @foreach(['task_achievement' => 'Task', 'coherence_cohesion' => 'Coherence', 'lexical_resource' => 'Lexical', 'grammar' => 'Grammar'] as $key => $label)
        <div class="band-cell">
            <div class="label">{{ $label }}</div>
            <div class="score">{{ number_format($scores[$key] ?? 0, 1) }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Examiner Feedback --}}
@if(!empty($feedback))
<div class="section">
    <div class="section-title">Examiner Feedback</div>
    <div class="feedback-box">
        <p>{{ $feedback }}</p>
    </div>
</div>
@endif

{{-- Strengths & Improvements --}}
<div class="section">
    <div style="display:table; width:100%; border-spacing:12px; border-collapse:separate;">
        <div style="display:table-cell; vertical-align:top; width:50%;">
            <div class="list-section strengths">
                <div class="label">✓ Strengths</div>
                <ul>
                    @forelse($strengths as $s)
                    <li>{{ $s }}</li>
                    @empty
                    <li>N/A</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div style="display:table-cell; vertical-align:top; width:50%;">
            <div class="list-section improvements">
                <div class="label">△ Areas to Improve</div>
                <ul>
                    @forelse($improvements as $i)
                    <li>{{ $i }}</li>
                    @empty
                    <li>N/A</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Question --}}
@if($question)
<div class="section">
    <div class="section-title">Question Prompt</div>
    <div class="essay-box" style="color:#475569; font-size:10px;">{{ $question->content }}</div>
</div>
@endif

{{-- Student Essay --}}
<div class="section">
    <div class="section-title">Your Essay ({{ $word_count }} words)</div>
    <div class="essay-box">{{ $original_answer }}</div>
</div>

<div class="footer">
    <span>Generated by IELTS Band AI · ieltsbandai.com</span>
    <span>This report is AI-generated and for practice purposes only.</span>
</div>

</body>
</html>
