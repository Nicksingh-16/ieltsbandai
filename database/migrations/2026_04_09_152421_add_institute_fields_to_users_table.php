<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institute_id')->nullable()->after('is_admin')
                ->constrained('institutes')->nullOnDelete();
            $table->enum('institute_role', ['owner', 'teacher', 'student'])
                ->nullable()->after('institute_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropColumn(['institute_id', 'institute_role']);
        });
    }
};
