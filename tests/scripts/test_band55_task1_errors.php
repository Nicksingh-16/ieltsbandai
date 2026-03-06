<?php

/**
 * Test Band 5.5 Essay with 10 Known Genuine Errors
 * 
 * Task 1: Map Description
 * Band: 5.5 (Frequent errors but meaning is clear)
 * Target: 10 Known Errors
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoringService;

echo "=== Band 5.5 Task 1 Error Detection Test (10 Errors) ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$question = (object) [
    'content' => 'The maps below show the center of a small town called Islip as it is now, and plans for its development. Summarize the information by selecting and reporting the main features, and make comparisons where relevant.',
    'category' => 'writing_academic_task1',
    'metadata' => json_encode([])
];

// Band 5.5 Essay with 10 specific errors
$essay = "The maps illustrate the changes in Islip town center currently and the future development plan.

Overall, the town center will be significantly change with the addition of new infrastructure and housing. The main road is going to be pedestrianize and new facilities will be constructed.

Currently, the town center is locate in the main road which runs from east to west. There is a shops along both sides of this road. On the north side, there is a small park and a school. To the south, we can see a park and housing area.

In the future plan, a dual carriageway will be built around the town center to reduce traffic. The main road will become a pedestrian zone, so cars cannot enter. The shops on the north side will remains, but the shops on the south side will be replace by a bus station, a shopping center, and a car park.

Furthermore, the park in the north will be removed to giving place for new housings. The school will remain in the same position but it will be expanded. Finally, new housing will also be constructed on the west side of the town.

In summary, Islip will become more modern with better transport links and many facility for residents.";

// KNOWN ERRORS (10 total):
$knownErrors = [
    [
        'text' => 'will be significantly change',
        'type' => 'Grammar',
        'explanation' => 'Grammar error - should be "will significantly change" or "will be significantly changed"',
        'correction' => 'will change significantly'
    ],
    [
        'text' => 'pedestrianize',
        'type' => 'Grammar',
        'explanation' => 'Passive voice error - after "be", use past participle "pedestrianized"',
        'correction' => 'pedestrianized'
    ],
    [
        'text' => 'is locate',
        'type' => 'Grammar',
        'explanation' => 'Passive voice error - should be "is located"',
        'correction' => 'is located'
    ],
    [
        'text' => 'in the main road',
        'type' => 'Grammar',
        'explanation' => 'Wrong preposition - should be "on the main road"',
        'correction' => 'on the main road'
    ],
    [
        'text' => 'There is a shops',
        'type' => 'Grammar',
        'explanation' => 'Subject-verb agreement - should be "are shops" or "is a shop"',
        'correction' => 'are shops'
    ],
    [
        'text' => 'will remains',
        'type' => 'Grammar',
        'explanation' => 'Modal verb "will" followed by base form - should be "will remain"',
        'correction' => 'will remain'
    ],
    [
        'text' => 'replace by',
        'type' => 'Grammar',
        'explanation' => 'Passive form - should be "replaced by"',
        'correction' => 'replaced by'
    ],
    [
        'text' => 'giving place for',
        'type' => 'Vocabulary',
        'explanation' => 'Unnatural phrasing - should be "make way for" or "give way to"',
        'correction' => 'make way for'
    ],
    [
        'text' => 'new housings',
        'type' => 'Vocabulary',
        'explanation' => 'Uncountable noun - should be "new housing"',
        'correction' => 'new housing'
    ],
    [
        'text' => 'many facility',
        'type' => 'Grammar',
        'explanation' => 'Plural noun required - should be "many facilities"',
        'correction' => 'many facilities'
    ]
];


echo "QUESTION:\n";
echo $question->content . "\n\n";

echo "ESSAY (Band ~5.5 with 10 Known Errors):\n";
echo str_repeat('-', 80) . "\n";
echo $essay . "\n";
echo str_repeat('-', 80) . "\n\n";

echo "KNOWN ERRORS (10 total):\n";
foreach ($knownErrors as $i => $error) {
    echo ($i + 1) . ". [{$error['type']}] \"{$error['text']}\"\n";
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

// Analyze detection logic
$detected = 0;
$matches = [];
$missed = [];

foreach ($knownErrors as $known) {
    $found = false;
    foreach ($detectedErrors as $det) {
        $cleanKnown = trim(preg_replace('/\s+/', ' ', $known['text']));
        $cleanDet = trim(preg_replace('/\s+/', ' ', $det['text']));
        
        if (stripos($cleanKnown, $cleanDet) !== false || stripos($cleanDet, $cleanKnown) !== false) {
            $found = true;
            $matches[] = ['known' => $known, 'detected' => $det];
            $detected++;
            break;
        }
    }
    if (!$found) $missed[] = $known;
}

$rate = ($detected / count($knownErrors)) * 100;

echo "Correctly Detected: {$detected}/" . count($knownErrors) . " ({$rate}%)\n";

if (!empty($matches)) {
    echo "\nMATCHED ERRORS:\n";
    foreach ($matches as $m) {
        echo "✓ \"{$m['known']['text']}\" -> Detected as \"{$m['detected']['text']}\"\n";
    }
}

if (!empty($missed)) {
    echo "\nMISSED ERRORS:\n";
    foreach ($missed as $m) {
        // Fix: Access properties directly since $m is the known error array
        echo "✗ \"{$m['text']}\" ({$m['type']})\n";
    }
}

// Check False Positives
$falsePos = [];
foreach ($detectedErrors as $det) {
    $isKnown = false;
    foreach ($knownErrors as $known) {
        if (stripos($known['text'], $det['text']) !== false || stripos($det['text'], $known['text']) !== false) {
            $isKnown = true;
            break;
        }
    }
    if (!$isKnown) $falsePos[] = $det;
}

if (!empty($falsePos)) {
    echo "\nADDITIONAL ERRORS (Potential False Positives):\n";
    foreach ($falsePos as $fp) {
        echo "! \"{$fp['text']}\" ({$fp['type']}): {$fp['explanation']}\n";
    }
} else {
    echo "\nNo additional errors detected.\n";
}

