<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Two-part data fix for the listening question pool:
 *
 *  1. Re-encode any double-encoded metadata. Some seed rows stored
 *     metadata as a JSON-encoded *string* (one extra layer); downstream
 *     code branches on is_string() to cope but the data should be flat.
 *     We decode once and re-save so JSON_TYPE(metadata) = OBJECT for all.
 *
 *  2. Deactivate listening questions whose audio_url is null AND no
 *     section_audios entries are present. The audio guard already rejects
 *     these at request time but the user shouldn't even see them attempted.
 *     This keeps the pool small but functional until audio assets are
 *     uploaded for the rest.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Part 1: normalise double-encoded metadata ────────────────────
        $rows = DB::table('questions')
            ->where('type', 'listening')
            ->select('id', 'metadata')
            ->get();

        foreach ($rows as $row) {
            $raw = $row->metadata;
            if (!is_string($raw)) {
                continue;
            }

            // Try to detect double-encoding: outer parse yields a string
            // that itself looks like JSON.
            $first = json_decode($raw, true);
            if (is_string($first) && (str_starts_with(ltrim($first), '{') || str_starts_with(ltrim($first), '['))) {
                $inner = json_decode($first, true);
                if (is_array($inner)) {
                    DB::table('questions')
                        ->where('id', $row->id)
                        ->update(['metadata' => json_encode($inner)]);
                    Log::info('Normalised double-encoded listening metadata', ['question_id' => $row->id]);
                }
            }
        }

        // ── Part 2: deactivate silent listening questions ────────────────
        // Inactive flag is reversible; nothing destructive.
        $silent = DB::table('questions')
            ->where('type', 'listening')
            ->where('active', 1)
            ->get(['id', 'metadata']);

        $deactivated = [];
        foreach ($silent as $q) {
            $meta = is_string($q->metadata) ? (json_decode($q->metadata, true) ?: []) : (array) $q->metadata;
            $hasAudio = !empty($meta['audio_url']) || !empty($meta['section_audios']);
            if (!$hasAudio) {
                DB::table('questions')->where('id', $q->id)->update(['active' => 0]);
                $deactivated[] = $q->id;
            }
        }

        if ($deactivated) {
            Log::info('Deactivated silent listening questions', ['ids' => $deactivated, 'count' => count($deactivated)]);
        }
    }

    public function down(): void
    {
        // Re-activate everything we deactivated (best-effort — we don't
        // store the prior state, but listening questions default to active).
        // Skip metadata re-encoding: the normalised form is strictly better.
        // No-op intentionally.
    }
};
