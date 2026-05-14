<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Self-evaluation flow — user pastes their own IELTS Task 2 question + answer
 * and gets the full AI evaluation (no real test, no timer).
 *
 * Credit accounting:
 *   - Free pool: users.self_eval_credits (defaults to 5 on signup, separate
 *     from the test_credits pool used by the real exam flow).
 *   - After 5 free, redirects to the paywall's self_eval_single plan at ₹10.
 *   - Pro / Pro Plus subscribers get unlimited self-evals (via isPro()).
 *
 * Results are persisted as Test rows (so they show up in test history) with
 * metadata.self_eval=true so the rest of the system can distinguish them.
 */
class SelfEvaluationController extends Controller
{
    public function __construct(protected ScoringService $scorer) {}

    /** Show the paste-your-own-essay form. */
    public function index()
    {
        $user = Auth::user();
        $remaining = $this->resolveRemainingCredits($user);
        $isPro = app(\App\Services\CreditService::class)->isPro($user);

        return view('pages.self-eval.index', compact('remaining', 'isPro'));
    }

    /** Submit a question + answer for AI evaluation. */
    public function evaluate(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|min:30|max:2000',
            'answer' => 'required|string|min:100|max:8000',
        ], [
            'question.min' => 'Paste the full IELTS prompt (at least 30 characters).',
            'answer.min' => 'Your essay needs at least 100 characters to evaluate meaningfully.',
        ]);

        $user = $request->user();
        $isPro = app(\App\Services\CreditService::class)->isPro($user);
        $remaining = $this->resolveRemainingCredits($user);

        // Out of credits → paywall.
        if (! $isPro && $remaining <= 0) {
            return redirect()->route('paywall.index', ['from' => 'self_eval'])
                ->with('error', "You've used your free essay evaluation. Pick a plan below to keep going.");
        }

        // Hard word-count floor — under 100 words can't produce useful scoring
        // and would just waste an LLM call.
        $wordCount = str_word_count(trim($data['answer']));
        if ($wordCount < 100) {
            return back()->with('error', "Essay is only {$wordCount} words. Need at least 100 to evaluate.")->withInput();
        }

        // Decrement credit BEFORE the LLM call so a parallel submission can't
        // double-spend. Refund on scoring failure.
        $deducted = false;
        if (! $isPro) {
            DB::transaction(function () use ($user, &$deducted) {
                $locked = User::whereKey($user->id)->lockForUpdate()->first();
                if ($locked && $locked->self_eval_credits > 0) {
                    $locked->decrement('self_eval_credits');
                    $deducted = true;
                }
            });
            if (! $deducted) {
                return redirect()->route('paywall.index', ['from' => 'self_eval']);
            }
        }

        // Synthesise a Question-like object — ScoringService accepts a plain
        // object with ->content / ->category / ->metadata, no DB Question needed.
        $questionStub = (object) [
            'content' => $data['question'],
            'title' => $data['question'],
            'category' => 'writing_academic_task2',
            'metadata' => [],
        ];

        try {
            $scoring = $this->scorer
                ->scoreWriting($data['answer'], $questionStub, $user->id, null);
        } catch (\Throwable $e) {
            Log::error('Self-eval scoring failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            if ($deducted) {
                $user->increment('self_eval_credits');
            }

            return back()->with('error', 'Evaluation failed. Your credit was refunded — please try again.')->withInput();
        }

        if (! $scoring || ! isset($scoring['overall_band'])) {
            if ($deducted) {
                $user->increment('self_eval_credits');
            }

            return back()->with('error', 'AI returned an incomplete result. Your credit was refunded.')->withInput();
        }

        // Persist as a Test row so the existing /results/{id} view handles
        // display. metadata.self_eval=true tags it for the dashboard list.
        $test = Test::create([
            'user_id' => $user->id,
            'type' => 'writing',
            'test_type' => 'academic',
            'category' => 'writing_academic_task2',
            'status' => 'completed',
            'answer' => $data['answer'],
            'metadata' => [
                'self_eval' => true,
                'user_question' => $data['question'],
            ],
            'overall_band' => $scoring['overall_band'],
            'score' => $scoring['overall_band'],
            'result' => json_encode(array_merge($scoring, [
                'original_answer' => $data['answer'],
                'word_count' => $wordCount,
            ])),
            'started_at' => now(),
            'completed_at' => now(),
            'credit_charged_at' => $deducted ? now() : null,
        ]);

        Log::info('Self-eval completed', [
            'user_id' => $user->id,
            'test_id' => $test->id,
            'band' => $scoring['overall_band'],
            'words' => $wordCount,
        ]);

        return redirect()->route('writing.result', $test->id);
    }

    protected function resolveRemainingCredits(User $user): int|string
    {
        if (app(\App\Services\CreditService::class)->isPro($user)) {
            return 'Unlimited';
        }

        return (int) ($user->self_eval_credits ?? 0);
    }
}
