<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores LanguageTool grammar matches per audio file. Computed in
 * TranscribeAudioJob after the transcript is saved, so re-rendering the
 * speaking result page is a static lookup — no recomputation on every page
 * view.
 *
 * Shape per row (JSON):
 *   [
 *     { "offset": 23, "length": 4, "message": "...", "replacements": ["was"], "category": "GRAMMAR" },
 *     ...
 *   ]
 *
 * Null when LanguageTool was unavailable at transcription time (Docker
 * not running on the VPS yet etc.); the blade falls back to no grammar
 * panel without breaking. Backfill via a re-transcribe if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->json('grammar_matches')->nullable()->after('transcript_words');
        });
    }

    public function down(): void
    {
        Schema::table('audio_files', function (Blueprint $table) {
            $table->dropColumn('grammar_matches');
        });
    }
};
