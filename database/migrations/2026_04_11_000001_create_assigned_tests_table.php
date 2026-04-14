<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigned_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('instructions')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('allows_retake')->default(false);
            $table->enum('status', ['draft', 'active', 'closed'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assigned_tests');
    }
};
