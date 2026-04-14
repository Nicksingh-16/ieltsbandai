<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tests MODIFY COLUMN type ENUM('speaking', 'writing', 'listening', 'reading') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tests MODIFY COLUMN type ENUM('speaking', 'writing') NOT NULL");
    }
};
