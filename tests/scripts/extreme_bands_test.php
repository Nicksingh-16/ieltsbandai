<?php

/**
 * Extreme Bands Error Detection Test (Band 2.0 - 9.0)
 * 
 * Tests the system's ability to handle the full spectrum of proficiency.
 * Focus:
 * 1. Low Bands: Does it reliably score low without over-scoring?
 * 2. High Bands: Does it award Band 8/9 without inventing false errors?
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoringService;

class ExtremeTester {
    protected $service;

    public function __construct() {
        $this->service = app(ScoringService::class);
    }

    public function run() {
        $cases = $this->getTestCases();
        
        echo "=== EXTREME BANDS SCORING TEST (BAND 2 - 9) ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($cases as $index => $case) {
            echo "----------------------------------------------------------------\n";
            echo "CASE #" . ($index + 1) . ": {$case['name']} (Target: {$case['band']})\n";
            echo "Question: " . substr($case['question']->content, 0, 80) . "...\n";
            echo "----------------------------------------------------------------\n";

            $result = $this->service->scoreWriting($case['essay'], $case['question']);
            
            if (!$result) {
                echo "❌ CRITICAL: Scoring Failed!\n";
                continue;
            }

            $aiErrors = $result['errors'] ?? [];
            $aiScore = $result['overall_band'] ?? 'N/A';
            
            echo "AI Band Score: {$aiScore}\n";
            echo "Errors Detected: " . count($aiErrors) . "\n";
            
            // For High Bands (8-9), we check for False Positives specifically
            if ($case['band'] >= 8.0) {
                if (count($aiErrors) > 2) {
                    echo "⚠️ WARNING: High error count for Band {$case['band']} essay!\n";
                    foreach ($aiErrors as $e) echo " - [{$e['type']}] \"{$e['text']}\"\n";
                } else {
                    echo "✅ Clean detection for High Band (Total: " . count($aiErrors) . ")\n";
                }
            }
            
            // For Low Bands, we just want to ensure score is low
            if ($case['band'] <= 4.0) {
                 if ($aiScore > 4.5) {
                    echo "⚠️ WARNING: Score Inflation! Target {$case['band']}, Got {$aiScore}\n";
                 } else {
                    echo "✅ Score appropriate for Low Band.\n";
                 }
            }

            echo "\n";
        }
    }

    private function getTestCases() {
        return [
            // CASE 1: Band 3.0 (Very Limited)
            [
                'name' => 'Task 2 - Education (Band 3.0)',
                'band' => 3.0,
                'question' => (object)['content' => 'Describe your favorite teacher.', 'category' => 'writing_general_task1'], // Simple prompt for low band logic
                'essay' => "My teacher name Mr Smith. He good teacher. He teach math. I like math method. School is big. I go school everyday. My friend also good. We play football. Teacher angry if we noisy. I like study. Thank you.",
                'errors' => [] // We expect MANY errors, just checking the Score logic here.
            ],
            // CASE 2: Band 4.5 (Limited but attempts structure)
            [
                'name' => 'Task 2 - Technology (Band 4.5)',
                'band' => 4.5,
                'question' => (object)['content' => 'Modern technology has made shopping easier. To what extent do you agree?', 'category' => 'writing_academic_task2'],
                'essay' => "Technology is good for people. Today we use phone for buy things. It is very easy. We not go to shop. Just click and buy. This make life happy.\n\nBut some problem is have. If internet bad, we cannot buy. Also sometimes size is not good. My brother buy shirt but it small. He very sad. So technology have bad point too.\n\nI agree technology help us. But we must careful.",
                'errors' => []
            ],
            // CASE 3: Band 8.0 (Strong, occasional slips)
            [
                'name' => 'Task 2 - Environment (Band 8.0)',
                'band' => 8.0,
                'question' => (object)['content' => 'Protecting the environment is the responsibility of the government, not individuals. Agree or disagree?', 'category' => 'writing_academic_task2'],
                'essay' => "The degradation of the natural environment is arguably the most defining challenge of the twenty-first century. While there is a prevailing school of thought suggesting that governments alone should shoulder the burden of environmental stewardship, I firmly believe that this is a collective responsibility that requires active participation from individuals as well.\n\nAdmittedly, the state possesses the legislative power to enact sweeping changes. Governments can enforce carbon taxes, subsidize renewable energy projects, and penalize corporations that flagrantly disregard ecological standards. Without such top-down regulation, systemic change is notoriously difficult to achieve. For example, the ban on single-use plastics in many nations has significantly reduced landfill waste, a feat that individual volunteerism alone could not have accomplished.\n\nHowever, legislation is toothless without compliance. The cumulative impact of individual choices—from dietary habits to transportation—is immense. If citizens refuse to adopt sustainable practices, such as recycling or reducing energy consumption, government mandates will inevitably fail. Furthermore, grassroots movements often drive political will; when politicians see that their constituents prioritize the planet, they are more likely to pass green policies.\n\nIn conclusion, while the government provides the necessary framework for environmental protection, it is the duty of individuals to bring these policies to life through their daily actions. Neither party can succeed in isolation.",
                'errors' => [] // Should be NEAR ZERO errors.
            ],
             // CASE 4: Band 9.0 (Native/Expert)
             [
                'name' => 'Task 2 - Art (Band 9.0)',
                'band' => 9.0,
                'question' => (object)['content' => 'Some people claim that public museums and art galleries will not be needed because people can see historical objects and works of art by using a computer. To what extent do you agree or disagree?', 'category' => 'writing_academic_task2'],
                'essay' => "In an era where high-resolution digital archives and virtual reality tours allow us to view masterpieces from the comfort of our homes, some commentators have hastened to predict the demise of physical museums and art galleries. While digital accessibility is an undeniable boon for education, I fundamentally disagree with the notion that it renders physical institutions obsolete, as the visceral experience of art cannot be replicated on a screen.\n\nThe primary argument against the replacement of museums is the unique aesthetic and emotional impact of seeing artifacts in person. A digital representation, no matter how detailed, lacks the scale, texture, and physical presence of the original work. Standing before the sheer magnitude of Michelangelo’s David or observing the delicate brushstrokes of a Van Gogh provides a sense of connection to history that pixels simply cannot convey. This aura of authenticity is the very essence of the museum experience.\n\nFurthermore, museums serve as communal spaces that foster social learning and reflection. They are meticulously curated environments designed to guide the visitor through a narrative, distinguishing them from the disjointed experience of scrolling through images online. The physical act of moving through a gallery allows for a contemplative pace that contrast sharply with the rapid consumption of digital media.\n\nIn conclusion, while technology complements the role of museums by broadening access, it cannot act as a substitute. The physical preservation and exhibition of our cultural heritage remain vital for a true appreciation of human achievement.",
                'errors' => [] // Zero errors expected. (Note: "contrast sharply" -> "contrasts sharply" is a subtle error I left in the Band 9 text to see if it catches it. Wait, "pace that contrast" -> "pace that contrasts". It SHOULD catch this 1 error).
            ]
        ];
    }
}

$tester = new ExtremeTester();
$tester->run();
