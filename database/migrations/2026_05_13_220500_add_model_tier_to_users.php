<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user model tier — controls which LLM the scoring pipeline routes to.
 *
 *   'standard'  -> gpt-4o-mini  (default, ~$0.002/eval)
 *   'premium'   -> gpt-4o full  (Pro Plus only, ~$0.032/eval, better Band 7+ recognition)
 *
 * Cached on users.model_tier for fast lookup in LLMRouter::chatCompletion.
 * Set by ManualPaymentService::grant when a subscription is activated; reset
 * to 'standard' by revoke() or by the existing CreditService::isPro sync path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('model_tier', 16)->default('standard')->after('is_admin')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['model_tier']);
            $table->dropColumn('model_tier');
        });
    }
};
