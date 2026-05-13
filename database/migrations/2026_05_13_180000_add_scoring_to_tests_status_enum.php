<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * TranscribeAudioJob marks the test as 'scoring' once all three speaking
 * transcripts are ready (so a parallel TranscribeAudioJob doesn't double-
 * dispatch SpeakingScoreJob). The frontend speaking/result.blade.php also
 * renders a dedicated "AI Examiner is scoring" UI state when status===scoring.
 *
 * The previous enum migration (2026_04_15) added 'evaluating' but not
 * 'scoring', so production MySQL truncated the value and the speaking
 * pipeline silently failed at the last hop. This migration adds 'scoring'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','scoring','completed','failed') NOT NULL DEFAULT 'created'");
            return;
        }

        // PostgreSQL: drop old check constraint and add updated one
        DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
        DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','scoring','completed','failed'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
            return;
        }

        DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
        DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','completed','failed'))");
    }
};
