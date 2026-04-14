<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Test;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReadingTestController extends Controller
{
    public function index()
    {
        return view('pages.reading.index');
    }

    public function start(Request $request)
    {
        $request->validate([
            'test_type' => 'required|in:academic,general',
        ]);

        $testType = $request->input('test_type');
        $category = "reading_{$testType}";

        $userId = Auth::id();
        $seen = DB::table('test_questions')
            ->join('tests', 'tests.id', '=', 'test_questions.test_id')
            ->where('tests.user_id', $userId)
            ->where('tests.type', 'reading')
            ->orderByDesc('tests.created_at')
            ->limit(20)
            ->pluck('test_questions.question_id');

        $question = Question::where('type', 'reading')
            ->where('category', $category)
            ->where('active', true)
            ->when($seen->isNotEmpty(), fn($q) => $q->whereNotIn('id', $seen))
            ->inRandomOrder()
            ->first();

        // Fallback: all questions seen — ignore deduplication
        if (!$question) {
            $question = Question::where('type', 'reading')
                ->where('category', $category)
                ->where('active', true)
                ->inRandomOrder()
                ->first();
        }

        if (!$question) {
            return back()->with('error', 'No reading passages available for this type. Please try again later.');
        }

        $test = DB::transaction(function () use ($question, $testType) {
            $test = Test::create([
                'user_id'    => Auth::id(),
                'type'       => 'reading',
                'test_type'  => $testType,
                'category'   => "reading_{$testType}",
                'status'     => 'in_progress',
                'started_at' => now(),
            ]);

            $test->questions()->attach($question->id, ['part' => 1]);

            return $test;
        });

        $creditService = app(CreditService::class);
        $creditService->deductCredit(Auth::user());

        $meta = json_decode($question->metadata ?? '{}', true);

        return view('pages.reading.test', compact('test', 'question', 'testType', 'meta'));
    }

    public function submit(Request $request, $testId)
    {
        $test = Test::where('id', $testId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $submitted  = $request->input('answers', []);
        $question   = $test->questions()->first();
        $meta       = json_decode($question->metadata ?? '{}', true);
        $allQs      = $meta['questions'] ?? [];

        $correct = 0;
        $total   = 0;

        foreach ($allQs as $q) {
            $type = $q['type'] ?? 'fill';

            // ── Single-answer types ──────────────────────────────────────
            if (in_array($type, ['fill', 'sentence_completion', 'short_answer', 'mcq',
                                  'note_completion', 'flow_chart', 'summary_completion',
                                  'tfng', 'yngng',
                                  'matching_item', 'heading_match', 'sentence_ending', 'feature_match'])) {
                $total++;
                $given  = trim(strtolower($submitted[$q['id']] ?? ''));
                $answer = trim(strtolower($q['answer'] ?? ''));
                if ($given !== '' && $given === $answer) $correct++;

            // ── MCQ multiple (choose 2 — all-or-nothing) ─────────────────
            } elseif ($type === 'mcq_multi') {
                $total++;
                $raw      = $submitted[$q['id']] ?? [];
                $selected = array_map('trim', array_map('strtolower', is_array($raw) ? $raw : [$raw]));
                $expected = array_map('trim', array_map('strtolower', $q['answers'] ?? []));
                sort($selected); sort($expected);
                if ($selected === $expected) $correct++;

            // ── Diagram label — 1 mark per label ─────────────────────────
            } elseif ($type === 'diagram_label') {
                foreach ($q['labels'] ?? [] as $lbl) {
                    $total++;
                    $given  = trim(strtolower($submitted[$lbl['key']] ?? ''));
                    $answer = trim(strtolower($lbl['answer'] ?? ''));
                    if ($given !== '' && $given === $answer) $correct++;
                }
            }
        }

        // Normalise raw score to 40-mark scale for band lookup
        $raw40 = $total > 0 ? (int) round($correct / $total * 40) : 0;
        $band  = $this->rawToBand($raw40);

        $test->update([
            'status'       => 'completed',
            'score'        => $band,
            'overall_band' => $band,
            'answer'       => json_encode($submitted),
            'result'       => json_encode([
                'correct'    => $correct,
                'total'      => $total,
                'percentage' => $total > 0 ? round($correct / $total * 100) : 0,
                'answers'    => $submitted,
            ]),
            'completed_at' => now(),
        ]);

        return redirect()->route('reading.result', $test->id);
    }

    public function result($testId)
    {
        $test = Test::where('id', $testId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $question = $test->questions()->first();
        $meta     = json_decode($question->metadata ?? '{}', true);
        $result   = json_decode($test->result ?? '{}', true);
        $answers  = $result['answers'] ?? [];

        return view('pages.reading.result', compact('test', 'question', 'meta', 'result', 'answers'));
    }

    private function rawToBand(int $raw): float
    {
        // Reading band scale — slightly different from Listening
        $scale = [
            39 => 9.0, 37 => 8.5, 35 => 8.0, 33 => 7.5, 30 => 7.0,
            27 => 6.5, 23 => 6.0, 19 => 5.5, 15 => 5.0, 13 => 4.5,
            10 => 4.0,  8 => 3.5,  6 => 3.0,  4 => 2.5,  2 => 2.0,
        ];

        foreach ($scale as $threshold => $band) {
            if ($raw >= $threshold) return $band;
        }

        return 1.0;
    }
}
