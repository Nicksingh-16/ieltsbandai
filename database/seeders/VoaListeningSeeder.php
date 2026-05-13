<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Wraps the `ingest:voa-listening` command so the VOA Learning English POC
 * listening test (real audio, IELTS-style questions) is seeded on every
 * deploy. Without this, prod has only placeholder listening rows with
 * `audio_url => null`, which the no-audio guard in ListeningTestController
 * blocks at start — every listening test would dead-end.
 *
 * The underlying command is idempotent: re-running overwrites metadata
 * in-place keyed by question title, so this runs safely on each deploy.
 */
class VoaListeningSeeder extends Seeder
{
    public function run(): void
    {
        $spec = 'database/seeders/data/voa_listening_poc.json';
        if (!file_exists(base_path($spec))) {
            $this->command?->warn("VOA spec missing at {$spec} — skipping listening POC seed.");
            return;
        }

        $exitCode = Artisan::call('ingest:voa-listening', [
            'path'    => $spec,
            '--force' => true,
        ]);

        $output = Artisan::output();
        if ($exitCode === 0) {
            $this->command?->info('VOA listening POC seeded.');
            if ($output) {
                $this->command?->line(trim($output));
            }
        } else {
            $this->command?->error("VOA listening seed failed (exit {$exitCode}):");
            $this->command?->line(trim($output));
        }
    }
}
