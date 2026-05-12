<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('llm_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('test_id')->nullable()->index();
            $table->string('provider', 32)->index();   // openai / groq / gemini
            $table->string('model', 64)->index();
            $table->string('purpose', 64)->nullable(); // writing_score / speaking_score / clarify etc
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0); // 6 decimals = sub-millicent precision
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('ok')->default(true);
            $table->timestamp('created_at')->index();

            $table->index(['provider', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_call_logs');
    }
};
