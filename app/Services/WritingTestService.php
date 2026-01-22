<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestScore;
use App\Repositories\WritingRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WritingTestService
{
    protected $writingRepo;
    protected $scoringService;

    public function __construct(WritingRepository $writingRepo, ScoringService $scoringService)
    {
        $this->writingRepo = $writingRepo;
        $this->scoringService = $scoringService;
    }

    /**
     * Create a new writing test for a user
     */
    public function createWritingTest($userId, $question, $testType = 'academic')
    {
        DB::beginTransaction();
        try {
            // Create main test record
            $test = Test::create([
                'user_id' => $userId,
                'type' => 'writing',
                'category' => $question->category,
                'test_type' => $testType,
                'status' => 'in_progress',
                'started_at' => now(),
                'metadata' => json_encode([
                    'task_type' => $this->determineTaskType($question->category),
                    'time_limit' => $this->getTimeLimit($question->category),
                    'word_limit' => $this->getWordLimit($question->category),
                ]),
            ]);

            // Link question to test
            TestQuestion::create([
                'test_id' => $test->id,
                'question_id' => $question->id,
                'part' => str_contains($question->category, 'task2') ? 2 : 1,
            ]);


            DB::commit();
            return $test;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create writing test', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'question_id' => $question->id,
            ]);
            throw $e;
        }
    }

    /**
     * Submit and evaluate a writing test
     */
    public function submitWritingTest($testId, $answer)
    {
        DB::beginTransaction();
        try {
            $test = Test::findOrFail($testId);
            
            // Get question through the test_questions relationship
            $testQuestion = TestQuestion::where('test_id', $testId)->first();
            if (!$testQuestion) {
                throw new \Exception('Test question relationship not found');
            }
            
            $question = $this->writingRepo->getQuestionById($testQuestion->question_id);
            if (!$question) {
                throw new \Exception('Question not found');
            }

            // Validate word count
            $wordCount = str_word_count($answer);
            $minWords = $this->getWordLimit($question->category);
            
            if ($wordCount < $minWords) {
                Log::warning('Writing test submitted with insufficient words', [
                    'test_id' => $testId,
                    'word_count' => $wordCount,
                    'required' => $minWords,
                ]);
            }

            // Score the writing using AI
            $scoring = $this->scoringService->scoreWriting($answer, $question);
            if (!$scoring) {
                throw new \Exception('Failed to score writing test');
            }

            // Calculate overall band
            $overallBand = $this->scoringService->calculateOverallBand($scoring);

            // Store individual scores in test_scores table
            $criteria = [
                'task_achievement' => $scoring['task_achievement'],
                'coherence_cohesion' => $scoring['coherence_cohesion'],
                'lexical_resource' => $scoring['lexical_resource'],
                'grammar' => $scoring['grammar'],
            ];

            foreach ($criteria as $criteriaName => $bandScore) {
                TestScore::create([
                    'test_id' => $test->id,
                    'criteria' => $criteriaName,
                    'band_score' => $bandScore,
                    'comments' => is_array($scoring['examiner_comments'] ?? null)
                        ? implode("\n", $scoring['examiner_comments'])
                        : ($scoring['feedback'] ?? ''),
                ]);
            }

            // Prepare result data
            $result = [
                'task_achievement' => $scoring['task_achievement'],
                'coherence_cohesion' => $scoring['coherence_cohesion'],
                'lexical_resource' => $scoring['lexical_resource'],
                'grammar' => $scoring['grammar'],
                'overall_band' => $overallBand,
                'feedback' => $scoring['feedback'] ?? ($scoring['examiner_comments'][0] ?? ''),
                'strengths' => $scoring['strengths'] ?? [],
                'improvements' => $scoring['improvements'] ?? [],
                'errors' => $scoring['errors'] ?? [],
                'band_explanations' => $scoring['band_explanations'] ?? [],
                'summary' => $scoring['summary'] ?? [],
                'word_count' => $wordCount,
                'original_answer' => $answer,
                'highlightedEssay' => $scoring['annotated_answer_html'] ?? null,
                
                // New examiner fields
                'band_confidence_range' => $scoring['band_confidence_range'] ?? (($overallBand - 0.5) . ' - ' . ($overallBand + 0.5)),
                'band_9_rewrite' => $scoring['band_9_rewrite'] ?? '',
                'topic_vocabulary' => $scoring['topic_vocabulary'] ?? [],
                'examiner_comments' => $scoring['examiner_comments'] ?? [],
                'error_summary' => $scoring['error_summary'] ?? [],
            ];

            // Update test record
            $test->update([
                'status' => 'completed',
                'answer' => $answer,
                'score' => $overallBand,
                'overall_band' => $overallBand,
                'result' => json_encode($result),
                'completed_at' => now(),
                'duration_seconds' => now()->diffInSeconds($test->started_at),
                'metadata' => json_encode(array_merge(
                    json_decode($test->metadata, true) ?? [],
                    [
                        'word_count' => $wordCount,
                        'submitted_at' => now()->toISOString(),
                        'band_confidence_range' => $result['band_confidence_range'],
                        'band_9_rewrite' => $result['band_9_rewrite'],
                        'topic_vocabulary' => $result['topic_vocabulary'],
                        'examiner_comments' => $result['examiner_comments'],
                        'error_summary' => $result['error_summary'],
                    ]
                )),
            ]);

            DB::commit();

            return [
                'success' => true,
                'test' => $test->fresh(),
                'result' => $result,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Writing test submission failed', [
                'test_id' => $testId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process your writing test. Please try again.',
            ];
        }
    }

    /**
     * Get writing test results with processed errors for highlighting
     */
    public function getWritingTestResults($testId)
{
    $test = Test::with(['testQuestions.question'])->findOrFail($testId);
    $result = json_decode($test->result, true);

    $original = $result['original_answer'];
    $rawErrors = $result['errors'] ?? [];
$errors = [];

foreach ($rawErrors as $i => $e) {
    $errors[] = [
        'id'          => $e['id'] ?? 'error_' . ($i + 1),
        'text'        => $e['text'] ?? '',
        'type'        => $e['type'] ?? 'grammar',
        'category'    => $e['category'] ?? ($e['type'] ?? 'grammar'),
        'severity'    => $e['severity'] ?? 'medium',
        'correction'  => $e['correction'] ?? '',
        'explanation' => $e['explanation'] ?? '',
    ];
}


    // ✅ SERVER-SIDE HIGHLIGHTING (AUTHORITATIVE)
    [$highlightedEssay, $positioned, $unpositioned] =
        $this->processErrorsForHighlighting($original, $errors);

    return [
        'test' => $test,
        'scores' => [
            'task_achievement'   => $result['task_achievement'],
            'coherence_cohesion' => $result['coherence_cohesion'],
            'lexical_resource'   => $result['lexical_resource'],
            'grammar'            => $result['grammar'],
            'overall_band'       => $result['overall_band'],
            'band_confidence_range' => $result['band_confidence_range'] ?? null,
        ],
        'feedback' => $result['feedback'],
        'examiner_comments' => $result['examiner_comments'] ?? [],
        'band_explanations' => $result['band_explanations'] ?? [],
        'band_9_rewrite' => $result['band_9_rewrite'] ?? '',
        'topic_vocabulary' => $result['topic_vocabulary'] ?? [],
        'error_summary' => $result['error_summary'] ?? [],
        'strengths' => $result['strengths'] ?? [],
        'improvements' => $result['improvements'] ?? [],
        'errors' => $positioned,
        'unpositioned_errors' => $unpositioned,
        'highlightedEssay' => $highlightedEssay,
        'word_count' => $result['word_count'],
        'task_info' => $this->getTaskInfo($test->category),
    ];
}


// File: app/Services/WritingTestService.php

// Find the existing processErrorsForHighlighting method and REPLACE it completely:

protected function processErrorsForHighlighting(string $essay, array $errors): array
{
    $positioned = [];
    $unpositioned = [];

    // Start with escaped essay text
    $html = e($essay);
    
    // 🛡️ UTMOST LEVEL: We must find positions first and sort them, 
    // because AI might return errors out of order.
    $matches = [];
    foreach ($errors as $error) {
        $text = trim($error['text']);
        if ($text === '') continue;

        $escapedText = e($text);
        $found = false;
        
        $strategies = [
            // Strategy 1: Exact escaped match
            fn($t, $h) => preg_match('/' . preg_quote($t, '/') . '/i', $h, $m, PREG_OFFSET_CAPTURE) ? $m : null,
            
            // Strategy 2: Flexible whitespace
            fn($t, $h) => preg_match('/' . preg_replace('/\s+/', '\s+', preg_quote($t, '/')) . '/i', $h, $m, PREG_OFFSET_CAPTURE) ? $m : null,
            
            // Strategy 3: Flexible punctuation & entities
            fn($t, $h) => preg_match('/' . preg_replace('/(&#039;|&quot;|[.,!?;:])+/', '.*?(\s+|&#039;|&quot;|[.,!?;:])*.*?', preg_quote($t, '/')) . '/i', $h, $m, PREG_OFFSET_CAPTURE) ? $m : null,
            
            // Strategy 4: Partial match (First 5 words)
            function($t, $h) {
                $words = preg_split('/\s+/', $t, 6);
                if (count($words) < 3) return null;
                $anchor = implode('\s+', array_map(fn($w) => preg_quote($w, '/'), array_slice($words, 0, min(5, count($words)))));
                return preg_match('/' . $anchor . '/i', $h, $m, PREG_OFFSET_CAPTURE) ? $m : null;
            },
            
            // Strategy 5: Ultra-resilient (Fuzzy)
            function($t, $h) {
                $clean = preg_replace('/[^a-z0-9]/i', '', $t);
                if (strlen($clean) < 10) return null;
                $regex = implode('.*?', str_split($clean));
                return preg_match('/' . $regex . '/i', $h, $m, PREG_OFFSET_CAPTURE) ? $m : null;
            },
            
            // Strategy 6: Word-by-word aggressive matching
            function($t, $h) {
                $words = preg_split('/\s+/', trim($t));
                if (count($words) < 2) return null;
                // Try to find any 2 consecutive words from the error
                for ($i = 0; $i < count($words) - 1; $i++) {
                    $pattern = preg_quote($words[$i], '/') . '\s+' . preg_quote($words[$i+1], '/');
                    if (preg_match('/' . $pattern . '/i', $h, $m, PREG_OFFSET_CAPTURE)) {
                        return $m;
                    }
                }
                return null;
            }
        ];

        foreach ($strategies as $strategy) {
            $m = $strategy($escapedText, $html);
            if ($m) {
                $matches[] = [
                    'error' => $error,
                    'pos' => $m[0][1],
                    'len' => strlen($m[0][0]),
                    'matched_text' => $m[0][0]
                ];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $unpositioned[] = $error;
        }
    }

    // Sort matches by position to avoid overlapping logic issues
    usort($matches, fn($a, $b) => $a['pos'] <=> $b['pos']);

    // Build the highlighted HTML
    $outputHtml = '';
    $lastPos = 0;
    
    foreach ($matches as $match) {
        $error = $match['error'];
        $pos = $match['pos'];
        $len = $match['len'];

        // If this match overlaps with previous one, skip it
        if ($pos < $lastPos) {
            $unpositioned[] = $error;
            continue;
        }

        // Add text before the match
        $outputHtml .= substr($html, $lastPos, $pos - $lastPos);

        // Add the highlighted span
        $colorClass = $this->getErrorColorClass($error['category'] ?? $error['type'] ?? 'Grammar');
        $outputHtml .= sprintf(
            '<span data-error-id="%s" class="error %s error-severity-%s" title="%s">%s</span>',
            e($error['id'] ?? uniqid()),
            $colorClass,
            e($error['severity'] ?? 'medium'),
            e($error['explanation'] ?? ''),
            substr($html, $pos, $len)
        );

        $lastPos = $pos + $len;
        $positioned[] = $error;
    }

    // Add remaining text
    $outputHtml .= substr($html, $lastPos);

    // Convert to paragraphs instead of using nl2br
    $paragraphs = preg_split('/\n\s*\n/', $outputHtml);
    $formattedHtml = '';
    foreach ($paragraphs as $para) {
        $para = trim($para);
        if (!empty($para)) {
            $para = preg_replace('/\n/', ' ', $para);
            $formattedHtml .= '<p class="mb-4">' . $para . '</p>';
        }
    }

    return [$formattedHtml, $positioned, $unpositioned];
}

// ADD this new method right after processErrorsForHighlighting:

/**
 * Map error category to CSS color class for utmost diagnostic clarity
 */
protected function getErrorColorClass(string $category): string
{
    // Normalize category
    $category = strtolower(trim($category));
    
    // 1. Grammar-related errors (RED - Critical Accuracy)
    $grammarErrors = [
        'grammar', 'tense', 'article', 'preposition', 
        'subject_verb_agreement', 'verb_form', 'plural',
        'pronoun', 'modal', 'conditional', 'verb', 'syntax',
        'agreement', 'word_order'
    ];
    
    // 2. Vocabulary-related errors (YELLOW/ORANGE - Lexical Resource)
    $vocabularyErrors = [
        'vocabulary', 'word_choice', 'word_form', 
        'collocation', 'spelling', 'redundancy',
        'register', 'formality', 'lexical', 'idiomatic',
        'connotation', 'phrasal_verb'
    ];
    
    // 3. Cohesion-related errors (GREEN - Flow and Logic)
    $cohesionErrors = [
        'cohesion', 'clarity', 'coherence', 'linking',
        'paragraph', 'structure', 'transition', 'reference',
        'logic', 'flow', 'organisation', 'task_response'
    ];

    // 4. Punctuation-related errors (BLUE - Technical Detail)
    $punctuationErrors = [
        'punctuation', 'capitalization', 'comma', 'full_stop',
        'semi_colon', 'apostrophe', 'quotes', 'hyphen'
    ];
    
    if (in_array($category, $grammarErrors)) {
        return 'error-grammar';
    } elseif (in_array($category, $vocabularyErrors)) {
        return 'error-vocabulary';
    } elseif (in_array($category, $cohesionErrors)) {
        return 'error-cohesion';
    } elseif (in_array($category, $punctuationErrors)) {
        return 'error-punctuation';
    }
    
    // Default to grammar (red) if truly unknown
    return 'error-grammar';
}



    // ... (keep all other existing methods: processErrorsForHighlighting, findTextPosition, 
    // determineTaskType, getTimeLimit, getWordLimit, getTaskInfo, getBandDescription, 
    // generateHighlightedEssay - they remain the same)
    

  

  

    /**
     * Resolve an error position safely using multiple matching strategies.
     */

    protected function trimPunctuation(string $text): string
    {
        $text = preg_replace('/^\p{P}+/u', '', $text);
        $text = preg_replace('/\p{P}+$/u', '', $text);
        return trim($text);
    }



    protected function determineTaskType($category)
    {
        if (str_contains($category, 'academic_task1')) {
            return 'task1_academic';
        } elseif (str_contains($category, 'academic_task2')) {
            return 'task2_academic';
        } elseif (str_contains($category, 'general_task1')) {
            return 'task1_general';
        } elseif (str_contains($category, 'general_task2')) {
            return 'task2_general';
        }
        
        return 'unknown';
    }

    protected function getTimeLimit($category)
    {
        if (str_contains($category, 'task1')) {
            return 20;
        }
        return 40;
    }

    protected function getWordLimit($category)
    {
        if (str_contains($category, 'task1')) {
            return 150;
        }
        return 250;
    }

    protected function getTaskInfo($category)
    {
        $taskType = $this->determineTaskType($category);
        
        $info = [
            'type' => $taskType,
            'time_limit' => $this->getTimeLimit($category),
            'word_limit' => $this->getWordLimit($category),
        ];

        switch ($taskType) {
            case 'task1_academic':
                $info['title'] = 'Academic Task 1';
                $info['description'] = 'Describe visual information (graphs, charts, diagrams, tables)';
                $info['requirements'] = [
                    'Write at least 150 words',
                    'Include an overview of main trends',
                    'Select and report key features',
                    'Make relevant comparisons',
                ];
                break;

            case 'task2_academic':
                $info['title'] = 'Academic Task 2';
                $info['description'] = 'Write an essay in response to a point of view, argument or problem';
                $info['requirements'] = [
                    'Write at least 250 words',
                    'Present a clear position',
                    'Support ideas with examples',
                    'Organize ideas logically',
                ];
                break;

            case 'task1_general':
                $info['title'] = 'General Training Task 1';
                $info['description'] = 'Write a letter (formal, semi-formal, or informal)';
                $info['requirements'] = [
                    'Write at least 150 words',
                    'Address all bullet points',
                    'Use appropriate tone',
                    'Follow letter format',
                ];
                break;

            case 'task2_general':
                $info['title'] = 'General Training Task 2';
                $info['description'] = 'Write an essay in response to a point of view, argument or problem';
                $info['requirements'] = [
                    'Write at least 250 words',
                    'Present a clear position',
                    'Support ideas with examples',
                    'Organize ideas logically',
                ];
                break;

            default:
                $info['title'] = 'Unknown Task';
                $info['description'] = '';
                $info['requirements'] = [];
        }

        return $info;
    }

    public function getBandDescription($score)
    {
        $descriptions = [
            9.0 => 'Expert User - Full operational command',
            8.5 => 'Very Good User - Fully operational with occasional inaccuracies',
            8.0 => 'Very Good User - Fully operational command',
            7.5 => 'Good User - Operational command with occasional inaccuracies',
            7.0 => 'Good User - Operational command',
            6.5 => 'Competent User - Generally effective command',
            6.0 => 'Competent User - Effective command',
            5.5 => 'Modest User - Partial command',
            5.0 => 'Modest User - Partial command',
            4.5 => 'Limited User - Basic competence',
            4.0 => 'Limited User - Basic competence in familiar situations',
            3.5 => 'Extremely Limited User - Conveys basic meaning',
            3.0 => 'Extremely Limited User - Very limited understanding',
        ];

        return $descriptions[$score] ?? 'Not Assessed';
    }

    protected function debugLog(string $message, array $context = []): void
    {
        if (env('IELTS_DEBUG', false)) {
            Log::debug($message, $context);
        }
    }
}