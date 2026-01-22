<?php

namespace App\Jobs;

use App\Models\Test;
use App\Models\AudioFile;
use App\Models\TestScore;
use App\Services\TranscriptionService;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SpeakingEvaluationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $testId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $testId)
    {
        $this->testId = $testId;
    }

    /**
     * Execute the job.
     */
    public function handle(TranscriptionService $transcriptionService, ScoringService $scoringService): void
    {
        try {
            $test = Test::with(['audioFiles', 'testQuestions.question'])
                ->findOrFail($this->testId);

            // Verify this is a speaking test with 3 audio files
            if ($test->type !== 'speaking') {
                Log::error('SpeakingEvaluationJob called for non-speaking test', [
                    'test_id' => $this->testId,
                    'type' => $test->type,
                ]);
                $this->markTestAsFailed($test, 'Invalid test type');
                return;
            }

            $audioFiles = $test->audioFiles()->orderBy('created_at')->get();

            if ($audioFiles->count() < 3) {
                Log::error('SpeakingEvaluationJob called with insufficient audio files', [
                    'test_id' => $this->testId,
                    'count' => $audioFiles->count(),
                ]);
                $this->markTestAsFailed($test, 'Insufficient audio files');
                return;
            }

            // Get test questions ordered by part
            $testQuestions = $test->testQuestions()->orderBy('part')->with('question')->get();

            // Step 1: Transcribe all audio files
            $transcripts = [];
            foreach ($audioFiles as $index => $audioFile) {
                Log::info('Transcribing audio file', [
                    'test_id' => $this->testId,
                    'audio_file_id' => $audioFile->id,
                    'part' => $index + 1,
                ]);

                $transcript = $transcriptionService->transcribe($audioFile->file_url);

                if (!$transcript) {
                    Log::error('Transcription failed for audio file', [
                        'test_id' => $this->testId,
                        'audio_file_id' => $audioFile->id,
                    ]);
                    $this->markTestAsFailed($test, 'Transcription failed');
                    return;
                }

                // Save transcript to audio file
                $audioFile->update(['transcript' => $transcript]);
                $transcripts[] = $transcript;
            }

            // Step 2: Concatenate transcripts with part labels
            $combinedTranscript = $this->buildCombinedTranscript($transcripts, $testQuestions);

            // Step 3: Get AI scoring
            Log::info('Requesting AI scoring', ['test_id' => $this->testId]);

            $scoring = $scoringService->scoreSpeaking($combinedTranscript);

            if (!$scoring) {
                Log::error('AI scoring failed', ['test_id' => $this->testId]);
                $this->markTestAsFailed($test, 'AI scoring failed');
                return;
            }

            // Step 4: Calculate overall band
            $overallBand = $scoringService->calculateOverallBand($scoring);

            // Step 5: Analyze filler words and repetitions
            $fillerAnalysis = $this->analyzeFillers($combinedTranscript);
            $repetitionAnalysis = $this->analyzeRepetitions($combinedTranscript);

            // Step 6: Save scores to database
            $this->saveScores($test, $scoring, $overallBand, $fillerAnalysis, $repetitionAnalysis);

            Log::info('Speaking evaluation completed successfully', [
                'test_id' => $this->testId,
                'overall_band' => $overallBand,
            ]);

        } catch (\Exception $e) {
            Log::error('SpeakingEvaluationJob failed', [
                'test_id' => $this->testId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $test = Test::find($this->testId);
            if ($test) {
                $this->markTestAsFailed($test, 'Evaluation job failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Build combined transcript with part labels
     */
    protected function buildCombinedTranscript(array $transcripts, $testQuestions): string
    {
        $parts = [];
        
        foreach ($transcripts as $index => $transcript) {
            $partNumber = $index + 1;
            $question = $testQuestions->where('part', $partNumber)->first();
            $questionText = $question ? $question->question->title : "Part {$partNumber}";
            
            $parts[] = "=== Part {$partNumber}: {$questionText} ===\n{$transcript}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * Save scores to database
     */
    protected function saveScores(Test $test, array $scoring, float $overallBand, array $fillerAnalysis = [], array $repetitionAnalysis = []): void
    {
        // Map scoring fields to test_scores criteria
        $criteriaMap = [
            'fluency' => 'fluency_coherence',
            'lexical' => 'lexical_resource',
            'grammar' => 'grammatical_range_accuracy',
            'pronunciation' => 'pronunciation',
        ];

        // Delete existing scores
        $test->testScores()->delete();

        // Create new scores
        foreach ($criteriaMap as $scoreKey => $criteria) {
            TestScore::create([
                'test_id' => $test->id,
                'criteria' => $criteria,
                'band_score' => $scoring[$scoreKey],
                'comments' => is_array($scoring['examiner_comments'] ?? null)
                    ? implode("\n", $scoring['examiner_comments'])
                    : ($scoring['feedback'] ?? ''),
            ]);
        }

        // Update test with overall band and feedback
        $test->update([
            'overall_band' => $overallBand,
            'score' => $overallBand,
            'feedback' => $scoring['feedback'] ?? ($scoring['examiner_comments'][0] ?? 'Evaluation completed.'),
            'status' => 'completed',
            'metadata' => json_encode(array_merge(
                json_decode($test->metadata, true) ?? [],
                [
                    'band_confidence_range' => $scoring['band_confidence_range'] ?? (($overallBand - 0.5) . ' - ' . ($overallBand + 0.5)),
                    'fillers_detected' => $scoring['metadata']['fillers_detected'] ?? 0,
                    'repetition_flags' => $scoring['metadata']['repetition_flags'] ?? false,
                    'pronunciation_notes' => $scoring['metadata']['pronunciation_notes'] ?? [],
                    'examiner_comments' => $scoring['examiner_comments'] ?? [],
                    'filler_analysis' => $fillerAnalysis,
                    'repetition_analysis' => $repetitionAnalysis,
                ]
            )),
        ]);
    }

    /**
     * Analyze filler words in transcript
     */
    protected function analyzeFillers(string $transcript): array
    {
        $fillerPatterns = [
            'um' => '/\b(um|umm|ummm)\b/i',
            'uh' => '/\b(uh|uhh|uhhh)\b/i',
            'like' => '/\b(like)\b/i',
            'you know' => '/\b(you know)\b/i',
            'actually' => '/\b(actually)\b/i',
            'basically' => '/\b(basically)\b/i',
            'sort of' => '/\b(sort of|kind of)\b/i',
        ];

        $fillerCounts = [];
        $totalFillers = 0;

        foreach ($fillerPatterns as $name => $pattern) {
            preg_match_all($pattern, $transcript, $matches);
            $count = count($matches[0]);
            if ($count > 0) {
                $fillerCounts[$name] = $count;
                $totalFillers += $count;
            }
        }

        // Calculate filler density (fillers per 100 words)
        $wordCount = str_word_count($transcript);
        $density = $wordCount > 0 ? round(($totalFillers / $wordCount) * 100, 2) : 0;

        return [
            'total' => $totalFillers,
            'density' => $density,
            'breakdown' => $fillerCounts,
            'word_count' => $wordCount,
        ];
    }

    /**
     * Analyze word repetitions in transcript
     */
    protected function analyzeRepetitions(string $transcript): array
    {
        // Common stop words to exclude
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
                      'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be', 
                      'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 
                      'would', 'should', 'could', 'may', 'might', 'must', 'can', 'i', 'you', 
                      'he', 'she', 'it', 'we', 'they', 'this', 'that', 'these', 'those'];

        // Extract words
        preg_match_all('/\b\w+\b/', strtolower($transcript), $matches);
        $words = $matches[0];

        // Filter out stop words and short words
        $contentWords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 3;
        });

        // Count frequencies
        $wordCounts = array_count_values($contentWords);
        
        // Filter words used more than 3 times
        $overusedWords = array_filter($wordCounts, function($count) {
            return $count > 3;
        });

        // Sort by frequency
        arsort($overusedWords);

        // Take top 5
        $topOverused = array_slice($overusedWords, 0, 5, true);

        return [
            'overused_words' => $topOverused,
            'unique_words' => count(array_unique($contentWords)),
            'total_content_words' => count($contentWords),
        ];
    }

    /**
     * Mark test as failed
     */
    protected function markTestAsFailed(Test $test, string $reason): void
    {
        $test->update([
            'status' => 'failed',
            'feedback' => 'Evaluation failed: ' . $reason,
        ]);
    }
}

