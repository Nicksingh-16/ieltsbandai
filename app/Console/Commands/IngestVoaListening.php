<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

/**
 * Ingest a JSON spec describing a VOA-sourced IELTS listening test into the
 * Question table in the shape `ListeningTestController` expects.
 *
 * The spec format (see database/seeders/data/voa_listening_poc.json):
 *   {
 *     "title": "...",
 *     "content": "summary shown in admin",
 *     "category": "listening_academic",
 *     "section_audios": [ "https://...mp3", ... 4 urls ],
 *     "attribution": "Audio courtesy of VOA Learning English — public domain",
 *     "questions": [ { id, section, type, ... }, ... ]
 *   }
 *
 * Idempotent: re-running with the same title overwrites the metadata in place.
 */
class IngestVoaListening extends Command
{
    protected $signature = 'ingest:voa-listening
        {path : Path to JSON spec, relative to base_path() or absolute}
        {--force : Overwrite existing question with the same title}';

    protected $description = 'Ingest a VOA-sourced IELTS listening test JSON spec into the Question table.';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!str_starts_with($path, '/') && !preg_match('#^[A-Z]:#', $path)) {
            $path = base_path($path);
        }

        if (!file_exists($path)) {
            $this->error("Spec not found: {$path}");
            return self::FAILURE;
        }

        $spec = json_decode(file_get_contents($path), true);
        if (!$spec) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return self::FAILURE;
        }

        foreach (['title', 'category', 'section_audios', 'questions'] as $required) {
            if (empty($spec[$required])) {
                $this->error("Spec missing required key: {$required}");
                return self::FAILURE;
            }
        }

        if (!is_array($spec['section_audios']) || count($spec['section_audios']) !== 4) {
            $this->error('section_audios must be exactly 4 mp3 URLs (one per section).');
            return self::FAILURE;
        }

        $existing = Question::where('title', $spec['title'])->first();
        if ($existing && !$this->option('force')) {
            $this->warn("Question already exists with title '{$spec['title']}' (id={$existing->id}). Use --force to overwrite.");
            return self::FAILURE;
        }

        // Backwards-compat: the listening view reads metadata.audio_url for a
        // single concatenated audio. We expose audio_url = first section so
        // legacy tests keep playing, and section_audios = [4 mp3s] which the
        // updated view will prefer when present.
        $metadata = [
            'audio_url'      => $spec['section_audios'][0],
            'section_audios' => $spec['section_audios'],
            'attribution'    => $spec['attribution'] ?? null,
            'source'         => $spec['source'] ?? 'VOA Learning English',
            'license'        => $spec['license'] ?? 'public domain',
            'questions'      => $spec['questions'],
        ];

        $payload = [
            'type'     => 'listening',
            'category' => $spec['category'],
            'title'    => $spec['title'],
            'content'  => $spec['content'] ?? 'IELTS listening test sourced from VOA Learning English.',
            'active'   => true,
            'metadata' => json_encode($metadata),
        ];

        if ($existing) {
            $existing->update($payload);
            $this->info("Updated question #{$existing->id}: {$spec['title']}");
        } else {
            $new = Question::create($payload);
            $this->info("Created question #{$new->id}: {$spec['title']}");
        }

        $totalQs = $this->countAtomicQuestions($spec['questions']);
        $this->line("  Sections: 4");
        $this->line("  Atomic questions: {$totalQs}");
        $this->line("  Audio: " . count($spec['section_audios']) . ' mp3 URLs');

        return self::SUCCESS;
    }

    /**
     * Count atomic marks. matching/fill/mcq = 1 each; diagram_label expands to
     * one mark per label. Mirrors the scoring logic in ListeningTestController.
     */
    private function countAtomicQuestions(array $questions): int
    {
        $count = 0;
        foreach ($questions as $q) {
            $type = $q['type'] ?? 'fill';
            if ($type === 'diagram_label') {
                $count += count($q['labels'] ?? []);
            } else {
                $count++;
            }
        }
        return $count;
    }
}
