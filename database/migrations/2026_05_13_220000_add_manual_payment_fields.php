<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual UPI payment lifecycle fields.
 *
 * We trust-then-verify: when a user submits a UTR the credits/subscription
 * are granted immediately (granted_at stamped) and the payment row is marked
 * pending_verification. An admin later confirms against the bank statement
 * and stamps verified_at, or flags fraud and revokes via admin_note.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // When we granted credits/sub to the user (trust step).
            $table->timestamp('granted_at')->nullable()->after('admin_note')->index();
            // When an admin manually verified the UTR against the bank statement.
            $table->timestamp('verified_at')->nullable()->after('granted_at')->index();
            // Who verified, for audit trail.
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('users')->nullOnDelete();
            // The number of credits granted (or null for subscription plans).
            $table->unsignedInteger('credits_granted')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['granted_at', 'verified_at', 'credits_granted']);
        });
    }
};
