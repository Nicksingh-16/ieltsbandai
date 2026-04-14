<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('speaking', 'writing', 'listening', 'reading') NOT NULL");
            DB::statement("ALTER TABLE questions MODIFY COLUMN category ENUM(
                'speaking_part1', 'speaking_part2', 'speaking_part3',
                'writing_academic_task1', 'writing_academic_task2',
                'writing_general_task1', 'writing_general_task2',
                'listening_academic', 'listening_general',
                'reading_academic', 'reading_general'
            ) NOT NULL");
        } else {
            DB::statement("ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check");
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type IN ('speaking', 'writing', 'listening', 'reading'))");

            DB::statement("ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_category_check");
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_category_check CHECK (category IN (
                'speaking_part1', 'speaking_part2', 'speaking_part3',
                'writing_academic_task1', 'writing_academic_task2',
                'writing_general_task1', 'writing_general_task2',
                'listening_academic', 'listening_general',
                'reading_academic', 'reading_general'
            ))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('speaking', 'writing') NOT NULL");
            DB::statement("ALTER TABLE questions MODIFY COLUMN category ENUM(
                'speaking_part1', 'speaking_part2', 'speaking_part3',
                'writing_academic_task1', 'writing_academic_task2',
                'writing_general_task1', 'writing_general_task2'
            ) NOT NULL");
        } else {
            DB::statement("ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check");
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type IN ('speaking', 'writing'))");

            DB::statement("ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_category_check");
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_category_check CHECK (category IN (
                'speaking_part1', 'speaking_part2', 'speaking_part3',
                'writing_academic_task1', 'writing_academic_task2',
                'writing_general_task1', 'writing_general_task2'
            ))");
        }
    }
};
