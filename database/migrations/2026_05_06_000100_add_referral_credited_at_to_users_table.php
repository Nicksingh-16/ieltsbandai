<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set when referrer has been credited for this user's first
            // completed test. Prevents both repeat-credit and farm-then-
            // abandon attacks (referrer only earns once a real test is done).
            $table->timestamp('referral_credited_at')->nullable()->after('referral_credits_earned');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_credited_at');
        });
    }
};
