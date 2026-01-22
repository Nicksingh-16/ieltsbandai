<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('tests', function (Blueprint $table) {
        $table->string('category')->nullable()->after('type');
        $table->string('test_type')->nullable()->after('category');
        $table->foreignId('question_id')->nullable()->after('test_type');
        $table->timestamp('started_at')->nullable()->after('status');
        $table->timestamp('completed_at')->nullable()->after('started_at');
        $table->text('answer')->nullable()->after('completed_at');
        $table->decimal('score', 3, 1)->nullable()->after('answer');
        $table->json('result')->nullable()->after('score');
        $table->json('metadata')->nullable()->after('result');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            //
        });
    }
};
