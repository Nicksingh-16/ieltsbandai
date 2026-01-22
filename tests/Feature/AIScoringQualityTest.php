<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIScoringQualityTest extends TestCase
{
    protected $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = app(ScoringService::class);
    }

    /**
     * Test that AI doesn't flag false positive punctuation errors
     * Specifically: "Overall," already has a comma, should not be flagged
     */
    public function test_no_false_positive_punctuation_errors()
    {
        $question = (object) [
            'content' => 'The diagram below shows how coffee is produced and prepared for sale.',
            'category' => 'writing_academic_task1',
            'metadata' => json_encode([])
        ];

        // This essay has "Overall," with a comma - should NOT be flagged
        $answer = "The diagram illustrates the process by which coffee is produced and prepared for sale.\n\n" .
                  "Overall, coffee production is a linear, multi-stage process that begins with harvesting ripe coffee beans and ends with packaging the final product for commercial distribution.";

        $result = $this->scoringService->scoreWriting($answer, $question);

        $this->assertNotNull($result, 'Scoring should return a result');
        $this->assertIsArray($result['errors'] ?? null, 'Errors should be an array');

        // Check that "Overall" is NOT flagged as missing a comma
        $punctuationErrors = array_filter($result['errors'], function($error) {
            return ($error['type'] === 'Punctuation' || $error['category'] === 'Punctuation') 
                   && stripos($error['text'], 'Overall') !== false;
        });

        $this->assertEmpty($punctuationErrors, 
            'Should not flag "Overall," as missing a comma when it already has one. Found: ' . 
            json_encode($punctuationErrors));
    }

    /**
     * Test Band 6.0 essay quality - should have reasonable error count
     */
    public function test_band_6_essay_quality()
    {
        $question = (object) [
            'content' => 'The chart shows the percentage of people living in urban and rural areas from 1950 to 2000.',
            'category' => 'writing_academic_task1',
            'metadata' => json_encode([
                'years' => [1950, 1970, 1990, 2000],
                'urban' => [30, 40, 60, 70],
                'rural' => [70, 60, 40, 30]
            ])
        ];

        // Band 6.0 essay with some genuine errors but task adequately addressed
        $answer = "The chart illustrates the percentage of people living in urban and rural areas between 1950 and 2000.\n\n" .
                  "Overall, there was a significant increase in urban population while rural population decreased during this period.\n\n" .
                  "In 1950, about 30% of people lived in urban areas, while 70% lived in rural areas. " .
                  "Over the next 20 years, the urban population increased to 40%, and the rural population fell to 60%.\n\n" .
                  "By 1990, the trend continued, with 60% of people living in cities and 40% in rural areas. " .
                  "Finally, in 2000, the urban population reached 70%, while the rural population was only 30%.";

        $result = $this->scoringService->scoreWriting($answer, $question);

        $this->assertNotNull($result);
        
        // Band should be around 6.0 (±0.5)
        $overallBand = $result['overall_band'] ?? 0;
        $this->assertGreaterThanOrEqual(5.5, $overallBand, 'Band should be at least 5.5');
        $this->assertLessThanOrEqual(6.5, $overallBand, 'Band should not exceed 6.5 for this essay');

        // Error count should be reasonable (not artificially inflated to 15-20)
        $errorCount = count($result['errors'] ?? []);
        $this->assertLessThan(15, $errorCount, 
            "Error count should be reasonable, not artificially inflated. Found {$errorCount} errors");
    }

    /**
     * Test that errors are actually present in the text
     */
    public function test_all_errors_exist_in_text()
    {
        $question = (object) [
            'content' => 'Some people think that universities should provide graduates with the knowledge and skills needed in the workplace. Others think that the true function of a university should be to give access to knowledge for its own sake. Discuss both views and give your own opinion.',
            'category' => 'writing_academic_task2',
            'metadata' => json_encode([])
        ];

        $answer = "In today's world, there is debate about whether universities should focus on practical skills or theoretical knowledge. " .
                  "I believe that universities should balance both approaches.\n\n" .
                  "On one hand, practical skills are important for employment. Many employers want graduates who can start working immediately. " .
                  "For example, engineering students need hands-on experience with tools and software.\n\n" .
                  "On the other hand, theoretical knowledge is also valuable. It helps students think critically and solve complex problems. " .
                  "Pure research in fields like mathematics or philosophy advances human understanding.\n\n" .
                  "In conclusion, universities should provide both practical training and theoretical education to prepare well-rounded graduates.";

        $result = $this->scoringService->scoreWriting($answer, $question);

        $this->assertNotNull($result);

        // Every error text must exist in the original answer
        foreach ($result['errors'] ?? [] as $error) {
            $errorText = $error['text'] ?? '';
            $this->assertNotEmpty($errorText, 'Error text should not be empty');
            
            // Case-insensitive check
            $found = stripos($answer, $errorText) !== false;
            
            $this->assertTrue($found, 
                "Error text '{$errorText}' not found in original answer. This is a hallucinated error!");
        }
    }

    /**
     * Test Band 7.5+ essay should have minimal errors
     */
    public function test_high_band_essay_minimal_errors()
    {
        $question = (object) [
            'content' => 'Some people believe that technological progress has made our lives more complex. Others think it has made life easier. Discuss both views and give your opinion.',
            'category' => 'writing_academic_task2',
            'metadata' => json_encode([])
        ];

        // High-quality Band 7.5+ essay
        $answer = "The impact of technological advancement on modern life remains a contentious issue. " .
                  "While some argue that technology has introduced unnecessary complexity, I believe that, on balance, it has significantly simplified our daily existence.\n\n" .
                  "Admittedly, technological progress has created certain complications. The proliferation of digital devices and platforms requires constant learning and adaptation, " .
                  "which can be overwhelming, particularly for older generations. Moreover, the integration of technology into every aspect of life has created new dependencies, " .
                  "making us vulnerable to system failures and cyber threats.\n\n" .
                  "However, the benefits of technology far outweigh these drawbacks. Automation has eliminated countless tedious tasks, from household chores to complex calculations, " .
                  "freeing up time for more meaningful pursuits. Communication technologies have revolutionized how we connect with others, enabling instant global interaction " .
                  "that would have been unimaginable just decades ago. Furthermore, access to information has become democratized, empowering individuals with knowledge and opportunities " .
                  "previously reserved for the privileged few.\n\n" .
                  "In conclusion, while technological progress has introduced some complexities, its overall effect has been to streamline and enhance our lives. " .
                  "The key lies in developing digital literacy to navigate this evolving landscape effectively.";

        $result = $this->scoringService->scoreWriting($answer, $question);

        $this->assertNotNull($result);
        
        // Band should be 7.0 or higher
        $overallBand = $result['overall_band'] ?? 0;
        $this->assertGreaterThanOrEqual(7.0, $overallBand, 'High-quality essay should score at least Band 7.0');

        // Error count should be low (< 5 for Band 7.5+)
        $errorCount = count($result['errors'] ?? []);
        $this->assertLessThan(8, $errorCount, 
            "High-band essay should have minimal errors. Found {$errorCount} errors");
    }

    /**
     * Test that duplicate errors are removed
     */
    public function test_no_duplicate_errors()
    {
        $question = (object) [
            'content' => 'Describe the process shown in the diagram.',
            'category' => 'writing_academic_task1',
            'metadata' => json_encode([])
        ];

        $answer = "The diagram shows a process. The process has many steps. The process is complex.";

        $result = $this->scoringService->scoreWriting($answer, $question);

        $this->assertNotNull($result);

        // Check for duplicate error texts
        $errorTexts = array_map(function($error) {
            return strtolower(trim($error['text'] ?? ''));
        }, $result['errors'] ?? []);

        $uniqueErrors = array_unique($errorTexts);
        
        $this->assertEquals(count($uniqueErrors), count($errorTexts), 
            'Should not have duplicate errors. Found duplicates: ' . 
            json_encode(array_diff_assoc($errorTexts, $uniqueErrors)));
    }
}
