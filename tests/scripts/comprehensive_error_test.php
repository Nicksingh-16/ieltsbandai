<?php

/**
 * Comprehensive Error Detection Test
 * 
 * Runs 5 different test cases against the AI Scoring Service.
 * Calculates Overall Recall (Detection Rate) and False Positive Rate.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoringService;

class ComprehensiveTester {
    protected $service;
    protected $results = [];

    public function __construct() {
        $this->service = app(ScoringService::class);
    }

    public function run() {
        $cases = $this->getTestCases();
        $totalKnown = 0;
        $totalDetected = 0;
        $totalFalsePositives = 0;

        echo "=== COMPREHENSIVE AI ERROR DETECTION TEST (5 CASES) ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($cases as $index => $case) {
            echo "----------------------------------------------------------------\n";
            echo "CASE #" . ($index + 1) . ": {$case['name']} (Target Band: {$case['band']})\n";
            echo "Question: " . substr($case['question']->content, 0, 80) . "...\n";
            echo "Known Errors: " . count($case['errors']) . "\n";
            echo "----------------------------------------------------------------\n";

            $result = $this->service->scoreWriting($case['essay'], $case['question']);
            
            if (!$result) {
                echo "❌ CRITICAL: Scoring Failed!\n";
                continue;
            }

            $aiErrors = $result['errors'] ?? [];
            $detectedCount = 0;
            $matches = [];
            $missed = [];

            // Check detection of known errors
            foreach ($case['errors'] as $known) {
                $found = false;
                foreach ($aiErrors as $aiError) {
                    $cleanKnown = trim(strtolower(preg_replace('/[^\w\s]/', '', $known['text'])));
                    $cleanAI = trim(strtolower(preg_replace('/[^\w\s]/', '', $aiError['text'])));
                    
                    // Flexible matching
                    if (str_contains($cleanKnown, $cleanAI) || str_contains($cleanAI, $cleanKnown)) {
                        $found = true;
                        $detectedCount++;
                        $matches[] = $known['text'];
                        break;
                    }
                }
                if (!$found) $missed[] = $known['text'];
            }

            // Check for False Positives (errors flagged that aren't in known list)
            $falsePositives = 0;
            foreach ($aiErrors as $aiError) {
                $isKnown = false;
                foreach ($case['errors'] as $known) {
                    $cleanKnown = trim(strtolower(preg_replace('/[^\w\s]/', '', $known['text'])));
                    $cleanAI = trim(strtolower(preg_replace('/[^\w\s]/', '', $aiError['text'])));
                    
                     if (str_contains($cleanKnown, $cleanAI) || str_contains($cleanAI, $cleanKnown)) {
                        $isKnown = true;
                        break;
                    }
                }
                if (!$isKnown) {
                    // It's a false positive UNLESS it's a valid error we forgot to list
                    // For this test, we assume our list is complete, so anything else is a candidate FP
                    $falsePositives++;
                    echo "⚠️  Potential False Positive: \"{$aiError['text']}\" ({$aiError['type']})\n";
                }
            }

            $rate = count($case['errors']) > 0 ? ($detectedCount / count($case['errors'])) * 100 : 100;
            
            echo "Detected: {$detectedCount}/" . count($case['errors']) . " (" . number_format($rate, 1) . "%)\n";
            echo "False Positives: {$falsePositives}\n";
            echo "AI Band Score: " . ($result['overall_band'] ?? 'N/A') . "\n";

            if (!empty($matches)) {
                echo "✅ Successfully Detected:\n";
                foreach ($matches as $m) echo " - \"{$m}\"\n";
            }

            if (!empty($missed)) {
                echo "❌ Missed Errors:\n";
                foreach ($missed as $m) echo " - \"{$m}\"\n";
            }

            $totalKnown += count($case['errors']);
            $totalDetected += $detectedCount;
            $totalFalsePositives += $falsePositives;
            echo "\n";
        }

        echo "================================================================\n";
        echo "FINAL SUMMARY\n";
        echo "================================================================\n";
        $overallRate = $totalKnown > 0 ? ($totalDetected / $totalKnown) * 100 : 0;
        echo "Total Test Cases: " . count($cases) . "\n";
        echo "Total Known Errors: {$totalKnown}\n";
        echo "Total Detected: {$totalDetected}\n";
        echo "Overall Detection Rate: " . number_format($overallRate, 2) . "%\n";
        echo "Total False Positives: {$totalFalsePositives}\n";
        echo "================================================================\n";
    }

    private function getTestCases() {
        return [
            // CASE 1: Task 2 - Education (Band 5.0)
            [
                'name' => 'Task 2 - Education (Band 5.0)',
                'band' => 5.0,
                'question' => (object)['content' => 'Some people believe that exams are the best way to assess student learning. Others think coursework is better. Discuss both views.', 'category' => 'writing_academic_task2'],
                'essay' => "I think exams is good for student. When they do exam, they knowing how much they study. Students need to study hard for pass the test. This help them remember important things. Also, exams is fair because everyone do same test at same time. Teachers can see who is best student.

However, exams makes student stress. Many student fail because they nervous. They cannot thinking clearly in exam room. Also, sometimes exam is not about understand, but about memory. If student have bad memory, they fail.

On other hand, some coursework is easy. Student can doing it at home. They have time to think and write good answer. They can use book and internet. This is better for learning deep. But some student maybe copy from internet, so it is not fair.

In my opinion, I agree with rework. I think coursework is better than exam because it less stress and we can learn more.",
                'errors' => [
                    ['text' => 'exams is good', 'type' => 'Grammar'],
                    ['text' => 'they knowing', 'type' => 'Grammar'],
                    ['text' => 'exams makes', 'type' => 'Grammar'],
                    ['text' => 'student stress', 'type' => 'Grammar'],
                    ['text' => 'Many student', 'type' => 'Grammar'],
                    ['text' => 'they nervous', 'type' => 'Grammar'],
                    ['text' => 'On other hand', 'type' => 'Grammar'],
                    ['text' => 'Student can doing', 'type' => 'Grammar'],
                    ['text' => 'rework', 'type' => 'Vocabulary'],
                    ['text' => 'student.', 'type' => 'Grammar']
                ]
            ],
            // CASE 2: Task 1 - Line Graph (Band 6.0)
            [
                'name' => 'Task 1 - Line Graph (Band 6.0)',
                'band' => 6.0,
                'question' => (object)['content' => 'The graph below shows electricity consumption in different sectors of Eastern Europe.', 'category' => 'writing_academic_task1'],
                'essay' => "The line graph shows electricity consumption in Eastern Europe between 2005 and 2010. The graph illustrates usage in four different sectors: residential, commercial, industrial, and transport.

Overall, consumption increased in all sectors during the given period. In 2005, the highest usage was in industry, while the lowest was in transport.

By 2008, usage rose to 100 units in the industrial sector. It remained stable for two years. However, in 2010, it dropped sharpness to 80 units. In contrast, residential use goes up steadily throughout period. It reached peak of 50 units in the final year.

Commercial usage started at 20 units and fluctuated significantly. It increased to 40 units in 2007 but then fell back to 25 units. The transport sector showed the slowest growth, rising from 10 to 15 units over the five years.

In summary, industry consumed the most electricity, although it decreased at the end.",
                'errors' => [
                    ['text' => 'dropped sharpness', 'type' => 'Grammar'],
                    ['text' => 'use goes up', 'type' => 'Grammar'],
                    ['text' => 'throughout period', 'type' => 'Grammar'],
                    ['text' => 'reached peak', 'type' => 'Grammar'],
                ]
            ],
             // CASE 3: Task 2 - Technology (Band 6.5)
             [
                'name' => 'Task 2 - Technology (Band 6.5)',
                'band' => 6.5,
                'question' => (object)['content' => 'Modern technology has made shopping easier. To what extent do you agree?', 'category' => 'writing_academic_task2'],
                'essay' => "It is true that technology have revolutionized the way we shop. Nowadays, people can buy almost anything from comfort of their homes. This convenience is the main reason why online shopping is so popular. I completely agree that technology has made shopping much easier, although there are some minor drawbacks.

The primary benefit is convenience. In the past, people had to travel to physical stores, which was time-consuming. Now, with just a few clicks, we can order groceries, clothes, and electronics. For example, Amazon delivers products to our doorstep within a day. This saves a significant amount of time for busy professionals.

However, there are drawback. For example, customers cannot touch products before buying. This leads to high return rates. Despite this, I believe benefits outweigh downsides because return policies are usually very easy.

Another point is better prices. Technology allows us to compare prices instantly across different websites. We can find the best deal without visiting multiple shops.

In conclusion, technology has undeniably improved the shopping experience by providing convenience and better choices.",
                'errors' => [
                    ['text' => 'technology have', 'type' => 'Grammar'],
                    ['text' => 'from comfort', 'type' => 'Grammar'],
                    ['text' => 'are drawback', 'type' => 'Grammar'],
                    ['text' => 'believe benefits', 'type' => 'Grammar'],
                ]
            ],
            // CASE 4: Task 1 - Process (Band 5.5)
            [
                'name' => 'Task 1 - Process (Band 5.5)',
                'band' => 5.5,
                'question' => (object)['content' => 'The diagram shows how tea is produced.', 'category' => 'writing_academic_task1'],
                'essay' => "The flowchart illustrates the various stages in the production of tea, from planting to delivery. There are about five main steps in this process.

First, tea seeds are sown in the ground. The sun shines on them and they grow into plants. When the plants are ready, tea leaves are pick by hand. Then they are drying in the sun for several days to remove moisture. This is an important stage.

After that, the leaves are pack into boxes. They are put into a lorry and transported to the factory. In the factory, they are processed further. Finally, they delivered to supermarkets where customers can buy them.

Overall, passing through several stages is necessary to produce tea. It is a long process involving both farming and manufacturing.",
                'errors' => [
                    ['text' => 'are pick', 'type' => 'Grammar'],
                    ['text' => 'are drying', 'type' => 'Grammar'],
                    ['text' => 'are pack', 'type' => 'Grammar'],
                    ['text' => 'they delivered', 'type' => 'Grammar'],
                ]
            ],
             // CASE 5: Task 2 - Environment (Band 7.0)
             [
                'name' => 'Task 2 - Environment (Band 7.0)',
                'band' => 7.0,
                'question' => (object)['content' => 'Protecting the environment is the responsibility of the government, not individuals. Agree or disagree?', 'category' => 'writing_academic_task2'],
                'essay' => "Environmental protection is a pressing issue in the modern world. While some argue that the government plays the sole role in this regard, I believe that individuals also have a significant responsibility. Both parties must work together to achieve sustainable results.

Governments certainly have the power to enact change on a large scale. They can impose strict laws to prevent pollution and penalize companies that violate environmental regulations. For instance, putting a tax on carbon emissions encourages industries to be greener. Without government intervention, large corporations would likely prioritize profit over the planet.

However, without individual cooperation, these laws are useless. Government policies rely on public compliance. For instance, people should make an effort to do recycling and reduce waste in their own homes. If individuals continue to consume plastic excessively, government bans will have limited effect. We must pay attention on our daily habits, such as energy consumption and water usage.

In conclusion, I disagree that environmental protection is solely the government's job. While officials set the framework, individuals must actively participate by adopting eco-friendly habits.",
                'errors' => [
                    ['text' => 'do recycling', 'type' => 'Vocabulary'],
                    ['text' => 'pay attention on', 'type' => 'Grammar'],
                ]
            ]
        ];
    }
}

$tester = new ComprehensiveTester();
$tester->run();
