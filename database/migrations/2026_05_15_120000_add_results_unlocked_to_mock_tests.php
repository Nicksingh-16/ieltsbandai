<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mock test result gating. Per the user-confirmed pricing model
 * (2 credits charged once at the end), the mock test is FREE to take
 * — no per-module credit deduction — and the final result page is
 * gated behind a single 2-credit charge via mock-test.paywall.
 *
 * results_unlocked flag tracks whether that 2-credit charge has been
 * paid. When false, the result page redirects to the paywall. When
 * true, the writing & speaking evaluations (which were deferred during
 * the mock so we don't pay LLM cost on freeloaders) are dispatched
 * and the result page shows overall band + per-module breakdowns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mock_tests', function (Blueprint $table) {
            $table->boolean('results_unlocked')->default(false)->after('overall_band');
        });
    }

    public function down(): void
    {
        Schema::table('mock_tests', function (Blueprint $table) {
            $table->dropColumn('results_unlocked');
        });
    }
};
