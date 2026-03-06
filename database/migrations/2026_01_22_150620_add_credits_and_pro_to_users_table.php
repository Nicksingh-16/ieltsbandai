<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('test_credits')->default(3)->after('password');
            $table->boolean('is_pro')->default(false)->after('test_credits');
            $table->timestamp('pro_expires_at')->nullable()->after('is_pro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['test_credits', 'is_pro', 'pro_expires_at']);
        });
    }
};
