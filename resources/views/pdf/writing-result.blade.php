@php
    $criteria = [
        'task_achievement'   => ['short' => 'TA',  'label' => 'Task Achievement / Response'],
        'coherence_cohesion' => ['short' => 'CC',  'label' => 'Coherence & Cohesion'],
        'lexical_resource'   => ['short' => 'LR',  'label' => 'Lexical Resource'],
        'grammar'            => ['short' => 'GRA', 'label' => 'Grammatical Range & Accuracy'],
    ];

    $resolveDescriptor = function($key) use ($descriptor_match) {
        if (!is_array($descriptor_match)) return null;
        if (!empty($descriptor_match[$key])) return $descriptor_match[$key];
        if ($key === 'task_achievement' && !empty($descriptor_match['task_response'])) {
            return $descriptor_match['task_response'];
        }
        return null;
    };

    $taskInfo = $task_info ?? [];
    $taskTitle = $taskInfo['title'] ?? str_replace('_', ' ', ucwords($test->category ?? 'Writing Task'));

    $sevColor = function($sev) {
        $sev = strtolower((string) $sev);
        if ($sev === 'high')   return ['bg' => '#fee2e2', 'fg' => '#991b1b'];
        if ($sev === 'medium') return ['bg' => '#fef3c7', 'fg' => '#92400e'];
        return ['bg' => '#e5e7eb', 'fg' => '#374151'];
    };

    $errorsCapped = is_array($errors ?? null) ? array_slice($errors, 0, 25) : [];
    $errorsHidden = is_array($errors ?? null) ? max(0, count($errors) - 25) : 0;

    $confidenceRange = $scores['band_confidence_range'] ?? ($test->metadata['band_confidence_range'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>IELTS Writing Evaluation Report</title>
<style>
    @page { size: A4 portrait; margin: 20mm 18mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5pt; color: #111827; background: #ffffff; line-height: 1.5; }

    h1, h2, h3, h4 { font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }

    .doc-header { border-bottom: 3px solid #3b82f6; padding-bottom: 12px; margin-bottom: 18px; }
    .doc-header .brand { font-size: 9pt; color: #3b82f6; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
    .doc-header h1 { font-size: 18pt; font-weight: 700; margin-top: 4px; color: #0f172a; }
    .doc-header .subline { font-size: 9.5pt; color: #6b7280; margin-top: 4px; }

    .meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9.5pt; }
    .meta-table td { padding: 3px 8px 3px 0; color: #374151; vertical-align: top; }
    .meta-table td.lbl { color: #6b7280; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.05em; width: 90px; }

    .section { margin-top: 18px; page-break-inside: avoid; }
    .section-heading { font-size: 11pt; font-weight: 700; color: #0f172a; border-left: 4px solid #3b82f6; padding: 2px 0 2px 8px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.04em; }

    .prompt-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 10px 12px; font-size: 10pt; color: #374151; line-height: 1.55; }

    .score-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .score-table td { border: 1px solid #e5e7eb; padding: 10px 12px; vertical-align: top; }
    .score-overall { background: #1e3a8a; color: #ffffff; }
    .score-overall .lbl { font-size: 8.5pt; color: #bfdbfe; text-transform: uppercase; letter-spacing: 0.05em; }
    .score-overall .val { font-size: 28pt; font-weight: 700; line-height: 1.1; }
    .score-overall .conf { font-size: 9pt; color: #dbeafe; margin-top: 4px; }
    .crit-row td { padding: 8px 12px; }
    .crit-row .cname { font-size: 9.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.04em; }
    .crit-row .cscore { font-size: 14pt; font-weight: 700; color: #0f172a; }
    .crit-row .cdesc { font-size: 8.5pt; color: #4b5563; font-style: italic; margin-top: 2px; }

    .crit-block { border: 1px solid #e5e7eb; padding: 12px 14px; margin-bottom: 10px; page-break-inside: avoid; }
    .crit-block .crit-head { border-bottom: 1px solid #f3f4f6; padding-bottom: 6px; margin-bottom: 8px; }
    .crit-block .crit-head .name { font-size: 10.5pt; font-weight: 700; color: #0f172a; }
    .crit-block .crit-head .score { float: right; font-size: 13pt; font-weight: 700; color: #3b82f6; }
    .crit-block .descriptor { border-left: 3px solid #93c5fd; background: #eff6ff; padding: 6px 10px; margin: 8px 0; font-style: italic; font-size: 9.5pt; color: #1e40af; }
    .crit-block .why { font-size: 10pt; color: #374151; margin: 6px 0; }
    .crit-block .tip-label { font-size: 9pt; font-weight: 700; color: #047857; margin-top: 6px; }
    .crit-block .tip { font-size: 10pt; color: #064e3b; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 6px 10px; margin-top: 3px; }

    ul.tight { padding-left: 18px; margin: 4px 0; }
    ul.tight li { font-size: 10pt; color: #374151; margin-bottom: 4px; }

    .two-col { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
    .two-col td { vertical-align: top; width: 50%; border: 1px solid #e5e7eb; padding: 10px 12px; }
    .col-title { font-size: 9pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .col-strengths .col-title { color: #047857; }
    .col-improve .col-title { color: #b45309; }

    .err-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9pt; }
    .err-table th { background: #f3f4f6; color: #374151; text-align: left; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.04em; padding: 6px 8px; border: 1px solid #e5e7eb; }
    .err-table td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; color: #374151; }
    .err-table td.sev { text-align: center; font-weight: 700; font-size: 8.5pt; text-transform: uppercase; }
    .err-table td.orig { color: #991b1b; text-decoration: line-through; }
    .err-table td.corr { color: #047857; font-weight: 600; }
    .err-note { font-size: 8.5pt; color: #6b7280; margin-top: 4px; font-style: italic; }

    .stat-table { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 10pt; }
    .stat-table td { padding: 6px 10px; border: 1px solid #e5e7eb; }
    .stat-table td.lbl { color: #6b7280; background: #f9fafb; width: 60%; }
    .stat-table td.val { font-weight: 700; color: #0f172a; }

    .chip { display: inline-block; border: 1px solid #d1d5db; background: #f9fafb; color: #1f2937; font-size: 9pt; padding: 2px 8px; margin: 0 4px 4px 0; }

    .model-box { border: 1px solid #c7d2fe; background: #eef2ff; padding: 14px 16px; font-size: 10pt; color: #1e1b4b; line-height: 1.7; }
    .model-box p { margin-bottom: 8px; }

    .essay-box { border: 1px solid #e5e7eb; background: #fafafa; padding: 12px 14px; font-size: 10pt; color: #1f2937; line-height: 1.7; }
    .essay-box p { margin-bottom: 8px; }

    .page-break { page-break-before: always; }

    .disclaimer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8.5pt; color: #6b7280; line-height: 1.5; }
    .disclaimer .brand { color: #3b82f6; font-weight: 700; }
</style>
</head>
<body>

<div class="doc-header">
    <div class="brand">IELTS Band AI</div>
    <h1>Writing Evaluation Report</h1>
    <div class="subline">{{ $taskTitle }} — for self-study; not an official IELTS score.</div>
    <table class="meta-table">
        <tr>
            <td class="lbl">Candidate</td>
            <td>{{ $test->user->name ?? 'Student' }}</td>
            <td class="lbl">Completed</td>
            <td>{{ $test->completed_at?->format('d M Y, H:i') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Task</td>
            <td>{{ $taskTitle }}</td>
            <td class="lbl">Word count</td>
            <td>{{ $word_count ?? 0 }} words</td>
        </tr>
    </table>
</div>

@if(!empty($question?->content))
<div class="section">
    <div class="section-heading">Question Prompt</div>
    <div class="prompt-box">{{ $question->content }}</div>
</div>
@endif

<div class="section">
    <div class="section-heading">Band Scores</div>
    <table class="score-table">
        <tr>
            <td class="score-overall" rowspan="4" style="width: 32%; text-align: center;">
                <div class="lbl">Overall Band</div>
                <div class="val">{{ number_format($test->overall_band, 1) }}</div>
                @if($confidenceRange)
                <div class="conf">Confidence range: {{ $confidenceRange }}</div>
                @endif
            </td>
        </tr>
        @foreach($criteria as $key => $info)
        @php $descriptor = $resolveDescriptor($key); @endphp
        <tr class="crit-row">
            <td style="width: 18%;">
                <div class="cname">{{ $info['short'] }}</div>
                <div style="font-size: 8.5pt; color: #6b7280;">{{ $info['label'] }}</div>
            </td>
            <td style="width: 12%; text-align: center;">
                <div class="cscore">{{ number_format($scores[$key] ?? 0, 1) }}</div>
            </td>
            <td>
                @if($descriptor)
                <div class="cdesc">&ldquo;{{ $descriptor }}&rdquo;</div>
                @else
                <div class="cdesc" style="color:#9ca3af;">No descriptor returned.</div>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>

<div class="section">
    <div class="section-heading">Per-Criterion Analysis</div>
    @foreach($criteria as $key => $info)
        @php
            $score = $scores[$key] ?? 0;
            $expl  = $band_explanations[$key] ?? [];
            $why   = is_array($expl) ? ($expl['why'] ?? null) : null;
            $tip   = is_array($expl) ? ($expl['tip'] ?? null) : null;
            $descriptor = $resolveDescriptor($key);
        @endphp
        @if($why || $tip || $descriptor)
        <div class="crit-block">
            <div class="crit-head">
                <span class="score">{{ number_format($score, 1) }}</span>
                <span class="name">{{ $info['short'] }} — {{ $info['label'] }}</span>
            </div>
            @if($descriptor)
            <div class="descriptor">&ldquo;{{ $descriptor }}&rdquo;</div>
            @endif
            @if($why)
            <p class="why">{{ $why }}</p>
            @endif
            @if($tip)
            <div class="tip-label">How to reach the next band:</div>
            <div class="tip">{{ $tip }}</div>
            @endif
        </div>
        @endif
    @endforeach
</div>

@if(!empty($examiner_comments) && is_array($examiner_comments))
<div class="section">
    <div class="section-heading">Examiner Comments</div>
    <ul class="tight">
        @foreach($examiner_comments as $c)
            @if(is_string($c) && trim($c) !== '')
            <li>{{ $c }}</li>
            @endif
        @endforeach
    </ul>
</div>
@endif

@if((!empty($strengths) && count($strengths) > 0) || (!empty($improvements) && count($improvements) > 0))
<div class="section">
    <div class="section-heading">Strengths &amp; Areas to Improve</div>
    <table class="two-col">
        <tr>
            <td class="col-strengths">
                <div class="col-title">Strengths</div>
                @if(!empty($strengths))
                <ul class="tight">
                    @foreach($strengths as $s)
                    <li>{{ $s }}</li>
                    @endforeach
                </ul>
                @else
                <p style="font-size: 9.5pt; color: #9ca3af;">—</p>
                @endif
            </td>
            <td class="col-improve">
                <div class="col-title">To Improve</div>
                @if(!empty($improvements))
                <ul class="tight">
                    @foreach($improvements as $i)
                    <li>{{ $i }}</li>
                    @endforeach
                </ul>
                @else
                <p style="font-size: 9.5pt; color: #9ca3af;">—</p>
                @endif
            </td>
        </tr>
    </table>
</div>
@endif

@if(count($errorsCapped) > 0)
<div class="section">
    <div class="section-heading">Error Breakdown</div>
    <table class="err-table">
        <thead>
            <tr>
                <th style="width: 14%;">Type</th>
                <th style="width: 10%;">Severity</th>
                <th style="width: 22%;">Original</th>
                <th style="width: 22%;">Correction</th>
                <th>Explanation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($errorsCapped as $err)
                @php $sc = $sevColor($err['severity'] ?? 'low'); @endphp
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $err['type'] ?? $err['category'] ?? 'error')) }}</td>
                    <td class="sev" style="background: {{ $sc['bg'] }}; color: {{ $sc['fg'] }};">{{ strtoupper($err['severity'] ?? 'low') }}</td>
                    <td class="orig">{{ $err['text'] ?? '' }}</td>
                    <td class="corr">{{ $err['correction'] ?? '' }}</td>
                    <td>{{ $err['explanation'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($errorsHidden > 0)
    <p class="err-note">Showing top 25 of {{ count($errors) }} flagged errors. {{ $errorsHidden }} additional issues omitted to keep the report readable.</p>
    @endif
</div>
@endif

@if(!empty($error_summary) && is_array($error_summary))
<div class="section">
    <div class="section-heading">Error Pattern Summary</div>
    <table class="stat-table">
        @if(isset($error_summary['grammar_errors_per_100_words']))
        <tr>
            <td class="lbl">Grammar errors per 100 words</td>
            <td class="val">{{ $error_summary['grammar_errors_per_100_words'] }}</td>
        </tr>
        @endif
        @if(!empty($error_summary['repeated_errors']) && is_array($error_summary['repeated_errors']))
        <tr>
            <td class="lbl">Recurring patterns</td>
            <td class="val">
                <ul class="tight" style="padding-left: 14px;">
                    @foreach($error_summary['repeated_errors'] as $pattern)
                    <li style="font-weight: 400;">{{ $pattern }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @endif
    </table>
</div>
@endif

@if(!empty($topic_vocabulary) && is_array($topic_vocabulary) && count($topic_vocabulary) > 0)
<div class="section">
    <div class="section-heading">Advanced Topic Vocabulary</div>
    <div>
        @foreach($topic_vocabulary as $word)
        <span class="chip">{{ $word }}</span>
        @endforeach
    </div>
</div>
@endif

@if(!empty($band_9_rewrite) && trim($band_9_rewrite) !== '')
<div class="page-break"></div>
<div class="section">
    <div class="section-heading">Band 9 Model Rewrite</div>
    <p style="font-size: 9pt; color: #6b7280; margin-bottom: 8px;">A reference Band 9 response to the same prompt, generated to illustrate the target standard.</p>
    <div class="model-box">
        @foreach(preg_split('/\n\s*\n/', trim($band_9_rewrite)) as $para)
            @if(trim($para) !== '')
            <p>{!! nl2br(e(trim($para))) !!}</p>
            @endif
        @endforeach
    </div>
</div>
@endif

@if(!empty($original_answer) || !empty($test->answer))
<div class="page-break"></div>
<div class="section">
    <div class="section-heading">Your Submitted Answer ({{ $word_count ?? 0 }} words)</div>
    <div class="essay-box">
        @foreach(preg_split('/\n\s*\n/', trim($original_answer ?? $test->answer ?? '')) as $para)
            @if(trim($para) !== '')
            <p>{!! nl2br(e(trim($para))) !!}</p>
            @endif
        @endforeach
    </div>
</div>
@endif

<div class="disclaimer">
    <span class="brand">IELTS Band AI</span> — ieltsbandai.com<br>
    IELTS Writing Evaluation Report — for self-study; not an official IELTS score. Generated by AI on {{ now()->format('d M Y') }}.
</div>

</body>
</html>
