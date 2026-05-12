<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('feedbacks')) {
            return;
        }
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->enum('category', ['bug', 'feature', 'scoring', 'general'])->default('general');
            $table->tinyInteger('rating')->nullable(); // 1–5
            $table->text('message');
            $table->string('page_url', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip')->nullable();
            $table->enum('status', ['new', 'reviewing', 'resolved', 'dismissed'])->default('new');
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
