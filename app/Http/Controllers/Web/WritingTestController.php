<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\WritingEvaluationJob;
use App\Repositories\WritingRepository;
use App\Services\WritingTestService;
use Barryvdh\DomPDF\Facade\Pdf;

class WritingTestController extends Controller
{
    protected $writingRepo;
    protected $writingService;

    public function __construct(WritingRepository $writingRepo, WritingTestService $writingService)
    {
        $this->writingRepo = $writingRepo;
        $this->writingService = $writingService;
    }

    /**
     * Show writing test selection page
     */
    public function index()
    {
        return view('pages.writing.index');
    }

    /**
     * Start a new writing test
     */
    public function start(Request $request)
    {
        $request->validate([
            'test_type' => 'required|in:academic,general',
            'task' => 'required|in:task1,task2',
        ]);

        $testType = $request->input('test_type');
        $task = $request->input('task');
        
        // Build category string (e.g., 'writing_academic_task1')
        $category = "writing_{$testType}_{$task}";

        // Get a random question for this category
        $question = $this->writingRepo->getWritingQuestionByCategory($category, Auth::id());

        if (!$question) {
            return back()->with('error', 'No questions available for this task type.');
        }

        // Add time limit and min words to question object for view
        $timeLimit = str_contains($category, 'task1') ? 20 * 60 : 40 * 60;
        $minWords = str_contains($category, 'task1') ? 150 : 250;
        
        $question->time_limit = $timeLimit;
        $question->min_words = $minWords;

        // Create a test entry
        $test = $this->writingService->createWritingTest(Auth::id(), $question, $testType);

        // Deduct credit
        $creditService = app(\App\Services\CreditService::class);
        $creditService->deductCredit(Auth::user());

        // PRG pattern: redirect to GET so refresh doesn't re-submit start form
        return redirect()->route('writing.test', $test->id);
    }

    /**
     * Show an existing writing test (GET — safe to refresh)
     */
    public function showTest($testId)
    {
        $test = \App\Models\Test::with('testQuestions.question')->findOrFail($testId);

        if ($test->user_id !== Auth::id()) {
            abort(403);
        }

        // If already completed, send straight to results
        if ($test->status === 'completed') {
            return redirect()->route('writing.result', $testId);
        }

        $testQuestion = $test->testQuestions->first();
        if (!$testQuestion || !$testQuestion->question) {
            return redirect()->route('writing.index')->with('error', 'Test question not found.');
        }

        $question = $testQuestion->question;
        $testType = $test->test_type ?? 'academic';

        preg_match('/(task[12])/', $test->category ?? '', $matches);
        $task = $matches[1] ?? 'task1';

        $question->time_limit = str_contains($test->category ?? '', 'task1') ? 1200 : 2400;
        $question->min_words  = str_contains($test->category ?? '', 'task1') ? 150 : 250;

        return view('pages.writing.test', compact('test', 'question', 'testType', 'task'));
    }

    /**
     * Submit writing test answer — saves immediately, dispatches async job for AI scoring.
     */
    public function submit(Request $request, $testId)
    {
        $request->validate([
            'answer' => 'required|string|min:50',
        ]);

        $test = \App\Models\Test::where('id', $testId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Save answer and mark as evaluating
        $this->writingService->saveAnswerForEvaluation($testId, $request->input('answer'));

        // Dispatch async job — no more 3-minute blocking
        WritingEvaluationJob::dispatch($testId);

        return response()->json([
            'success'  => true,
            'message'  => 'Your writing has been submitted. Evaluating…',
            'redirect' => route('writing.result', $testId),
        ]);
    }

    /**
     * Show writing test results
     */
    public function result($testId)
    {
        try {
            // Get all data from service
            $data = $this->writingService->getWritingTestResults($testId);

            // Ensure user owns this test
            if ($data['test']->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access to test results');
            }

            // Extract testType and task for the view
            $testType = $data['test']->test_type ?? 'academic';
            
            $category = $data['test']->category ?? '';
            preg_match('/(task[12])/', $category, $matches);
            $task = $matches[1] ?? 'task1';

            // Extract all individual variables from data array
            $test = $data['test'];
            $scores = $data['scores'];
            $feedback = $data['feedback'];
            $strengths = $data['strengths'];
            $improvements = $data['improvements'];
            $word_count = $data['word_count'];
            $errors = $data['errors'];
            $unpositioned_errors = $data['unpositioned_errors'];
            $band_explanations = $data['band_explanations'] ?? [];
            $summary = $data['summary'] ?? null;
            $question = $data['question'] ?? null;
            $highlightedEssay = $data['highlightedEssay'];
            $task_info = $data['task_info'];
            $original_answer = $data['original_answer'] ?? '';
            
            // Examiner-Calibrated Fields
            $band_9_rewrite = $data['band_9_rewrite'] ?? '';
            $topic_vocabulary = $data['topic_vocabulary'] ?? [];
            $examiner_comments = $data['examiner_comments'] ?? [];
            $error_summary = $data['error_summary'] ?? [];

            return view('pages.results.index', compact(
                'test',
                'testType',
                'task',
                'scores',
                'feedback',
                'strengths',
                'improvements',
                'word_count',
                'errors',
                'unpositioned_errors',
                'band_explanations',
                'summary',
                'highlightedEssay',
                'task_info',
                'question',
                'original_answer',
                'band_9_rewrite',
                'topic_vocabulary',
                'examiner_comments',
                'error_summary'
            ));

        } catch (\Exception $e) {
            \Log::error('Writing result exception', [
                'test_id' => $testId,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // Check if the test exists but isn't completed yet (job still running)
            $test = \App\Models\Test::find($testId);
            if ($test && in_array($test->status, ['in_progress', 'evaluating'])) {
                return view('pages.writing.evaluating', compact('test'));
            }
            if ($test && $test->status === 'failed') {
                return redirect()->route('writing.index')
                    ->with('error', 'Writing evaluation failed. Please try again.');
            }

            return redirect()->route('dashboard')
                ->with('error', 'Could not load writing results: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF score report for a completed writing test.
     */
    public function downloadPdf($testId)
    {
        $data = $this->writingService->getWritingTestResults($testId);

        if ($data['test']->user_id !== Auth::id()) {
            abort(403);
        }

        $data['test']->load('user');

        $pdf = Pdf::loadView('pdf.writing-result', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("ielts-writing-band{$data['test']->overall_band}-{$testId}.pdf");
    }

    /**
     * Lazy-load Band 9 rewrite via AJAX
     */
    public function band9Rewrite($testId)
    {
        $test = \App\Models\Test::findOrFail($testId);

        if ($test->user_id !== Auth::id()) {
            abort(403);
        }

        $result = json_decode($test->result, true);

        // Return cached version if already generated
        if (!empty($result['band_9_rewrite'])) {
            return response()->json(['rewrite' => $result['band_9_rewrite']]);
        }

        // Generate now
        $answer = $result['original_answer'] ?? $test->answer ?? '';
        $testQuestion = \App\Models\TestQuestion::where('test_id', $testId)->first();
        $question = $testQuestion
            ? $this->writingRepo->getQuestionById($testQuestion->question_id)
            : null;

        try {
            $rewrite = $this->writingService->generateBand9Rewrite($answer, $question);

            // Cache it
            $result['band_9_rewrite'] = $rewrite;
            $test->update(['result' => json_encode($result)]);

            return response()->json(['rewrite' => $rewrite]);
        } catch (\Exception $e) {
            return response()->json(['rewrite' => null, 'error' => 'Could not generate model answer.'], 500);
        }
    }

    /**
     * Get test progress/status (for AJAX polling)
     */
    public function status($testId)
    {
        $test = \App\Models\Test::findOrFail($testId);

        // Ensure user owns this test
        if ($test->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'status' => $test->status,
            'completed' => $test->status === 'completed',
            'score' => $test->score,
        ]);
    }

    /**
     * Save draft (auto-save functionality)
     */
    public function saveDraft(Request $request, $testId)
    {
        $request->validate([
            'answer' => 'required|string',
        ]);

        $test = \App\Models\Test::findOrFail($testId);

        // Ensure user owns this test
        if ($test->user_id !== Auth::id()) {
            abort(403);
        }

        $metadata = json_decode($test->metadata, true) ?? [];
        $metadata['draft'] = $request->input('answer');
        $metadata['last_saved'] = now()->toISOString();

        $test->update([
            'metadata' => json_encode($metadata),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved',
            'word_count' => str_word_count($request->input('answer')),
        ]);
    }

    /**
     * Answer a follow-up clarification question about the writing result.
     * Uses the stored result context so the AI gives a personalised answer.
     */
    public function clarify(Request $request, $testId)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $test = \App\Models\Test::where('id', $testId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->firstOrFail();

        $result = json_decode($test->result, true) ?? [];

        $context = sprintf(
            "IELTS Writing Result Context:\n" .
            "- Overall band: %s\n" .
            "- Task Achievement: %s | Coherence & Cohesion: %s | Lexical Resource: %s | Grammar: %s\n" .
            "- Feedback: %s\n" .
            "- Strengths: %s\n" .
            "- Improvements: %s\n" .
            "- Essay (first 600 chars): %s",
            $test->overall_band,
            $result['task_achievement']    ?? 'N/A',
            $result['coherence_cohesion']  ?? 'N/A',
            $result['lexical_resource']    ?? 'N/A',
            $result['grammar']             ?? 'N/A',
            is_array($result['feedback']   ?? null) ? implode(' ', $result['feedback'])   : ($result['feedback']   ?? ''),
            is_array($result['strengths']  ?? null) ? implode('; ', $result['strengths']) : ($result['strengths']  ?? ''),
            is_array($result['improvements']?? null) ? implode('; ', $result['improvements']) : ($result['improvements'] ?? ''),
            substr($result['original_answer'] ?? $test->answer ?? '', 0, 600)
        );

        $prompt = "{$context}\n\nStudent question: {$request->input('question')}\n\n" .
                  "Answer as a friendly, expert IELTS examiner. Be specific, refer to the student's actual scores and essay. " .
                  "Be concise (max 150 words). Do not use bullet points — write in natural paragraph form.";

        try {
            // Try Gemini first (free tier)
            $gemini = app(\App\Services\GeminiService::class);
            $answer = null;

            if ($gemini->isAvailable()) {
                $answer = $gemini->generate($prompt, 0.7, 300);
            }

            // Fallback to OpenAI
            if (!$answer) {
                $response = \Illuminate\Support\Facades\Http::timeout(20)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                        'Content-Type'  => 'application/json',
                    ])
                    ->post(config('services.openai.base_url', 'https://api.openai.com/v1') . '/chat/completions', [
                        'model'       => config('services.openai.model', 'gpt-4o-mini'),
                        'messages'    => [
                            ['role' => 'system', 'content' => 'You are a friendly, expert IELTS examiner giving personalised feedback.'],
                            ['role' => 'user',   'content' => $prompt],
                        ],
                        'temperature' => 0.7,
                        'max_tokens'  => 300,
                    ]);

                $answer = $response->json('choices.0.message.content');
            }

            return response()->json(['answer' => trim($answer ?? 'I could not generate a response. Please try again.')]);

        } catch (\Exception $e) {
            \Log::error('Examiner clarify failed: ' . $e->getMessage());
            return response()->json(['answer' => 'Sorry, I am temporarily unavailable. Please try again shortly.'], 500);
        }
    }

    /**
     * Analyze vocabulary level (for real-time feedback)
     */
    public function analyzeVocabulary(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:10',
        ]);

        $text = $request->input('text');
        $words = str_word_count($text);

        // Only analyze if sufficient text (min 50 words to save API costs)
        if ($words < 50) {
            return response()->json([
                'basic' => 70,
                'intermediate' => 20,
                'advanced' => 10,
                'message' => 'Write at least 50 words for accurate analysis'
            ]);
        }

        try {
            $prompt = 'Analyze this IELTS essay text and categorize vocabulary as Basic (common everyday words), Intermediate (academic/professional), or Advanced (sophisticated/rare). Return percentages as JSON: {"basic": X, "intermediate": Y, "advanced": Z}' . "\n\nText: " . substr($text, 0, 500);

            // Try Gemini first (free)
            $gemini = app(\App\Services\GeminiService::class);
            $analysis = null;

            if ($gemini->isAvailable()) {
                $analysis = $gemini->generateJson($prompt, 0.3, 100);
            }

            // Fallback to OpenAI
            if (!$analysis) {
                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                        'Content-Type' => 'application/json',
                    ])
                    ->post(config('services.openai.base_url', 'https://api.openai.com/v1') . '/chat/completions', [
                        'model' => config('services.openai.model', 'gpt-4o-mini'),
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a vocabulary analyzer. Respond ONLY with valid JSON.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.3,
                        'max_tokens' => 100,
                        'response_format' => ['type' => 'json_object'],
                    ]);

                if (!$response->successful()) {
                    throw new \Exception('API request failed');
                }

                $content = $response->json('choices.0.message.content');
                if (!$content) throw new \Exception('No content in response');
                $analysis = json_decode($content, true);
            }

            return response()->json([
                'basic' => $analysis['basic'] ?? 60,
                'intermediate' => $analysis['intermediate'] ?? 30,
                'advanced' => $analysis['advanced'] ?? 10,
            ]);

        } catch (\Exception $e) {
            \Log::error('Vocabulary analysis failed: ' . $e->getMessage());
            
            // Return default values on error
            return response()->json([
                'basic' => 60,
                'intermediate' => 30,
                'advanced' => 10,
                'error' => 'Analysis temporarily unavailable'
            ]);
        }
    }
}