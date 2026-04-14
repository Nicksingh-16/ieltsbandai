<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(0);
            $table->json('config')->nullable(); // per-slot overrides: time_limit, instructions
            $table->timestamps();

            $table->unique(['test_template_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_questions');
    }
};
