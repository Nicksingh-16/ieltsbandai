<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','evaluating','completed','failed') NOT NULL DEFAULT 'created'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tests MODIFY COLUMN status ENUM('created','in_progress','processing','completed','failed') NOT NULL DEFAULT 'created'");
    }
};
