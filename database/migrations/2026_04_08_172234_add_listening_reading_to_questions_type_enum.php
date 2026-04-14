<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('speaking', 'writing', 'listening', 'reading') NOT NULL");
        DB::statement("ALTER TABLE questions MODIFY COLUMN category ENUM(
            'speaking_part1', 'speaking_part2', 'speaking_part3',
            'writing_academic_task1', 'writing_academic_task2',
            'writing_general_task1', 'writing_general_task2',
            'listening_academic', 'listening_general',
            'reading_academic', 'reading_general'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('speaking', 'writing') NOT NULL");
        DB::statement("ALTER TABLE questions MODIFY COLUMN category ENUM(
            'speaking_part1', 'speaking_part2', 'speaking_part3',
            'writing_academic_task1', 'writing_academic_task2',
            'writing_general_task1', 'writing_general_task2'
        ) NOT NULL");
    }
};
