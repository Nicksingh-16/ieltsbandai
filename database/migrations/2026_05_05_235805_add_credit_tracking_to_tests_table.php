<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->timestamp('credit_charged_at')->nullable()->after('completed_at');
            $table->timestamp('credit_refunded_at')->nullable()->after('credit_charged_at');
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn(['credit_charged_at', 'credit_refunded_at']);
        });
    }
};
