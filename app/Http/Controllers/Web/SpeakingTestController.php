<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\SpeakingRepository;
use App\Services\SpeakingTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SpeakingTestController extends Controller
{
    protected $speakingRepo;

    protected $speakingService;

    public function __construct(SpeakingRepository $speakingRepo, SpeakingTestService $speakingService)
    {
        $this->speakingRepo = $speakingRepo;
        $this->speakingService = $speakingService;
    }

    public function show()
    {
        try {
            // Get 3 random speaking questions (Part 1, 2, 3)
            $questions = $this->speakingRepo->getSpeakingQuestions();

            // Get or create test (reuses incomplete test if exists)
            $test = $this->speakingService->getOrCreateSpeakingTest(Auth::id(), $questions);

            // Atomic, idempotent charge — chargeForTest no-ops if already
            // charged, so re-entering an existing speaking test never
            // double-charges and a parallel /speaking/test fetch can't
            // create two charges either.
            if ($test->wasRecentlyCreated) {
                app(\App\Services\CreditService::class)->chargeForTest(Auth::user(), $test);
            }

            // If test already exists, rebuild questions array from test questions
            if ($test->testQuestions->isNotEmpty()) {
                $questions = [];
                foreach ($test->testQuestions as $testQuestion) {
                    $partKey = 'part'.$testQuestion->part;
                    $questions[$partKey] = $testQuestion->question;
                }
            }

            return view('pages.speaking.index', compact('test', 'questions'));
        } catch (\Exception $e) {
            Log::error('Speaking test access failed: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Unable to start speaking test: '.$e->getMessage());
        }
    }

    public function uploadAudio(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'audio' => 'required|file|mimetypes:audio/webm,video/webm,audio/wav,audio/mpeg,audio/ogg|max:10240', // 10MB max
                'test_id' => 'required|exists:tests,id',
                'duration' => 'nullable|integer|min:1|max:300', // 1-300 seconds
            ]);

            // Ensure test belongs to authenticated user
            $test = \App\Models\Test::where('id', $validated['test_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Process upload through service layer
            $result = $this->speakingService->uploadAudio(
                $validated['test_id'],
                $request->file('audio'),
                $validated['duration'] ?? null
            );

            // Mock test: on the final audio upload, override the redirect so
            // the front-end skips the speaking result polling page (which
            // would never resolve — scoring is deferred until paywall payment)
            // and jumps straight to mock-test.paywall via the advance flow.
            if (! empty($result['is_last']) && ($mockId = session('mock_test_id'))) {
                $mock = \App\Models\MockTest::find($mockId);
                if ($mock && $mock->user_id === Auth::id()) {
                    $result['mock_next_url'] = $mock->recordModuleAndNextRoute('speaking', $test);
                }
            }

            return response()->json($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Audio upload failed: '.$e->getMessage(), [
                'test_id' => $request->test_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to upload audio. Please try again.',
            ], 500);
        }
    }
}
