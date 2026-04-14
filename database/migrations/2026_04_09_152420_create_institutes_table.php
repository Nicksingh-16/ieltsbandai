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
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('plan', ['free', 'starter', 'pro'])->default('free');
            $table->unsignedInteger('seat_limit')->default(10);
            $table->unsignedInteger('seats_used')->default(0);
            $table->string('contact_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
