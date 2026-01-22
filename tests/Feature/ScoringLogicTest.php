<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ScoringService;
use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoringLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = new ScoringService();
    }

    /** @test */
    public function it_caps_overall_band_at_6_if_any_criterion_is_below_6()
    {
        $scores = [
            'task_achievement' => 5.0,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];

        $overall = $this->scoringService->calculateOverallBand($scores);
        
        // Average: (5+7+7+7)/4 = 6.5. 
        // But since TA is 5.0, it should be capped at 6.0.
        $this->assertEquals(6.0, $overall);
    }

    /** @test */
    public function it_rounds_overall_band_to_nearest_half_band()
    {
        $scores = [
            'task_achievement' => 6.5,
            'coherence_cohesion' => 7.0,
            'lexical_resource' => 7.0,
            'grammar' => 7.0,
        ];

        $overall = $this->scoringService->calculateOverallBand($scores);
        
        // Average: (6.5+7+7+7)/4 = 6.875. 
        // Nearest 0.5 is 7.0.
        $this->assertEquals(7.0, $overall);
    }

    /** @test */
    public function it_injects_task_1_metadata_into_prompt()
    {
        $question = Question::factory()->create([
            'category' => 'writing_academic_task1',
            'metadata' => json_encode([
                'chart_type' => 'line',
                'units' => 'percentage'
            ])
        ]);

        // Access protected method via reflection or just check if it's used in scoreWriting
        // For simplicity, we'll check the service's internal behavior if possible
        
        $reflection = new \ReflectionClass(ScoringService::class);
        $method = $reflection->getMethod('buildWritingScoringPrompt');
        $method->setAccessible(true);
        
        $prompt = $method->invoke($this->scoringService, 'Sample answer', $question);
        
        $this->assertStringContainsString('METADATA:', $prompt);
        $this->assertStringContainsString('chart_type', $prompt);
        $this->assertStringContainsString('line', $prompt);
    }

    /** @test */
    public function it_applies_calibration_nudge_for_high_error_density()
    {
        $scores = [
            'task_achievement' => 6.5,
            'coherence_cohesion' => 6.5,
            'lexical_resource' => 6.5,
            'grammar' => 6.5,
            'error_summary' => [
                'grammar_errors_per_100_words' => 10 // High density
            ]
        ];

        $overall = $this->scoringService->calculateOverallBand($scores);
        
        // Average is 6.5. 
        // calibrateScore should nudge it down to 6.0 because density > 8 and average >= 5.5 and average <= 6.5.
        // Wait, my logic was: if ($average >= 5.5 && $average <= 6.5) && $errorDensity > 8 && $average > 6.0
        // Oh, $average > 6.0. So 6.5 should be nudged.
        $this->assertEquals(6.0, $overall);
    }
}
