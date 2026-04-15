<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
        } else {
            DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
            DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','completed','failed'))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
        } else {
            DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_status_check");
            DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_status_check CHECK (status IN ('created','in_progress','processing','evaluating','completed','failed'))");
        }
    }
};
