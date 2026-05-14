<?php

namespace App\Console\Commands;

use App\Models\CalibratedEssay;
use App\Services\ScoringService;
use Illuminate\Console\Command;

/**
 * Holdout benchmark of the production scoring pipeline.
 *
 * Reads ONLY essays flagged is_holdout=true in the calibrated_essays table —
 * these are excluded from CalibratedEssay::forFewShot() so the LLM never sees
 * the benchmark target as an in-context anchor (no data leakage).
 *
 * Usage:
 *   php artisan benchmark:scoring                # all holdout essays
 *   php artisan benchmark:scoring --count=10     # cap the sample
 *   php artisan benchmark:scoring --task=task2   # filter task type
 *   php artisan benchmark:scoring --seed=42      # deterministic shuffle
 */
class BenchmarkScoring extends Command
{
    protected $signature = 'benchmark:scoring
        {--count=10 : Max essays to score (capped to holdout pool size)}
        {--task= : Optional filter — task1 or task2}
        {--seed=42 : Random seed for reproducibility}';

    protected $description = 'Score Cambridge holdout essays through the live LLM pipeline and report band accuracy.';

    public function handle(): int
    {
        // Holdout pool — set by CalibratedEssaySeeder via deterministic
        // crc32(id) % 5 == 0. Excluded from CalibrationService::findSimilarExamples
        // so no benchmark essay can be retrieved as its own anchor.
        $query = CalibratedEssay::holdout();

        $task = $this->option('task');
        if ($task) {
            $taskTypeFilter = match (strtolower($task)) {
                'task1', 'task_1' => 'writing_task_1_academic',
                'task2', 'task_2' => 'writing_task_2',
                'general1' => 'writing_task_1_general',
                default => null,
            };
            if ($taskTypeFilter) {
                $query->where('task_type', $taskTypeFilter);
            } else {
                $query->where('task_type', 'LIKE', "%{$task}%");
            }
        }

        $essays = $query->orderBy('id')->get();

        if ($essays->isEmpty()) {
            $this->error('No holdout essays match the filter. Run db:seed --class=CalibratedEssaySeeder first.');

            return self::FAILURE;
        }

        // Deterministic shuffle within the holdout pool.
        mt_srand((int) $this->option('seed'));
        $essays = $essays->sort(fn () => mt_rand() <=> mt_rand())->values();

        $count = max(1, min((int) $this->option('count'), $essays->count()));
        $essays = $essays->take($count);

        $this->info("Benchmarking {$count} holdout essay(s) through ScoringService → LLMRouter");
        $this->line('Prompt version: '.ScoringService::PROMPT_VERSION);
        $this->line('Provider chain: OpenRouter (sk-or-) → OpenAI (sk-) → Groq → Gemini');
        $this->newLine();

        $scorer = app(ScoringService::class);
        $rows = [];
        $errors = [];

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($essays as $essay) {
            // Map calibrated_essays.task_type back to a Question::category that
            // determineTaskType() understands. The category string just needs
            // to contain "task1"/"task2"; granularity comes from task_type.
            $category = match ($essay->task_type) {
                'writing_task_1_academic' => 'writing_academic_task1',
                'writing_task_1_general' => 'writing_general_task1',
                'writing_task_2' => 'writing_academic_task2',
                default => 'writing_academic_task2',
            };

            $question = (object) [
                'title' => $essay->task_description ?? '',
                'content' => $essay->task_description ?? '',
                'category' => $category,
                'type' => 'writing',
            ];

            $start = microtime(true);
            try {
                $result = $scorer->scoreWriting($essay->essay_text ?? '', $question);
                $elapsed = microtime(true) - $start;

                if (! $result || ! isset($result['overall_band'])) {
                    $errors[] = "Essay {$essay->external_id}: empty/malformed response";
                    $bar->advance();

                    continue;
                }

                $official = (float) $essay->band_overall;
                $predicted = (float) $result['overall_band'];
                $raw = (float) ($result['overall_band_raw'] ?? $predicted);
                $rows[] = [
                    'id' => $essay->external_id,
                    'task' => $essay->task_type,
                    'official' => $official,
                    'raw' => $raw,
                    'predicted' => $predicted,
                    'shift' => $result['bias_shift'] ?? 0.0,
                    'delta' => round($predicted - $official, 2),
                    'abs_delta' => round(abs($predicted - $official), 2),
                    'elapsed' => round($elapsed, 1).'s',
                ];
            } catch (\Throwable $e) {
                $errors[] = "Essay {$essay->external_id}: ".substr($e->getMessage(), 0, 200);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (empty($rows)) {
            $this->error('All scoring attempts failed.');
            foreach ($errors as $e) {
                $this->line('  - '.$e);
            }

            return self::FAILURE;
        }

        $this->table(
            ['ID', 'Task', 'Official', 'Raw', '+Shift', 'Predicted', 'Δ', '|Δ|', 'Time'],
            array_map(fn ($r) => [$r['id'], $r['task'], $r['official'], $r['raw'], '+'.$r['shift'], $r['predicted'], $r['delta'], $r['abs_delta'], $r['elapsed']], $rows)
        );

        // Aggregate stats
        $abs = array_column($rows, 'abs_delta');
        $delta = array_column($rows, 'delta');
        $mae = array_sum($abs) / count($abs);
        $bias = array_sum($delta) / count($delta);
        $within05 = count(array_filter($abs, fn ($x) => $x <= 0.5));
        $within10 = count(array_filter($abs, fn ($x) => $x <= 1.0));
        $exact = count(array_filter($abs, fn ($x) => $x == 0));

        $this->newLine();
        $this->line('<fg=cyan>══ Aggregate Results ══</>');
        $this->line(sprintf('  MAE (mean absolute error): <fg=yellow>%.2f bands</>', $mae));
        $this->line(sprintf('  Bias (avg signed Δ):       <fg=yellow>%+.2f bands</> %s',
            $bias,
            $bias > 0.2 ? '(consistently over-scoring)' : ($bias < -0.2 ? '(consistently under-scoring)' : '(unbiased)')
        ));
        $this->line(sprintf('  Exact match:               <fg=green>%d/%d (%d%%)</>', $exact, count($rows), round($exact / count($rows) * 100)));
        $this->line(sprintf('  Within ±0.5 band:          <fg=green>%d/%d (%d%%)</>', $within05, count($rows), round($within05 / count($rows) * 100)));
        $this->line(sprintf('  Within ±1.0 band:          <fg=green>%d/%d (%d%%)</>', $within10, count($rows), round($within10 / count($rows) * 100)));

        if ($errors) {
            $this->newLine();
            $this->warn(sprintf('%d failure(s):', count($errors)));
            foreach ($errors as $e) {
                $this->line('  - '.$e);
            }
        }

        // Honest recommendation
        $this->newLine();
        $this->line('<fg=cyan>══ Beta-launch recommendation ══</>');
        if ($mae <= 0.5 && $within05 / count($rows) >= 0.7) {
            $this->info('  ✓ Free-tier quality looks beta-ready (MAE ≤ 0.5, ≥70% within ±0.5).');
            $this->line('  You can launch on Telegram without paying for an OpenAI key, but disclose');
            $this->line('  scores are indicative (±0.5 from official) in your beta intro.');
        } elseif ($mae <= 0.75) {
            $this->line('  <fg=yellow>~ Borderline.</> MAE between 0.5–0.75 — usable for beta with a clear');
            $this->line('  disclaimer, but expect 10–20% of users to dispute borderline calls.');
            $this->line('  Plan to budget OpenAI for public launch.');
        } else {
            $this->error('  ✗ Free-tier quality is too soft (MAE > 0.75) for IELTS scoring claims.');
            $this->line('  Either set OPENAI_API_KEY=sk-... before beta, or strongly disclaim');
            $this->line('  results as "AI estimates, not official IELTS scores".');
        }

        return self::SUCCESS;
    }
}
