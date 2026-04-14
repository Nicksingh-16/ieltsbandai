<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN type ENUM('speaking', 'writing', 'listening', 'reading') NOT NULL");
        } else {
            DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_type_check");
            DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_type_check CHECK (type IN ('speaking', 'writing', 'listening', 'reading'))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tests MODIFY COLUMN type ENUM('speaking', 'writing') NOT NULL");
        } else {
            DB::statement("ALTER TABLE tests DROP CONSTRAINT IF EXISTS tests_type_check");
            DB::statement("ALTER TABLE tests ADD CONSTRAINT tests_type_check CHECK (type IN ('speaking', 'writing'))");
        }
    }
};
