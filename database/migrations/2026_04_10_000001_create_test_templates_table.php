<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['writing', 'speaking', 'listening', 'reading', 'full_mock'])
                  ->default('writing');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // false = only assignable, not self-practice
            $table->json('metadata')->nullable(); // instructions, difficulty, tags, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_templates');
    }
};
