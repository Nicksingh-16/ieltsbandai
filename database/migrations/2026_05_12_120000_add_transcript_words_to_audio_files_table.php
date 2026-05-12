<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist provider-returned word-level timing + per-word confidence so the
 * SpeakingAcousticAnalyzer service can compute deterministic acoustic
 * signals (WPM, pause stats, filler density, low-confidence rate, speech-
 * rate variance, disfluencies) for the speaking scoring prompt.
 *
 * Stored as a JSON-encoded string in a longText column for cross-database
 * compatibility (MySQL locally + Postgres on Render). Each entry has the
 * normalised shape: { text, start, end, confidence }. Nullable so legacy
 * rows continue to work — the analyzer + caller both gracefully skip
 * acoustic analysis when this column is empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->longText('transcript_words')->nullable()->after('transcript');
        });
    }

    public function down(): void
    {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->dropColumn('transcript_words');
        });
    }
};
