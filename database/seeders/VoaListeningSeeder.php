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
        $specs = [
            'database/seeders/data/voa_listening_poc.json',  // original POC
            'database/seeders/data/voa_test_02.json',        // Idioms, Gettysburg, Microcredentials, Moon
            'database/seeders/data/voa_test_03.json',        // Overcommitment, Everest, Dementia, Asteroid
            'database/seeders/data/voa_test_04.json',        // Instincts, Yellowstone, Saying No, Mars
            'database/seeders/data/voa_test_05.json',        // Eagle Eyes, Monkeys, Assistive AI, Toyota
            'database/seeders/data/voa_test_06.json',        // Power Couple, Monarchs, DST, Stonehenge
            'database/seeders/data/voa_test_07.json',        // Dialed In, Pacific Rower, Vision, Fusion
            'database/seeders/data/voa_test_08.json',        // Kitchen-Table Politics, Yosemite, ADHD, Pearl S. Buck
        ];

        $seeded = 0;
        $skipped = 0;
        $failed  = 0;

        foreach ($specs as $spec) {
            if (!file_exists(base_path($spec))) {
                $this->command?->warn("VOA spec missing at {$spec} — skipping.");
                $skipped++;
                continue;
            }

            $exitCode = Artisan::call('ingest:voa-listening', [
                'path'    => $spec,
                '--force' => true,
            ]);

            $output = Artisan::output();
            if ($exitCode === 0) {
                $seeded++;
                $this->command?->line('  ✓ ' . basename($spec));
            } else {
                $failed++;
                $this->command?->error('  ✗ ' . basename($spec) . " (exit {$exitCode})");
                if ($output) {
                    $this->command?->line(trim($output));
                }
            }
        }

        $this->command?->info("VOA listening: {$seeded} seeded, {$skipped} skipped, {$failed} failed.");
    }
}
