<?php

/**
 * Test Band 6.0 Essay with 8 Known Genuine Errors
 * 
 * This script tests the AI's ability to detect genuine errors in a Band 6.0 essay.
 * The essay has been crafted with exactly 8 known errors that are typical of Band 6 writing.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoringService;

echo "=== Band 6.0 Essay Error Detection Test ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$question = (object) [
    'content' => 'Some people believe that children should start learning a foreign language at primary school rather than secondary school. Do the advantages of this outweigh the disadvantages?',
    'category' => 'writing_academic_task2',
    'metadata' => json_encode([])
];

// Band 6.0 essay with 8 KNOWN GENUINE ERRORS
$essay = "Nowadays, learning foreign language is becoming more important in our society. Some people thinks that children should start learning foreign language at primary school instead of secondary school. In my opinion, I believe that the advantages outweigh the disadvantages.

Firstly, young children have better ability to learn new languages. Their brain are more flexible and they can absorb new information easier than adults. For example, many research shows that children who start learning English at age 6 can speak more fluently than those who start at age 12. This is because they have more time to practice and develop their language skills.

Secondly, learning a foreign language at early age can help children to understand different cultures. When children learn a new language, they also learn about the country and the people who speak that language. This can make them more open-minded and tolerant to other cultures. In addition, it can help them in their future career because many companies need employees who can speak multiple languages.

However, some people argue that learning a foreign language at primary school can be too difficult for young children. They believe that children should focus on their mother tongue first before learning another language. Also, it might cause confusion and affect their performance in other subjects.

In conclusion, although there are some disadvantages, I believe that the advantages of learning a foreign language at primary school is greater. Children have better learning ability and it can benefit them in the future.";

// KNOWN ERRORS (8 total):
$knownErrors = [
    [
        'text' => 'learning foreign language',
        'type' => 'Grammar',
        'explanation' => 'Missing article "a" before "foreign language"',
        'correction' => 'learning a foreign language'
    ],
    [
        'text' => 'people thinks',
        'type' => 'Grammar',
        'explanation' => 'Subject-verb agreement error - "people" is plural, should be "think"',
        'correction' => 'people think'
    ],
    [
        'text' => 'Their brain are',
        'type' => 'Grammar',
        'explanation' => 'Subject-verb agreement - "brain" is singular, should be "is" OR use "brains are"',
        'correction' => 'Their brains are'
    ],
    [
        'text' => 'easier',
        'type' => 'Grammar',
        'explanation' => 'Should be "more easily" (adverb form) to modify the verb "absorb"',
        'correction' => 'more easily'
    ],
    [
        'text' => 'many research shows',
        'type' => 'Grammar',
        'explanation' => 'Research is uncountable - should be "much research shows" or "many studies show"',
        'correction' => 'much research shows'
    ],
    [
        'text' => 'at early age',
        'type' => 'Grammar',
        'explanation' => 'Missing article "an" before "early age"',
        'correction' => 'at an early age'
    ],
    [
        'text' => 'tolerant to',
        'type' => 'Vocabulary',
        'explanation' => 'Wrong preposition - should be "tolerant of" not "tolerant to"',
        'correction' => 'tolerant of'
    ],
    [
        'text' => 'advantages of learning a foreign language at primary school is greater',
        'type' => 'Grammar',
        'explanation' => 'Subject-verb agreement - "advantages" is plural, should be "are greater"',
        'correction' => 'advantages of learning a foreign language at primary school are greater'
    ]
];

echo "QUESTION:\n";
echo $question->content . "\n\n";

echo "ESSAY (Band 6.0 with 8 Known Errors):\n";
echo str_repeat('-', 80) . "\n";
echo $essay . "\n";
echo str_repeat('-', 80) . "\n\n";

echo "KNOWN ERRORS (8 total):\n";
foreach ($knownErrors as $i => $error) {
    echo ($i + 1) . ". [{$error['type']}] \"{$error['text']}\"\n";
    echo "   → {$error['explanation']}\n";
    echo "   ✓ Correction: \"{$error['correction']}\"\n\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "RUNNING AI SCORING...\n";
echo str_repeat('=', 80) . "\n\n";

$scoringService = app(ScoringService::class);
$result = $scoringService->scoreWriting($essay, $question);

if (!$result) {
    echo "❌ ERROR: Scoring failed\n";
    exit(1);
}

$detectedErrors = $result['errors'] ?? [];
$overallBand = $result['overall_band'] ?? 0;

echo "RESULTS:\n";
echo "Overall Band: {$overallBand}\n";
echo "Detected Errors: " . count($detectedErrors) . "\n\n";

// Analyze detection rate
$detected = 0;
$detectedKnownErrors = [];
$missedKnownErrors = [];

foreach ($knownErrors as $knownError) {
    $found = false;
    foreach ($detectedErrors as $detectedError) {
        // Check if the detected error matches the known error (case-insensitive partial match)
        if (stripos($knownError['text'], $detectedError['text']) !== false || 
            stripos($detectedError['text'], $knownError['text']) !== false) {
            $found = true;
            $detected++;
            $detectedKnownErrors[] = [
                'known' => $knownError,
                'detected' => $detectedError
            ];
            break;
        }
    }
    
    if (!$found) {
        $missedKnownErrors[] = $knownError;
    }
}

$detectionRate = ($detected / count($knownErrors)) * 100;

echo str_repeat('=', 80) . "\n";
echo "DETECTION ANALYSIS\n";
echo str_repeat('=', 80) . "\n\n";

echo "Known Errors: " . count($knownErrors) . "\n";
echo "Detected by AI: {$detected}/" . count($knownErrors) . " (" . round($detectionRate, 1) . "%)\n";
echo "Missed: " . count($missedKnownErrors) . "\n\n";

if (!empty($detectedKnownErrors)) {
    echo "✅ SUCCESSFULLY DETECTED ERRORS:\n";
    foreach ($detectedKnownErrors as $i => $match) {
        echo "\n" . ($i + 1) . ". Known Error: \"{$match['known']['text']}\"\n";
        echo "   AI Detected: \"{$match['detected']['text']}\"\n";
        echo "   Type: {$match['detected']['type']}\n";
        echo "   AI Explanation: {$match['detected']['explanation']}\n";
    }
    echo "\n";
}

if (!empty($missedKnownErrors)) {
    echo "❌ MISSED ERRORS:\n";
    foreach ($missedKnownErrors as $i => $error) {
        echo "\n" . ($i + 1) . ". \"{$error['text']}\"\n";
        echo "   Type: {$error['type']}\n";
        echo "   Expected: {$error['explanation']}\n";
    }
    echo "\n";
}

// Check for false positives
$falsePositives = [];
foreach ($detectedErrors as $detectedError) {
    $isKnown = false;
    foreach ($knownErrors as $knownError) {
        if (stripos($knownError['text'], $detectedError['text']) !== false || 
            stripos($detectedError['text'], $knownError['text']) !== false) {
            $isKnown = true;
            break;
        }
    }
    
    if (!$isKnown) {
        $falsePositives[] = $detectedError;
    }
}

if (!empty($falsePositives)) {
    echo "⚠️  ADDITIONAL ERRORS DETECTED (Not in Known List):\n";
    foreach ($falsePositives as $i => $error) {
        echo "\n" . ($i + 1) . ". [{$error['type']}] \"{$error['text']}\"\n";
        echo "   Explanation: {$error['explanation']}\n";
    }
    echo "\n";
} else {
    echo "✅ No additional errors detected (no false positives)\n\n";
}

echo str_repeat('=', 80) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 80) . "\n\n";

echo "Detection Rate: " . round($detectionRate, 1) . "%\n";
echo "Band Score: {$overallBand} (Expected: ~6.0)\n";
echo "False Positives: " . count($falsePositives) . "\n\n";

if ($detectionRate >= 75) {
    echo "✅ EXCELLENT: Detection rate >= 75%\n";
} elseif ($detectionRate >= 50) {
    echo "⚠️  GOOD: Detection rate >= 50%\n";
} else {
    echo "❌ NEEDS IMPROVEMENT: Detection rate < 50%\n";
}

if ($overallBand >= 5.5 && $overallBand <= 6.5) {
    echo "✅ BAND SCORE ACCURATE: Within expected range (5.5-6.5)\n";
} else {
    echo "⚠️  BAND SCORE: Outside expected range (got {$overallBand}, expected ~6.0)\n";
}

if (count($falsePositives) === 0) {
    echo "✅ NO FALSE POSITIVES\n";
} else {
    echo "⚠️  " . count($falsePositives) . " additional errors detected\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
