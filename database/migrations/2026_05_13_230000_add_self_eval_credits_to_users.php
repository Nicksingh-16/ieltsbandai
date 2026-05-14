<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Self-evaluation feature credits.
 *
 * Separate pool from test_credits — the self-eval page lets users paste their
 * own Task 2 question + answer and get an AI evaluation. Each user gets 5 free
 * self-evals; after that the paywall offers a `self_eval_single` plan at ₹10.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('self_eval_credits')->default(5)->after('test_credits');
        });

        // Backfill existing users so the feature works for anyone who signed up
        // before this migration (they'd otherwise see 0 credits and hit paywall).
        \Illuminate\Support\Facades\DB::table('users')
            ->where('self_eval_credits', 0)
            ->update(['self_eval_credits' => 5]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('self_eval_credits');
        });
    }
};
