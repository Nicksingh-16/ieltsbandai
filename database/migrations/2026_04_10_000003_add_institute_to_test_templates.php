<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_templates', function (Blueprint $table) {
            $table->foreignId('institute_id')->nullable()->after('id')
                  ->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('institute_id')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_templates', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['institute_id', 'created_by']);
        });
    }
};
