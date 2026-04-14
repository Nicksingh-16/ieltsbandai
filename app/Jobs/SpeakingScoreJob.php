<?php

namespace App\Jobs;

use App\Mail\SpeakingScoreReadyMail;
use App\Models\Test;
use App\Models\TestScore;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Scores a completed speaking test once all 3 transcripts are available.
 * Dispatched by TranscribeAudioJob after the last part is transcribed.
 */
class SpeakingScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $timeout = 120; // OpenAI scoring should complete well within 2 min
    public $backoff = [30, 90];

    public function __construct(private int $testId) {}

    public function handle(ScoringService $scoringService): void
    {
        $test = Test::with(['audioFiles', 'testQuestions.question'])
            ->findOrFail($this->testId);

        // Safety guard — re-check all transcripts present
        $audioFiles    = $test->audioFiles()->orderBy('created_at')->get();
        $testQuestions = $test->testQuestions()->orderBy('part')->with('question')->get();

        $missing = $audioFiles->filter(fn($a) => empty($a->transcript));
        if ($missing->isNotEmpty()) {
            throw new \RuntimeException(
                "SpeakingScoreJob: {$missing->count()} transcript(s) still missing for test {$this->testId}"
            );
        }

        // Build combined transcript
        $combinedTranscript = $this->buildCombinedTranscript($audioFiles, $testQuestions);

        Log::info("SpeakingScoreJob: sending to AI scorer", [
            'test_id'           => $this->testId,
            'transcript_length' => strlen($combinedTranscript),
        ]);

        $scoring = $scoringService->scoreSpeaking($combinedTranscript);

        if (!$scoring) {
            throw new \RuntimeException("AI scoring returned null for test {$this->testId}");
        }

        $overallBand       = $scoringService->calculateOverallBand($scoring);
        $fillerAnalysis    = $this->analyzeFillers($combinedTranscript);
        $repetitionAnalysis = $this->analyzeRepetitions($combinedTranscript);

        $this->saveScores($test, $scoring, $overallBand, $fillerAnalysis, $repetitionAnalysis);

        // Notify user via email
        $test->load('user', 'testScores');
        Mail::to($test->user)->queue(new SpeakingScoreReadyMail($test));

        Log::info("SpeakingScoreJob: completed", [
            'test_id'      => $this->testId,
            'overall_band' => $overallBand,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SpeakingScoreJob: permanently failed", [
            'test_id' => $this->testId,
            'error'   => $e->getMessage(),
        ]);

        $test = Test::find($this->testId);
        if ($test) {
            $test->update([
                'status'   => 'failed',
                'feedback' => 'AI evaluation failed. Please try again.',
            ]);
        }
    }

    // ── Helpers (extracted from SpeakingEvaluationJob) ───────────────────────

    private function buildCombinedTranscript($audioFiles, $testQuestions): string
    {
        $parts = [];
        foreach ($audioFiles as $index => $audioFile) {
            $partNumber  = $index + 1;
            $question    = $testQuestions->where('part', $partNumber)->first();
            $questionText = $question ? $question->question->title : "Part {$partNumber}";
            $parts[] = "=== Part {$partNumber}: {$questionText} ===\n{$audioFile->transcript}";
        }
        return implode("\n\n", $parts);
    }

    private function saveScores(Test $test, array $scoring, float $overallBand, array $fillerAnalysis, array $repetitionAnalysis): void
    {
        $criteriaMap = [
            'fluency'      => 'fluency_coherence',
            'lexical'      => 'lexical_resource',
            'grammar'      => 'grammatical_range_accuracy',
            'pronunciation' => 'pronunciation',
        ];

        $test->testScores()->delete();

        foreach ($criteriaMap as $scoreKey => $criteria) {
            TestScore::create([
                'test_id'    => $test->id,
                'criteria'   => $criteria,
                'band_score' => $scoring[$scoreKey] ?? 0,
                'comments'   => is_array($scoring['examiner_comments'] ?? null)
                    ? implode("\n", $scoring['examiner_comments'])
                    : ($scoring['feedback'] ?? ''),
            ]);
        }

        $test->update([
            'overall_band' => $overallBand,
            'score'        => $overallBand,
            'status'       => 'completed',
            'feedback'     => $scoring['feedback'] ?? ($scoring['examiner_comments'][0] ?? 'Evaluation completed.'),
            'metadata'     => json_encode(array_merge(
                json_decode($test->metadata ?? '{}', true) ?? [],
                [
                    'band_confidence_range' => $scoring['band_confidence_range'] ?? null,
                    'examiner_comments'     => $scoring['examiner_comments'] ?? [],
                    'filler_analysis'       => $fillerAnalysis,
                    'repetition_analysis'   => $repetitionAnalysis,
                ]
            )),
            'completed_at' => now(),
        ]);
    }

    private function analyzeFillers(string $transcript): array
    {
        $patterns = [
            'um'      => '/\b(um+|uh+)\b/i',
            'like'    => '/\blike\b/i',
            'you know' => '/\byou know\b/i',
            'actually' => '/\bactually\b/i',
            'basically' => '/\bbasically\b/i',
            'sort of'  => '/\b(sort|kind) of\b/i',
        ];
        $counts = []; $total = 0;
        foreach ($patterns as $name => $pattern) {
            preg_match_all($pattern, $transcript, $m);
            $c = count($m[0]);
            if ($c > 0) { $counts[$name] = $c; $total += $c; }
        }
        $words = str_word_count($transcript);
        return [
            'total'     => $total,
            'density'   => $words > 0 ? round(($total / $words) * 100, 2) : 0,
            'breakdown' => $counts,
            'word_count' => $words,
        ];
    }

    private function analyzeRepetitions(string $transcript): array
    {
        $stop = ['the','a','an','and','or','but','in','on','at','to','for','of','with',
                 'by','from','as','is','was','are','were','be','been','have','has','had',
                 'do','does','did','will','would','should','could','can','i','you','he',
                 'she','it','we','they','this','that','these','those'];
        preg_match_all('/\b\w+\b/', strtolower($transcript), $m);
        $content = array_filter($m[0], fn($w) => !in_array($w, $stop) && strlen($w) > 3);
        $freq    = array_count_values($content);
        $overused = array_filter($freq, fn($c) => $c > 3);
        arsort($overused);
        return [
            'overused_words'      => array_slice($overused, 0, 5, true),
            'unique_words'        => count(array_unique($content)),
            'total_content_words' => count($content),
        ];
    }
}
