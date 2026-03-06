<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_scores', function (Blueprint $table) {
            $table->string('criteria', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('test_scores', function (Blueprint $table) {
            $table->string('criteria', 20)->change();
        });
    }
};
