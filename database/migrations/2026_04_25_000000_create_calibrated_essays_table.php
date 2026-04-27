<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibrated_essays', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique()->comment('e.g. cam19a_t1_w1');
            $table->string('source_book');
            $table->unsignedSmallInteger('test_number');
            $table->enum('task_type', [
                'writing_task_1_academic',
                'writing_task_1_general',
                'writing_task_2',
                'speaking_part_1',
                'speaking_part_2',
                'speaking_part_3',
            ]);
            $table->text('task_description')->nullable();
            $table->text('essay_text');
            $table->json('topic_keywords')->nullable();
            $table->decimal('band_overall', 3, 1);
            $table->decimal('band_ta', 3, 1)->nullable()->comment('Task Achievement / Task Response');
            $table->decimal('band_cc', 3, 1)->nullable()->comment('Coherence and Cohesion');
            $table->decimal('band_lr', 3, 1)->nullable()->comment('Lexical Resource');
            $table->decimal('band_gra', 3, 1)->nullable()->comment('Grammatical Range and Accuracy');
            $table->text('examiner_notes')->nullable();
            $table->json('topic_embedding')->nullable()->comment('Cached embedding for similarity retrieval');
            $table->unsignedInteger('word_count')->nullable();
            $table->boolean('is_holdout')->default(false)->comment('True for benchmarking essays excluded from few-shot retrieval');
            $table->timestamps();

            $table->index(['task_type', 'band_overall'], 'idx_tasktype_band');
            $table->index('source_book');
            $table->index('is_holdout');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibrated_essays');
    }
};
