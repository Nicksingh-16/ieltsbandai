<?php

/**
 * AI Scoring Calibration Script
 * 
 * This script runs a comprehensive set of test essays through the AI scoring system
 * and generates a detailed report comparing expected vs actual results.
 * 
 * Usage: php tests/scripts/calibrate_ai_scoring.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoringService;

class ScoringCalibrator
{
    protected $scoringService;
    protected $results = [];
    
    public function __construct()
    {
        $this->scoringService = app(ScoringService::class);
    }
    
    public function run()
    {
        echo "=== AI Scoring Calibration Report ===\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $testCases = $this->getTestCases();
        
        foreach ($testCases as $index => $testCase) {
            echo "\n" . str_repeat('=', 80) . "\n";
            echo "Test Case #" . ($index + 1) . ": {$testCase['name']}\n";
            echo str_repeat('=', 80) . "\n";
            
            $this->runTestCase($testCase);
        }
        
        $this->printSummary();
    }
    
    protected function runTestCase($testCase)
    {
        $question = (object) [
            'content' => $testCase['question'],
            'category' => $testCase['category'],
            'metadata' => json_encode($testCase['metadata'] ?? [])
        ];
        
        echo "\nQuestion: {$testCase['question']}\n";
        echo "Expected Band: {$testCase['expected_band']}\n";
        echo "Known Errors: " . count($testCase['known_errors']) . "\n\n";
        
        $result = $this->scoringService->scoreWriting($testCase['answer'], $question);
        
        if (!$result) {
            echo "❌ FAILED: Scoring returned null\n";
            $this->results[] = ['status' => 'failed', 'reason' => 'null_result'];
            return;
        }
        
        $actualBand = $result['overall_band'] ?? 0;
        $actualErrors = $result['errors'] ?? [];
        
        echo "Actual Band: {$actualBand}\n";
        echo "Detected Errors: " . count($actualErrors) . "\n\n";
        
        // Validate band score
        $bandDiff = abs($actualBand - $testCase['expected_band']);
        $bandOk = $bandDiff <= 0.5;
        
        echo ($bandOk ? "✅" : "❌") . " Band Score: ";
        echo $bandOk ? "PASS (within ±0.5)\n" : "FAIL (difference: {$bandDiff})\n";
        
        // Validate false positives
        $falsePositives = $this->findFalsePositives($actualErrors, $testCase['answer']);
        echo ($falsePositives === 0 ? "✅" : "❌") . " False Positives: {$falsePositives}\n";
        
        if ($falsePositives > 0) {
            echo "\n⚠️  False Positive Errors (text not found in answer):\n";
            foreach ($actualErrors as $error) {
                if (stripos($testCase['answer'], $error['text']) === false) {
                    echo "  - [{$error['type']}] \"{$error['text']}\"\n";
                }
            }
        }
        
        // Validate known errors are detected
        $detectedKnownErrors = $this->countDetectedKnownErrors($actualErrors, $testCase['known_errors']);
        $recall = count($testCase['known_errors']) > 0 
            ? ($detectedKnownErrors / count($testCase['known_errors'])) * 100 
            : 100;
        
        echo ($recall >= 70 ? "✅" : "⚠️ ") . " Known Error Detection: {$detectedKnownErrors}/" . count($testCase['known_errors']) . " ({$recall}%)\n";
        
        // Check for specific issues mentioned in test case
        if (isset($testCase['should_not_flag'])) {
            foreach ($testCase['should_not_flag'] as $shouldNotFlag) {
                $flagged = $this->isTextFlagged($actualErrors, $shouldNotFlag);
                echo ($flagged ? "❌" : "✅") . " Should NOT flag: \"{$shouldNotFlag}\" - ";
                echo $flagged ? "FAILED (incorrectly flagged)\n" : "PASS\n";
            }
        }
        
        // Detailed error breakdown
        echo "\nError Breakdown by Type:\n";
        $errorsByType = [];
        foreach ($actualErrors as $error) {
            $type = $error['type'] ?? 'Unknown';
            $errorsByType[$type] = ($errorsByType[$type] ?? 0) + 1;
        }
        foreach ($errorsByType as $type => $count) {
            echo "  - {$type}: {$count}\n";
        }
        
        $this->results[] = [
            'name' => $testCase['name'],
            'status' => ($bandOk && $falsePositives === 0) ? 'passed' : 'failed',
            'band_diff' => $bandDiff,
            'false_positives' => $falsePositives,
            'recall' => $recall
        ];
    }
    
    protected function findFalsePositives($errors, $answer)
    {
        $count = 0;
        foreach ($errors as $error) {
            $errorText = trim($error['text'] ?? '');
            if (empty($errorText)) continue;
            
            // Check if error text exists in answer (case-insensitive)
            if (stripos($answer, $errorText) === false) {
                // Try with normalized whitespace
                $normalizedError = preg_replace('/\s+/', ' ', $errorText);
                $normalizedAnswer = preg_replace('/\s+/', ' ', $answer);
                
                if (stripos($normalizedAnswer, $normalizedError) === false) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    protected function countDetectedKnownErrors($actualErrors, $knownErrors)
    {
        $detected = 0;
        foreach ($knownErrors as $knownError) {
            foreach ($actualErrors as $actualError) {
                if (stripos($actualError['text'], $knownError) !== false) {
                    $detected++;
                    break;
                }
            }
        }
        return $detected;
    }
    
    protected function isTextFlagged($errors, $text)
    {
        foreach ($errors as $error) {
            if (stripos($error['text'], $text) !== false) {
                return true;
            }
        }
        return false;
    }
    
    protected function printSummary()
    {
        echo "\n\n" . str_repeat('=', 80) . "\n";
        echo "CALIBRATION SUMMARY\n";
        echo str_repeat('=', 80) . "\n\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'passed'));
        $total = count($this->results);
        
        echo "Tests Passed: {$passed}/{$total} (" . round(($passed/$total)*100, 1) . "%)\n\n";
        
        $avgFalsePositives = array_sum(array_column($this->results, 'false_positives')) / $total;
        $avgRecall = array_sum(array_column($this->results, 'recall')) / $total;
        
        echo "Average False Positives per Test: " . round($avgFalsePositives, 2) . "\n";
        echo "Average Known Error Recall: " . round($avgRecall, 1) . "%\n\n";
        
        echo ($avgFalsePositives === 0 ? "✅" : "❌") . " Zero False Positives: ";
        echo $avgFalsePositives === 0 ? "PASS\n" : "FAIL\n";
        
        echo ($avgRecall >= 85 ? "✅" : "⚠️ ") . " Recall >= 85%: ";
        echo $avgRecall >= 85 ? "PASS\n" : "NEEDS IMPROVEMENT\n";
        
        echo "\n" . str_repeat('=', 80) . "\n";
    }
    
    protected function getTestCases()
    {
        return [
            [
                'name' => 'Task 1 - Coffee Production (Band 6.0) - False Positive Test',
                'category' => 'writing_academic_task1',
                'question' => 'The diagram below shows how coffee is produced and prepared for sale.',
                'expected_band' => 6.0,
                'answer' => "The diagram illustrates the process by which coffee is produced and prepared for sale.\n\n" .
                           "Overall, coffee production is a linear, multi-stage process that begins with harvesting ripe coffee beans and ends with packaging the final product for commercial distribution. The procedure involves both agricultural and industrial stages, including drying, roasting, grinding, and packing.\n\n" .
                           "At the initial stage, ripe coffee cherries are harvested from coffee plants, usually by hand or machine. These cherries are then processed to extract the coffee beans, which are subsequently dried under the sun for several days to remove excess moisture. Once the beans are fully dried, they are packed into large sacks and transported to a processing factory.\n\n" .
                           "In the factory, the dried beans undergo roasting at high temperatures, which enhances their flavor and aroma. After roasting, the beans are rapidly cooled to stabilize their quality. The roasted beans are then ground into fine coffee powder, depending on the desired consistency.\n\n" .
                           "In the final stage, the ground coffee is packed into jars or packets, sealed, and labeled. These finished products are then ready to be distributed to shops and sold to consumers.",
                'known_errors' => [
                    // Intentionally minimal - this is a decent Band 6 essay
                ],
                'should_not_flag' => [
                    'Overall,', // Already has comma
                    'Overall', // Should not flag as missing comma
                ],
                'metadata' => []
            ],
            [
                'name' => 'Task 1 - Urban/Rural Population (Band 6.0)',
                'category' => 'writing_academic_task1',
                'question' => 'The chart shows the percentage of people living in urban and rural areas from 1950 to 2000.',
                'expected_band' => 6.0,
                'answer' => "The chart illustrates the percentage of people living in urban and rural areas between 1950 and 2000.\n\n" .
                           "Overall, there was a significant increase in urban population while rural population decreased during this period.\n\n" .
                           "In 1950, about 30% of people lived in urban areas, while 70% lived in rural areas. Over the next 20 years, the urban population increased to 40%, and the rural population fell to 60%.\n\n" .
                           "By 1990, the trend continued, with 60% of people living in cities and 40% in rural areas. Finally, in 2000, the urban population reached 70%, while the rural population was only 30%.",
                'known_errors' => [
                    'people' // Repetition - could use 'residents', 'population'
                ],
                'should_not_flag' => [
                    'Overall,', // Already has comma
                ],
                'metadata' => [
                    'years' => [1950, 1970, 1990, 2000],
                    'urban' => [30, 40, 60, 70],
                    'rural' => [70, 60, 40, 30]
                ]
            ],
            [
                'name' => 'Task 2 - University Education (Band 7.0)',
                'category' => 'writing_academic_task2',
                'question' => 'Some people think that universities should provide graduates with the knowledge and skills needed in the workplace. Others think that the true function of a university should be to give access to knowledge for its own sake. Discuss both views and give your own opinion.',
                'expected_band' => 7.0,
                'answer' => "The role of universities in modern society remains a subject of ongoing debate. While some argue that higher education should prioritize practical workplace skills, others maintain that universities should focus on theoretical knowledge. In my view, a balanced approach incorporating both elements is most beneficial.\n\n" .
                           "Proponents of vocational training argue that universities have a responsibility to prepare students for employment. In an increasingly competitive job market, graduates need specific skills that employers demand. For instance, engineering students require hands-on experience with industry-standard software and equipment, while business students benefit from internships and case studies based on real companies. This practical approach ensures that graduates can contribute immediately to their chosen fields.\n\n" .
                           "Conversely, advocates of pure academic study contend that universities should prioritize intellectual development over immediate employability. Theoretical knowledge fosters critical thinking and problem-solving abilities that transcend specific job requirements. Moreover, fundamental research in fields such as mathematics, philosophy, and pure sciences has historically led to breakthrough innovations, even when the practical applications were not immediately apparent.\n\n" .
                           "In my opinion, universities should strive to balance both approaches. While practical skills are undeniably important for career success, the ability to think critically and adapt to changing circumstances is equally valuable. A curriculum that combines theoretical foundations with practical applications prepares graduates who are both knowledgeable and employable.\n\n" .
                           "In conclusion, rather than choosing between workplace skills and theoretical knowledge, universities should integrate both to produce well-rounded graduates capable of contributing to society in multiple ways.",
                'known_errors' => [],
                'should_not_flag' => [],
                'metadata' => []
            ],
            [
                'name' => 'Task 2 - Technology Impact (Band 7.5)',
                'category' => 'writing_academic_task2',
                'question' => 'Some people believe that technological progress has made our lives more complex. Others think it has made life easier. Discuss both views and give your opinion.',
                'expected_band' => 7.5,
                'answer' => "The impact of technological advancement on modern life remains a contentious issue. While some argue that technology has introduced unnecessary complexity, I believe that, on balance, it has significantly simplified our daily existence.\n\n" .
                           "Admittedly, technological progress has created certain complications. The proliferation of digital devices and platforms requires constant learning and adaptation, which can be overwhelming, particularly for older generations. Moreover, the integration of technology into every aspect of life has created new dependencies, making us vulnerable to system failures and cyber threats. The need to manage multiple passwords, navigate complex interfaces, and stay current with rapid updates can indeed add stress to our lives.\n\n" .
                           "However, the benefits of technology far outweigh these drawbacks. Automation has eliminated countless tedious tasks, from household chores to complex calculations, freeing up time for more meaningful pursuits. Communication technologies have revolutionized how we connect with others, enabling instant global interaction that would have been unimaginable just decades ago. Furthermore, access to information has become democratized, empowering individuals with knowledge and opportunities previously reserved for the privileged few. Online education, telemedicine, and remote work have made essential services more accessible and convenient.\n\n" .
                           "In conclusion, while technological progress has introduced some complexities, its overall effect has been to streamline and enhance our lives. The key lies in developing digital literacy to navigate this evolving landscape effectively. Rather than resisting technological change, we should embrace it while remaining mindful of its potential pitfalls.",
                'known_errors' => [],
                'should_not_flag' => [],
                'metadata' => []
            ]
        ];
    }
}

// Run the calibration
$calibrator = new ScoringCalibrator();
$calibrator->run();
