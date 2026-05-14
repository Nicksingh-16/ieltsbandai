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
     * @param  string  $essayText  Reserved for Phase 2 topic similarity; ignored in v1.
     * @param  string  $taskType  Enum value: writing_task_1_academic|writing_task_1_general|writing_task_2|speaking_part_*
     * @param  int  $count  Desired number of examples (default 3 — one per bucket).
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
            // L5-v1: Band 8+ examiner-prepared models are back in the pool.
            // The L4-v2 exclusion (band <= 7.5) capped the visible ceiling at
            // Band 7.5, which caused the LLM to systematically under-score
            // genuine Band 8.0–8.5 essays (it had no in-context evidence that
            // Band 8 was achievable). CalibratedEssay::toFewShotBlock() labels
            // band >= 8.5 essays as "EXAMINER-PREPARED MODEL — target ceiling
            // reference" so the LLM treats them as ceiling anchors, not
            // representative student responses.
            $pool = CalibratedEssay::forFewShot()
                ->byTaskType($taskType)
                ->orderBy('id')
                ->get();

            if ($pool->isEmpty()) {
                return collect();
            }

            // Bucket: low (<=5.5) | mid (6.0-6.5) | high (7.0-7.5 student) | ceiling (8.0+).
            // L5-v1: a 4th "ceiling" bucket isolates Band 8+ examiner models
            // so they're guaranteed to appear as the top reference rather
            // than competing for slots inside a larger high bucket where the
            // deterministic hash routinely skipped them.
            $low = $pool->where('band_overall', '<=', 5.5)->values();
            $mid = $pool->whereBetween('band_overall', [6.0, 6.5])->values();
            $high = $pool->whereBetween('band_overall', [7.0, 7.5])->values();
            $ceiling = $pool->where('band_overall', '>=', 8.0)->values();

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
            $seedBase = $taskType.'|'.$count.'|'.($estimatedBand ?? '');

            $picks = collect();
            // Order matters: low → mid → ceiling → high so when count=3 and a
            // Band 8+ ceiling exists we keep [low, mid, ceiling] and the
            // student Band 7 anchor is only used as a fallback ceiling.
            $this->drawOne($picks, $low, $seedBase.'|low');
            $this->drawOne($picks, $mid, $seedBase.'|mid');
            $this->drawOne($picks, $ceiling, $seedBase.'|ceiling');
            if ($picks->count() < $count) {
                $this->drawOne($picks, $high, $seedBase.'|high');
            }

            // L5-v1: if BOTH high and ceiling buckets were empty for this task
            // type, fall back to a second mid-bucket pick. Niche task types
            // (general task 1) historically had no Band 7+ data.
            if ($high->isEmpty() && $ceiling->isEmpty() && $picks->count() < 3) {
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
                $idx = (int) (crc32($seedBase.'|fill|'.$i) % $remaining->count());
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
