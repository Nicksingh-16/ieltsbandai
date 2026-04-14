<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigned_test_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Filled once the student completes the test
            $table->foreignId('test_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('status', ['pending', 'started', 'completed', 'skipped'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['assigned_test_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assigned_test_students');
    }
};
