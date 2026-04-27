<?php

namespace Database\Seeders;

use App\Models\CalibratedEssay;
use Illuminate\Database\Seeder;

class CalibratedEssaySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('database/seeders/data/calibrated_essays_master.json');

        if (! file_exists($jsonPath)) {
            $this->command->error("Master JSON not found at: {$jsonPath}");
            $this->command->info('Place the consolidated calibration JSON there before seeding.');
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($data) || ! isset($data['essays'])) {
            $this->command->error('Invalid JSON structure. Expected: {"essays": [...]}');
            return;
        }

        $imported = 0;
        $skipped = 0;
        $totalEssays = count($data['essays']);

        // Deterministic ~20% holdout: stable across re-seeds and robust to new
        // essays being added later. Required for benchmark comparability — a
        // shuffled holdout would silently invalidate every prior benchmark run.
        $holdoutIds = array_values(array_filter(
            array_column($data['essays'], 'id'),
            fn ($id) => crc32((string) $id) % 5 === 0
        ));
        $holdoutCount = count($holdoutIds);

        foreach ($data['essays'] as $essay) {
            try {
                CalibratedEssay::updateOrCreate(
                    ['external_id' => $essay['id']],
                    [
                        'source_book' => $essay['source_book'] ?? 'Unknown',
                        'test_number' => $essay['test_number'] ?? 0,
                        'task_type' => $this->normalizeTaskType($essay['task_type'] ?? ''),
                        'task_description' => $essay['task_description'] ?? null,
                        'essay_text' => $essay['essay_text'] ?? '',
                        'topic_keywords' => $essay['topic_keywords'] ?? [],
                        'band_overall' => $essay['band_overall'] ?? 0,
                        'band_ta' => $essay['band_ta'] ?? null,
                        'band_cc' => $essay['band_cc'] ?? null,
                        'band_lr' => $essay['band_lr'] ?? null,
                        'band_gra' => $essay['band_gra'] ?? null,
                        'examiner_notes' => $essay['examiner_notes'] ?? null,
                        'word_count' => str_word_count($essay['essay_text'] ?? ''),
                        'is_holdout' => in_array($essay['id'], $holdoutIds),
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $this->command->warn("Skipped {$essay['id']}: {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->command->info("Imported {$imported} calibrated essays.");
        $this->command->info("Holdout set (for benchmarking): {$holdoutCount} essays.");
        if ($skipped > 0) {
            $this->command->warn("Skipped {$skipped} essays due to errors.");
        }
    }

    private function normalizeTaskType(string $type): string
    {
        $map = [
            'writing_task_1' => 'writing_task_1_academic',
            'writing_task_1_academic' => 'writing_task_1_academic',
            'writing_task_1_general' => 'writing_task_1_general',
            'writing_task_2' => 'writing_task_2',
            'speaking_part_1' => 'speaking_part_1',
            'speaking_part_2' => 'speaking_part_2',
            'speaking_part_3' => 'speaking_part_3',
        ];

        return $map[$type] ?? 'writing_task_2';
    }
}
