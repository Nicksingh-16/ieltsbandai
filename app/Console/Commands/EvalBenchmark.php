<?php

namespace App\Console\Commands;

use App\Models\CalibratedEssay;
use App\Services\CalibrationService;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Run the writing holdout calibration set through ScoringService and report
 * overall-band accuracy. Used as the regression gate for prompt changes.
 *
 * v1 reports overall-band metrics only — per-criterion analysis is deferred
 * until topic_keywords enrichment + a larger calibration set make per-criterion
 * variance statistically meaningful (with ~10 holdout essays the per-criterion
 * noise drowns the signal).
 */
class EvalBenchmark extends Command
{
    protected $signature = 'eval:benchmark
                            {--limit= : Optional cap on number of essays to score}';

    protected $description = 'Score the holdout calibration set and report accuracy metrics for the current prompt version.';

    public function handle(ScoringService $scoring, CalibrationService $calibration): int
    {
        $essays = CalibratedEssay::holdout()
            ->whereIn('task_type', ['writing_task_1_academic', 'writing_task_1_general', 'writing_task_2'])
            ->orderBy('external_id')
            ->get();

        if ($limit = $this->option('limit')) {
            $essays = $essays->take((int) $limit);
        }

        if ($essays->isEmpty()) {
            $this->error('No holdout writing essays found. Run db:seed --class=CalibratedEssaySeeder first.');
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Benchmarking %d holdout essays against ScoringService (prompt %s, model %s)...',
            $essays->count(),
            ScoringService::PROMPT_VERSION,
            config('services.openai.model', 'gpt-4o-mini')
        ));

        $perEssay = [];
        $errors = [];
        $bar = $this->output->createProgressBar($essays->count());
        $bar->start();

        $fewShotEnabled = (bool) config('services.calibration.few_shot_enabled', true);

        foreach ($essays as $essay) {
            $question = $this->buildQuestionStub($essay);

            // Capture anchors used for this essay BEFORE scoring. The cache key
            // (taskType, count, estimatedBand) is identical to what
            // ScoringService::buildCalibratedExamplesBlock will request, so the
            // cached call returns the same picks the scoring prompt saw.
            $anchorsUsed = [];
            $anchorBandAvg = null;
            if ($fewShotEnabled) {
                $anchors = $calibration->findSimilarExamples($essay->essay_text, $essay->task_type, 3);
                $anchorsUsed = $anchors->map(fn ($a) => [
                    'id' => $a->external_id,
                    'band' => (float) $a->band_overall,
                ])->all();
                if ($anchors->isNotEmpty()) {
                    $anchorBandAvg = round($anchors->avg('band_overall'), 2);
                }
            }

            $result = $scoring->scoreWriting($essay->essay_text, $question);

            if ($result === null) {
                $errors[] = $essay->external_id;
                $bar->advance();
                continue;
            }

            $predicted = (float) ($result['overall_band'] ?? $scoring->calculateOverallBand($result));
            $true = (float) $essay->band_overall;
            $error = round($predicted - $true, 2);

            $perEssay[] = [
                'id' => $essay->external_id,
                'task_type' => $essay->task_type,
                'true_band' => $true,
                'predicted_band' => $predicted,
                'error' => $error,
                'anchors_used' => $anchorsUsed,
                'anchor_band_avg' => $anchorBandAvg,
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $metrics = $this->computeMetrics($perEssay);
        $this->renderSummary($metrics, count($perEssay), count($errors));

        $output = [
            'timestamp' => now()->toIso8601String(),
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'prompt_version' => ScoringService::PROMPT_VERSION,
            'n_essays_scored' => count($perEssay),
            'n_essays_errored' => count($errors),
            'errored_ids' => $errors,
            'metrics' => $metrics,
            'per_essay' => $perEssay,
        ];

        $dir = storage_path('app/benchmarks');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $path = $dir . '/' . now()->format('Y-m-d_His') . '.json';
        File::put($path, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Written: ' . $path);

        return $this->meetsAcceptance($metrics) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Build a stub question object compatible with ScoringService::scoreWriting().
     * The category mapping mirrors WritingTestController category strings so
     * determineTaskType() picks the right human label.
     */
    private function buildQuestionStub(CalibratedEssay $essay): object
    {
        $category = match ($essay->task_type) {
            'writing_task_1_academic' => 'academic_task1',
            'writing_task_1_general'  => 'general_task1',
            'writing_task_2'          => 'academic_task2',
            default                   => 'academic_task2',
        };

        return (object) [
            'content' => $essay->task_description ?: '[Calibration essay — task prompt not preserved in source]',
            'category' => $category,
            'metadata' => '{}',
        ];
    }

    private function computeMetrics(array $perEssay): array
    {
        if (empty($perEssay)) {
            return [
                'avg_error_overall' => null,
                'pct_within_0.5_bands' => null,
                'pct_within_1.0_bands' => null,
                'bias_overall' => null,
            ];
        }

        $n = count($perEssay);
        $absErrors = array_map(fn ($r) => abs($r['error']), $perEssay);
        $signedErrors = array_map(fn ($r) => $r['error'], $perEssay);
        $within05 = count(array_filter($absErrors, fn ($e) => $e <= 0.5));
        $within10 = count(array_filter($absErrors, fn ($e) => $e <= 1.0));
        $bias = array_sum($signedErrors) / $n;

        return [
            'avg_error_overall' => round(array_sum($absErrors) / $n, 3),
            'pct_within_0.5_bands' => round($within05 / $n, 3),
            'pct_within_1.0_bands' => round($within10 / $n, 3),
            'bias_overall' => round($bias, 3),
        ];
    }

    private function renderSummary(array $metrics, int $scored, int $errored): void
    {
        $this->table(
            ['metric', 'value'],
            [
                ['n_scored', $scored],
                ['n_errored', $errored],
                ['avg_error_overall (bands)', $metrics['avg_error_overall'] ?? 'n/a'],
                ['pct_within_0.5_bands', $metrics['pct_within_0.5_bands'] ?? 'n/a'],
                ['pct_within_1.0_bands', $metrics['pct_within_1.0_bands'] ?? 'n/a'],
                ['bias_overall (signed)', $metrics['bias_overall'] ?? 'n/a'],
            ]
        );

        if ($this->meetsAcceptance($metrics)) {
            $this->info('PASS — meets L3 acceptance criteria.');
        } else {
            $this->warn('FAIL — does not meet L3 acceptance criteria. Iterate on prompt or retrieval.');
        }
    }

    private function meetsAcceptance(array $metrics): bool
    {
        return $metrics['avg_error_overall'] !== null
            && $metrics['avg_error_overall'] < 0.6
            && $metrics['pct_within_0.5_bands'] !== null
            && $metrics['pct_within_0.5_bands'] >= 0.7
            && $metrics['bias_overall'] !== null
            && abs($metrics['bias_overall']) < 0.5;
    }
}
