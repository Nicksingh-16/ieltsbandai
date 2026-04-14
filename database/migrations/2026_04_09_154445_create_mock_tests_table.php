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
        Schema::create('mock_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('test_type', ['academic', 'general'])->default('academic');
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->enum('current_module', ['listening', 'reading', 'writing', 'speaking'])->default('listening');
            // FK to individual test records
            $table->foreignId('listening_test_id')->nullable()->constrained('tests')->nullOnDelete();
            $table->foreignId('reading_test_id')->nullable()->constrained('tests')->nullOnDelete();
            $table->foreignId('writing_test_id')->nullable()->constrained('tests')->nullOnDelete();
            $table->foreignId('speaking_test_id')->nullable()->constrained('tests')->nullOnDelete();
            // Aggregated result
            $table->decimal('listening_band', 3, 1)->nullable();
            $table->decimal('reading_band', 3, 1)->nullable();
            $table->decimal('writing_band', 3, 1)->nullable();
            $table->decimal('speaking_band', 3, 1)->nullable();
            $table->decimal('overall_band', 3, 1)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mock_tests');
    }
};
