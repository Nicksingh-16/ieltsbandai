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
        Schema::create('questions', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['speaking', 'writing']);
    $table->enum('category', [
        'speaking_part1', 'speaking_part2', 'speaking_part3',
        'writing_academic_task1', 'writing_academic_task2',
        'writing_general_task1', 'writing_general_task2'
    ]);
    $table->string('title');
    $table->longText('content');
    $table->string('media_url')->nullable();
    $table->integer('time_limit')->nullable();
    $table->integer('min_words')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
