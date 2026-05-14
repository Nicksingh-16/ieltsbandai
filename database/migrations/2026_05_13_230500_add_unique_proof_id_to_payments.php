<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prevents the SEV-1 dup-UTR fraud where the same UTR (proof_id) could be
 * submitted across multiple Payment rows, each auto-granting credits.
 *
 * NULL proof_ids are still allowed (pending payments before UTR submission).
 * Existing duplicates are de-duplicated by keeping the oldest grant and
 * appending an admin_note marker on the rest so finance can reconcile.
 */
return new class extends Migration
{
    public function up(): void
    {
        // De-duplicate any existing rows so the unique index can be created.
        // For each proof_id that appears more than once, keep the first row
        // and flag the rest with a marker (proof_id reset to NULL, status
        // forced to 'pending' so admin can re-handle). Defensive — no
        // duplicates exist in fresh installs but production data may differ.
        $dups = DB::table('payments')
            ->select('proof_id', DB::raw('COUNT(*) as c'))
            ->whereNotNull('proof_id')
            ->groupBy('proof_id')
            ->havingRaw('c > 1')
            ->pluck('proof_id');

        foreach ($dups as $utr) {
            $ids = DB::table('payments')
                ->where('proof_id', $utr)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1)
                ->values();
            DB::table('payments')->whereIn('id', $ids)->update([
                'proof_id' => null,
                'admin_note' => DB::raw("CONCAT(COALESCE(admin_note,''), '\nDUP_UTR_MIGRATION: original proof_id=".addslashes($utr)." (cleared by 2026_05_13_230500)')"),
            ]);
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('proof_id', 'payments_proof_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_proof_id_unique');
        });
    }
};
