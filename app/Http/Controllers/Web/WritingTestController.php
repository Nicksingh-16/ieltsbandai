<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\WritingRepository;
use App\Services\WritingTestService;

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
        $question = $this->writingRepo->getWritingQuestionByCategory($category);

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

        return view('pages.writing.test', compact('test', 'question', 'testType', 'task'));
    }

    /**
     * Submit writing test answer
     */
    public function submit(Request $request, $testId)
    {
        $request->validate([
            'answer' => 'required|string|min:50',
        ]);

        $answer = $request->input('answer');

        $result = $this->writingService->submitWritingTest($testId, $answer);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your writing has been evaluated successfully!',
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

    \Log::error('❌ Writing result exception', [
        'test_id' => $testId,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => collect($e->getTrace())->take(5),
    ]);

    dd(
        'EXCEPTION CAUGHT',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
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
            // Use OpenAI to categorize vocabulary
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.openai.base_url', 'https://api.openai.com/v1') . '/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a vocabulary analyzer. Respond ONLY with valid JSON.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze this text and categorize words as Basic (common everyday words), Intermediate (academic/professional), or Advanced (sophisticated/rare). Return percentages as JSON: {\"basic\": X, \"intermediate\": Y, \"advanced\": Z}\n\nText: " . substr($text, 0, 500),
                        ],
                    ],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed');
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            
            if (!$content) {
                throw new \Exception('No content in response');
            }

            $analysis = json_decode($content, true);

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