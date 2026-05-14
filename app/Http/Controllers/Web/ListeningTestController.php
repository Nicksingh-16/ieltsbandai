<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Test;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListeningTestController extends Controller
{
    public function index()
    {
        return view('pages.listening.index');
    }

    public function start(Request $request)
    {
        $request->validate([
            'test_type' => 'required|in:academic,general',
        ]);

        $testType = $request->input('test_type');
        $category = "listening_{$testType}";

        // Per ielts.org: all test takers take the SAME Listening test regardless
        // of Academic vs General Training. Reading/Writing differ between AC and
        // GT, but Listening is shared. So when picking a question, allow EITHER
        // category — that way the 8 VOA tests seeded as listening_academic also
        // serve listening_general users (and vice versa for any GT-tagged tests
        // we add later).
        $eligibleCategories = ['listening_academic', 'listening_general'];

        $userId = Auth::id();
        $seen = DB::table('test_questions')
            ->join('tests', 'tests.id', '=', 'test_questions.test_id')
            ->where('tests.user_id', $userId)
            ->where('tests.type', 'listening')
            ->orderByDesc('tests.created_at')
            ->limit(20)
            ->pluck('test_questions.question_id');

        // Only consider questions that actually have a playable audio URL.
        // The patterns are tight on purpose:
        //   '%"audio_url":"http%'              → audio_url is a real URL string
        //   '%"section_audios":["http%'        → section_audios array starts
        //                                         with a URL (NOT empty `[]`,
        //                                         which an earlier `[%` pattern
        //                                         would have matched and then
        //                                         tripped the post-check guard)
        // PostgreSQL and MySQL both honour LIKE on the JSON column when cast
        // to text; using LIKE keeps this dialect-portable.
        $audioFilter = fn ($q) => $q->where(function ($w) {
            $w->where('metadata', 'LIKE', '%"audio_url":"http%')
                ->orWhere('metadata', 'LIKE', '%"section_audios":["http%');
        });

        $question = Question::where('type', 'listening')
            ->whereIn('category', $eligibleCategories)
            ->where('active', true)
            ->where($audioFilter)
            ->when($seen->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $seen))
            ->inRandomOrder()
            ->first();

        // Fallback 1: relax the seen-deduplication, keep the audio filter.
        if (! $question) {
            $question = Question::where('type', 'listening')
                ->whereIn('category', $eligibleCategories)
                ->where('active', true)
                ->where($audioFilter)
                ->inRandomOrder()
                ->first();
        }

        if (! $question) {
            \Illuminate\Support\Facades\Log::warning('Listening start: no audio question available', [
                'category' => $category,
                'user_id' => $userId,
            ]);

            return back()->with('error', 'No listening questions with audio are available right now. Please contact support.');
        }

        // Audio guard — listening tests are unusable without audio. Check that
        // the question has at least one of audio_url or section_audios before
        // charging a credit and entering the test view (which otherwise renders
        // a "Audio plays here" placeholder and traps the user).
        $meta = is_string($question->metadata)
            ? (json_decode($question->metadata, true) ?: [])
            : (is_array($question->metadata) ? $question->metadata : []);
        $hasAudio = ! empty($meta['audio_url']) || ! empty($meta['section_audios']);
        if (! $hasAudio) {
            \Illuminate\Support\Facades\Log::warning('Listening question missing audio', [
                'question_id' => $question->id,
                'category' => $category,
            ]);

            return back()->with('error', 'This listening test is missing its audio. Please try another or contact support.');
        }

        try {
            $test = DB::transaction(function () use ($question, $testType) {
                $test = Test::create([
                    'user_id' => Auth::id(),
                    'type' => 'listening',
                    'test_type' => $testType,
                    'category' => "listening_{$testType}",
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
                $test->questions()->attach($question->id, ['part' => 1]);
                app(CreditService::class)->chargeForTest(Auth::user(), $test);

                return $test;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Could not start test: '.$e->getMessage());
        }

        $sections = is_string($question->metadata)
            ? (json_decode($question->metadata, true) ?: [])
            : (is_array($question->metadata) ? $question->metadata : []);

        $viewName = $request->boolean('exam_mode') ? 'exam.listening' : 'pages.listening.test';

        return view($viewName, compact('test', 'question', 'testType', 'sections'));
    }

    public function submit(Request $request, $testId)
    {
        $test = Test::where('id', $testId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Idempotency: re-submitting a completed test would overwrite the
        // score, re-fire Test::updated() observers (creditReferrer +
        // EventTracker), and rot analytics. Bounce to the result page.
        if ($test->status === 'completed') {
            return redirect()->route('listening.result', $test->id);
        }

        $submitted = $request->input('answers', []);
        $question = $test->questions()->first();
        $sections = is_string($question->metadata)
            ? (json_decode($question->metadata, true) ?: [])
            : (is_array($question->metadata) ? $question->metadata : []);
        $allQs = $sections['questions'] ?? [];

        $correct = 0;
        $total = 0;

        foreach ($allQs as $q) {
            $type = $q['type'] ?? 'fill';

            // ── Types that are a single scored item ──────────────────
            if (in_array($type, ['fill', 'sentence_completion', 'short_answer', 'mcq', 'note_completion', 'flow_chart', 'summary_completion'])) {
                $total++;
                $given = trim(strtolower($submitted[$q['id']] ?? ''));
                $answer = trim(strtolower($q['answer'] ?? ''));
                if ($given !== '' && $given === $answer) {
                    $correct++;
                }

                // ── MCQ multiple (choose 2 — 1 mark total, all-or-nothing) ─
            } elseif ($type === 'mcq_multi') {
                $total++;
                $raw = $submitted[$q['id']] ?? [];
                $selected = array_map('trim', array_map('strtolower', is_array($raw) ? $raw : [$raw]));
                $expected = array_map('trim', array_map('strtolower', $q['answers'] ?? []));
                sort($selected);
                sort($expected);
                if ($selected === $expected) {
                    $correct++;
                }

                // ── Matching / headings / sentence-endings / features ─────
                // Each item in the group = 1 mark
            } elseif (in_array($type, ['matching_item', 'heading_match', 'sentence_ending', 'feature_match'])) {
                $total++;
                $given = trim(strtolower($submitted[$q['id']] ?? ''));
                $answer = trim(strtolower($q['answer'] ?? ''));
                if ($given !== '' && $given === $answer) {
                    $correct++;
                }

                // ── Diagram label — text input per label ──────────────────
            } elseif ($type === 'diagram_label') {
                $labels = $q['labels'] ?? [];
                foreach ($labels as $lbl) {
                    $total++;
                    $key = $lbl['key'];
                    $given = trim(strtolower($submitted[$key] ?? ''));
                    $answer = trim(strtolower($lbl['answer'] ?? ''));
                    if ($given !== '' && $given === $answer) {
                        $correct++;
                    }
                }
            }
        }

        // Normalise to 40-mark scale for band lookup
        $raw40 = $total > 0 ? (int) round($correct / $total * 40) : 0;
        $band = $this->rawToBand($raw40);

        $test->update([
            'status' => 'completed',
            'score' => $band,
            'overall_band' => $band,
            'answer' => json_encode($submitted),
            'result' => json_encode([
                'correct' => $correct,
                'total' => $total,
                'percentage' => $total > 0 ? round($correct / $total * 100) : 0,
                'answers' => $submitted,
            ]),
            'completed_at' => now(),
        ]);

        return redirect()->route('listening.result', $test->id);
    }

    public function result($testId)
    {
        $test = Test::where('id', $testId)->where('user_id', Auth::id())->firstOrFail();
        $question = $test->questions()->first();
        $sections = is_string($question->metadata)
            ? (json_decode($question->metadata, true) ?: [])
            : (is_array($question->metadata) ? $question->metadata : []);
        $result = is_string($test->result)
            ? (json_decode($test->result, true) ?: [])
            : (is_array($test->result) ? $test->result : []);
        $answers = $result['answers'] ?? [];

        return view('pages.listening.result', compact('test', 'question', 'sections', 'result', 'answers'));
    }

    private function rawToBand(int $raw): float
    {
        $scale = [
            39 => 9.0, 37 => 8.5, 35 => 8.0, 33 => 7.5, 30 => 7.0,
            27 => 6.5, 23 => 6.0, 20 => 5.5, 16 => 5.0, 13 => 4.5,
            10 => 4.0,  8 => 3.5,  6 => 3.0,  4 => 2.5,  2 => 2.0,
        ];
        foreach ($scale as $threshold => $band) {
            if ($raw >= $threshold) {
                return $band;
            }
        }

        return 1.0;
    }
}
