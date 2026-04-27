<?php

namespace App\Services;

use App\Models\CalibratedEssay;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Layer 3 (few-shot calibrated essays) retrieval service.
 *
 * Returns a small set of Cambridge-scored example essays for injection into
 * scoring prompts as in-context calibration anchors.
 *
 * v1 strategy: stratified band sampling (one each from low/mid/high buckets)
 * within the requested task type. This works around the current calibration
 * set's skewed band distribution (heavy 6.0/8.5, sparse 4.x/7.5) and gives the
 * LLM a "below | at | above" frame regardless of the candidate's actual band.
 *
 * Topic-similarity matching is deferred to Phase 2 — `topic_keywords` is empty
 * for the seeded essays. When `topic_keywords` is enriched, swap the bucket
 * picks for keyword-overlap-ranked picks within each bucket.
 */
class CalibrationService
{
    private const CACHE_TTL_SECONDS = 86400; // 24 hours

    /**
     * Retrieve calibrated example essays for few-shot prompt injection.
     *
     * @param  string      $essayText      Reserved for Phase 2 topic similarity; ignored in v1.
     * @param  string      $taskType       Enum value: writing_task_1_academic|writing_task_1_general|writing_task_2|speaking_part_*
     * @param  int         $count          Desired number of examples (default 3 — one per bucket).
     * @param  float|null  $estimatedBand  Optional pre-estimate; biases the mid-bucket pick toward this band.
     * @return Collection<int, CalibratedEssay> Up to $count essays. Empty if no calibration data exists for $taskType.
     */
    public function findSimilarExamples(
        string $essayText,
        string $taskType,
        int $count = 3,
        ?float $estimatedBand = null
    ): Collection {
        $cacheKey = sprintf(
            'calib:%s:%d:%s',
            $taskType,
            $count,
            $estimatedBand !== null ? number_format($estimatedBand, 1) : 'na'
        );

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($taskType, $count, $estimatedBand) {
            // Speaking calibration data does not exist yet (Phase A.5).
            if (str_starts_with($taskType, 'speaking_part_')) {
                return collect();
            }

            // orderBy('id') is load-bearing: MySQL does not guarantee row order
            // without ORDER BY, so two queries can return the same rows in
            // different sequence. That would defeat the seeded shuffle below
            // and produce different picks across runs.
            //
            // L4-v2: Band 8.5 essays in the Cambridge dataset are
            // examiner-prepared model answers, not student responses, and the
            // LLM was treating them as outlier references — discounting them
            // and anchoring predictions to the average of the lower student
            // bands. We exclude band > 7.5 from the pool entirely so no Band
            // 8.5 essay can land in the few-shot block via any path.
            $pool = CalibratedEssay::forFewShot()
                ->byTaskType($taskType)
                ->where('band_overall', '<=', 7.5)
                ->orderBy('id')
                ->get();

            if ($pool->isEmpty()) {
                return collect();
            }

            // Bucket: low (<=5.5) | mid (6.0-6.5) | high (7.0-7.5).
            // L4-v2: high bucket tightened to [7.0, 7.5] (was >=7.5) so the
            // ceiling anchor is a credible student response, not an examiner
            // ceiling reference.
            $low  = $pool->where('band_overall', '<=', 5.5)->values();
            $mid  = $pool->whereBetween('band_overall', [6.0, 6.5])->values();
            $high = $pool->whereBetween('band_overall', [7.0, 7.5])->values();

            // Bias the mid-bucket toward $estimatedBand if provided, falling back
            // to the full mid bucket when nothing falls within the tight window.
            if ($estimatedBand !== null) {
                $window = $pool
                    ->whereBetween('band_overall', [$estimatedBand - 0.5, $estimatedBand + 0.5])
                    ->values();
                if ($window->isNotEmpty()) {
                    $mid = $window;
                }
            }

            // Deterministic pick *without* mt_srand/shuffle. Laravel's
            // Collection::shuffle reseeds and restores PHP RNG inside the call,
            // so seeded shuffles compose unreliably across multiple buckets.
            // Instead, hash (taskType|bucket|estimatedBand) into a stable index
            // for each bucket — same inputs always pick the same essay.
            $seedBase = $taskType . '|' . $count . '|' . ($estimatedBand ?? '');

            $picks = collect();
            $this->drawOne($picks, $low,  $seedBase . '|low');
            $this->drawOne($picks, $mid,  $seedBase . '|mid');
            $this->drawOne($picks, $high, $seedBase . '|high');

            // L4-v2: if the high bucket [7.0, 7.5] is empty for this task type,
            // fall back to a second mid-bucket pick rather than reaching for
            // the highest essay in the pool. The pool no longer contains
            // Band 8.5 essays, so "highest available" would be Band 7.0/7.5
            // anyway — but we use the mid pool to avoid double-picking edge
            // cases and to keep the anchor distribution centered.
            if ($high->isEmpty() && $picks->count() < 3) {
                $candidate = $mid
                    ->reject(fn ($e) => $picks->contains('id', $e->id))
                    ->first();
                if ($candidate) {
                    $picks->push($candidate);
                }
            }

            // Symmetric guard for missing low bucket — floor anchor stays a floor.
            if ($low->isEmpty() && $picks->count() < 3) {
                $candidate = $pool
                    ->reject(fn ($e) => $picks->contains('id', $e->id))
                    ->sortBy('band_overall')
                    ->first();
                if ($candidate) {
                    $picks->push($candidate);
                }
            }

            // Final backfill for callers asking for more than 3 anchors.
            $i = 0;
            while ($picks->count() < $count) {
                $remaining = $pool->reject(fn ($e) => $picks->contains('id', $e->id))->values();
                if ($remaining->isEmpty()) {
                    break;
                }
                $idx = (int) (crc32($seedBase . '|fill|' . $i) % $remaining->count());
                $picks->push($remaining->get($idx));
                $i++;
            }

            return $picks->take($count)->values();
        });
    }

    /**
     * Pick one essay from $bucket (if non-empty) and append to $picks.
     * Index is derived from a hash of $seed, so the same (bucket, seed) tuple
     * always picks the same essay — no global RNG state is touched.
     * Skips when $bucket has no items so the caller can backfill.
     */
    private function drawOne(Collection $picks, Collection $bucket, string $seed): void
    {
        $available = $bucket->reject(fn ($e) => $picks->contains('id', $e->id))->values();
        if ($available->isEmpty()) {
            return;
        }
        $idx = (int) (crc32($seed) % $available->count());
        $picks->push($available->get($idx));
    }
}
