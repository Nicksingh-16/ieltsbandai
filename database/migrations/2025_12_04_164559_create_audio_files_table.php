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
        Schema::create('audio_files', function (Blueprint $table) {
    $table->id();
    $table->foreignId('test_id')->constrained()->cascadeOnDelete();
    $table->string('file_url');
    $table->integer('duration_seconds')->nullable();
    $table->integer('size_kb')->nullable();
    $table->longText('transcript')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
