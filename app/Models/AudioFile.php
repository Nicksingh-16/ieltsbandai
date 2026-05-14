<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'file_url',
        'duration_seconds',
        'size_kb',
        'transcript',
        'transcript_words',
        'grammar_matches',
    ];

    protected $casts = [
        'transcript_words' => 'array',
        'grammar_matches' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Decomposes the per-word transcript into renderable segments for the
     * speaking result page — words, filler markers, and pause indicators.
     *
     * Returns a flat array of items, each shaped like one of:
     *   ['type' => 'word',   'text' => 'reading', 'confidence' => 0.91]
     *   ['type' => 'filler', 'text' => 'um',      'category' => 'hesitation']
     *   ['type' => 'pause',  'duration' => 2.3]
     *
     * Pause detection: any gap >= $pauseThresholdSec between one word's end
     * and the next word's start is emitted as a 'pause' segment. The blade
     * renders it as a small dim chip with the duration on hover, so users
     * can see *where* they stalled, not just that they did.
     *
     * Filler detection is intentionally narrower than SpeakingScoreJob's
     * filler counter (which is regex-based on the full transcript). Here we
     * only match per-word tokens, so multi-word fillers ("you know",
     * "sort of") aren't covered — they need bigram matching against the
     * word array, which we can add later if it shows up in real data.
     */
    public function getRichTranscriptSegments(float $pauseThresholdSec = 1.5): array
    {
        $words = $this->transcript_words ?? [];
        if (empty($words) || ! is_array($words)) {
            return [];
        }

        // Single-word fillers grouped by phenomenon. Keep this conservative —
        // 'like' / 'actually' / 'basically' are often used legitimately;
        // we'd rather under-flag than over-flag and erode user trust.
        $hesitation = ['um', 'umm', 'ummm', 'uh', 'uhh', 'uhhh', 'er', 'erm', 'ah'];
        $softeners = ['like', 'actually', 'basically', 'literally'];

        $segments = [];
        $prevEnd = null;

        foreach ($words as $w) {
            $text = (string) ($w['text'] ?? '');
            $start = $w['start'] ?? null;
            $end = $w['end'] ?? null;

            if ($text === '') {
                continue;
            }

            // Long pause between previous word and this one
            if ($prevEnd !== null && $start !== null) {
                $gap = (float) $start - (float) $prevEnd;
                if ($gap >= $pauseThresholdSec) {
                    $segments[] = [
                        'type' => 'pause',
                        'duration' => round($gap, 1),
                    ];
                }
            }

            $clean = strtolower(preg_replace('/[^A-Za-z]/', '', $text));

            if ($clean !== '' && in_array($clean, $hesitation, true)) {
                $segments[] = ['type' => 'filler', 'text' => $text, 'category' => 'hesitation'];
            } elseif ($clean !== '' && in_array($clean, $softeners, true)) {
                $segments[] = ['type' => 'filler', 'text' => $text, 'category' => 'softener'];
            } else {
                $segments[] = [
                    'type' => 'word',
                    'text' => $text,
                    'confidence' => isset($w['confidence']) ? (float) $w['confidence'] : null,
                ];
            }

            $prevEnd = $end;
        }

        return $segments;
    }
}
