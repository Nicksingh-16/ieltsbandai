<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 64)->index();
            $table->json('properties')->nullable();
            $table->string('session_id', 64)->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->index();

            $table->index(['user_id', 'event']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_events');
    }
};
