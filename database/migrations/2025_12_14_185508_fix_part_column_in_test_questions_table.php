<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Convert existing string values to integers (if any exist)
        // 1️⃣ Convert existing string values to integers (if any exist)
        // SKIPPED for Postgres Compatibility on fresh install
        // DB::statement("
        //     UPDATE test_questions
        //     SET part = CASE
        //         WHEN part LIKE '%task2%' THEN 2
        //         ELSE 1
        //     END
        //     WHERE part NOT REGEXP '^[0-9]+$'
        // ");

        // 2️⃣ Ensure column is INTEGER
        Schema::table('test_questions', function (Blueprint $table) {
            $table->integer('part')->change();
        });
    }

    public function down(): void
    {
        // Rollback: change back to string (only if really needed)
        Schema::table('test_questions', function (Blueprint $table) {
            $table->string('part')->change();
        });
    }
};
