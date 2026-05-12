<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('llm_call_logs', function (Blueprint $table) {
            $table->string('prompt_version', 16)->nullable()->after('purpose')->index();
        });
    }

    public function down(): void
    {
        Schema::table('llm_call_logs', function (Blueprint $table) {
            $table->dropIndex(['prompt_version']);
            $table->dropColumn('prompt_version');
        });
    }
};
