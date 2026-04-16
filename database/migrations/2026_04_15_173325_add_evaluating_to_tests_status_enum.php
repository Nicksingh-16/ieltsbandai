<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
            return;
        }

        // PostgreSQL: drop old check constraint and add updated one
        DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
        DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','completed','failed'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
            return;
        }

        DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
        DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','completed','failed'))");
    }
};
