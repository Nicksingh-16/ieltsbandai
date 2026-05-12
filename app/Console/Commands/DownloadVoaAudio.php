<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Download remote audio URLs in a listening question's metadata.section_audios
 * into local public storage, then rewrite the metadata so the test view serves
 * from your own domain instead of streaming from VOA's CDN.
 *
 * Why: hot-linking is legal (VOA = public domain) and free, but if VOA renames
 * or removes an episode the test breaks. Local copies make tests durable.
 *
 * Usage:
 *   php artisan voa:download 129              # download for one question
 *   php artisan voa:download --all            # all listening questions with remote audio
 *   php artisan voa:download 129 --dry-run    # show plan without writing
 *
 * Files land under storage/app/public/listening/voa/{question_id}/sN.mp3 and
 * are exposed at /storage/listening/voa/{question_id}/sN.mp3 via the public
 * symlink (run `php artisan storage:link` if not already done).
 */
class DownloadVoaAudio extends Command
{
    protected $signature = 'voa:download
        {question_id? : Question ID to process (omit if using --all)}
        {--all : Process every listening question with remote audio URLs}
        {--dry-run : Print the plan without downloading or saving}
        {--force : Re-download even if local file already exists}';

    protected $description = 'Download remote VOA audio into local public storage and rewrite the question metadata.';

    private const REMOTE_PREFIXES = [
        'http://', 'https://',
    ];

    public function handle(): int
    {
        $ids = $this->resolveQuestionIds();
        if (empty($ids)) {
            $this->error('No matching listening questions found.');
            return self::FAILURE;
        }

        $this->info('Processing ' . count($ids) . ' question(s)' . ($this->option('dry-run') ? ' (dry-run)' : ''));
        $this->newLine();

        $okCount = 0;
        $failCount = 0;
        foreach ($ids as $id) {
            try {
                if ($this->processOne((int) $id)) {
                    $okCount++;
                } else {
                    $failCount++;
                }
            } catch (\Throwable $e) {
                $this->error("Question {$id} failed: " . $e->getMessage());
                $failCount++;
            }
            $this->newLine();
        }

        $this->line(sprintf('Done. %d ok, %d failed.', $okCount, $failCount));
        return $failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveQuestionIds(): array
    {
        if ($this->option('all')) {
            return Question::where('type', 'listening')
                ->whereNotNull('metadata')
                ->pluck('id')
                ->all();
        }

        $id = $this->argument('question_id');
        if (!$id) {
            $this->error('Provide a question_id argument or use --all.');
            return [];
        }
        return [(int) $id];
    }

    private function processOne(int $questionId): bool
    {
        $q = Question::find($questionId);
        if (!$q) {
            $this->error("Question #{$questionId} not found.");
            return false;
        }

        $meta = json_decode($q->metadata, true);
        if (!is_array($meta)) {
            $this->warn("Question #{$questionId} has no/invalid metadata. Skipping.");
            return false;
        }

        $audios = $meta['section_audios'] ?? null;
        if (!is_array($audios) || count($audios) === 0) {
            $this->warn("Question #{$questionId} has no section_audios. Skipping.");
            return false;
        }

        $this->line("Question #{$questionId}: {$q->title}");

        $localDirRel = "listening/voa/{$questionId}";
        $publicDisk = Storage::disk('public');

        if (!$this->option('dry-run')) {
            $publicDisk->makeDirectory($localDirRel);
        }

        $newAudios = [];
        foreach ($audios as $idx => $url) {
            $section = $idx + 1;
            $isRemote = $this->isRemote($url);

            if (!$isRemote) {
                $this->line("  S{$section}: already local — {$url}");
                $newAudios[] = $url;
                continue;
            }

            $filename = "s{$section}.mp3";
            $relPath  = "{$localDirRel}/{$filename}";
            $publicUrl = '/storage/' . $relPath;
            $fullPath = $publicDisk->path($relPath);

            if ($publicDisk->exists($relPath) && !$this->option('force')) {
                $this->line("  S{$section}: exists, skip — {$publicUrl} (" . $this->humanSize($publicDisk->size($relPath)) . ')');
                $newAudios[] = $publicUrl;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  S{$section}: would download → {$publicUrl}");
                $newAudios[] = $publicUrl;
                continue;
            }

            $this->line("  S{$section}: downloading from {$url} …");
            $bytes = $this->downloadTo($url, $fullPath);
            if ($bytes === false) {
                $this->error("  S{$section}: download failed, keeping remote URL");
                $newAudios[] = $url;
                continue;
            }
            $this->line("  S{$section}: saved {$this->humanSize($bytes)} → {$publicUrl}");
            $newAudios[] = $publicUrl;
        }

        $meta['section_audios'] = $newAudios;
        // Keep audio_url pointing at first section (legacy callers).
        $meta['audio_url'] = $newAudios[0] ?? $meta['audio_url'] ?? null;

        if ($this->option('dry-run')) {
            $this->line('  (dry-run, metadata not written)');
            return true;
        }

        $q->update(['metadata' => json_encode($meta)]);
        $this->info("  ✓ Metadata updated for question #{$questionId}");
        return true;
    }

    private function isRemote(string $url): bool
    {
        foreach (self::REMOTE_PREFIXES as $p) {
            if (str_starts_with($url, $p)) {
                return true;
            }
        }
        return false;
    }

    private function downloadTo(string $url, string $localFullPath): int|false
    {
        try {
            // Stream the download so a 20MB mp3 doesn't sit in memory.
            $resp = Http::withOptions([
                'sink'    => $localFullPath,
                'timeout' => 300,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 IELTS-BandAI/1.0',
            ])->get($url);

            if (!$resp->successful()) {
                @unlink($localFullPath);
                return false;
            }
            return filesize($localFullPath) ?: 0;
        } catch (\Throwable $e) {
            @unlink($localFullPath);
            $this->error('    ' . $e->getMessage());
            return false;
        }
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1024 / 1024, 1) . ' MB';
    }
}
