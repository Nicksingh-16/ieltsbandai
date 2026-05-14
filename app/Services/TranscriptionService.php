<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscriptionService
{
    protected $apiKey;

    protected $provider;

    protected $uploadTimeout = 120;

    protected $transcriptionTimeout = 180;

    public function __construct()
    {
        $this->provider = config('services.transcription.provider', 'assemblyai');

        if ($this->provider === 'deepgram') {
            $this->apiKey = config('services.deepgram.api_key');
        } else {
            $this->apiKey = config('services.assemblyai.api_key');
        }

        if (! $this->apiKey) {
            Log::error('No API key configured for transcription provider: '.$this->provider);
            throw new \Exception('Transcription API key not configured for provider: '.$this->provider);
        }
    }

    /**
     * Transcribe an audio file (returns plain transcript string for back-compat).
     * Most callers should prefer transcribeWithWords() to also obtain the
     * word-level timestamps + per-word confidence values used by
     * SpeakingAcousticAnalyzer.
     */
    public function transcribe(string $filePath): ?string
    {
        $result = $this->transcribeWithWords($filePath);

        return $result['text'] ?? null;
    }

    /**
     * Transcribe and return both the text and a normalised words[] array.
     * Each word in the result has the shape:
     *   ['text' => string, 'start' => float seconds, 'end' => float seconds,
     *    'confidence' => float in 0..1]
     *
     * Returns null on transcription failure. Returns ['text' => '...',
     * 'words' => []] if the provider succeeded but returned no word-level
     * data (rare — both AssemblyAI and Deepgram include it by default).
     *
     * @return array{text:string,words:array}|null
     */
    public function transcribeWithWords(string $filePath): ?array
    {
        try {
            // Check if file needs conversion (WebM files often need it)
            $fullPath = Storage::disk('public')->path($filePath);
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            // For WebM files, try to convert to WAV first if FFmpeg is available
            $workingFilePath = $filePath;
            if ($extension === 'webm' && $this->isFFmpegAvailable()) {
                Log::info('Converting WebM to WAV for better compatibility', [
                    'original_file' => $filePath,
                ]);

                $convertedPath = $this->convertToWav($filePath);
                if ($convertedPath) {
                    $workingFilePath = $convertedPath;
                    Log::info('WebM conversion successful', [
                        'converted_file' => $convertedPath,
                    ]);
                } else {
                    Log::warning('WebM conversion failed, will try with original file');
                }
            }

            if ($this->provider === 'deepgram') {
                $result = $this->transcribeWithDeepgram($workingFilePath);

                if (! $result && config('services.assemblyai.api_key')) {
                    Log::info('Deepgram failed, trying AssemblyAI as fallback', [
                        'file_path' => $workingFilePath,
                    ]);
                    $this->apiKey = config('services.assemblyai.api_key');
                    $result = $this->transcribeWithAssemblyAI($workingFilePath);
                }

                if ($workingFilePath !== $filePath) {
                    $this->cleanupConvertedFile($workingFilePath);
                }

                return $result;
            } else {
                $result = $this->transcribeWithAssemblyAI($workingFilePath);

                if (! $result && config('services.deepgram.api_key')) {
                    Log::info('AssemblyAI failed, trying Deepgram as fallback', [
                        'file_path' => $workingFilePath,
                    ]);
                    $this->apiKey = config('services.deepgram.api_key');
                    $result = $this->transcribeWithDeepgram($workingFilePath);
                }

                if ($workingFilePath !== $filePath) {
                    $this->cleanupConvertedFile($workingFilePath);
                }

                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Transcription failed: '.$e->getMessage(), [
                'file_path' => $filePath,
                'provider' => $this->provider,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Check if FFmpeg is available on the system
     */
    protected function isFFmpegAvailable(): bool
    {
        static $available = null;

        if ($available === null) {
            exec('ffmpeg -version 2>&1', $output, $returnCode);
            $available = ($returnCode === 0);

            if (! $available) {
                Log::info('FFmpeg not available - WebM files will be sent directly to transcription services');
            }
        }

        return $available;
    }

    /**
     * Convert WebM to WAV using FFmpeg
     */
    protected function convertToWav(string $webmPath): ?string
    {
        try {
            $fullWebmPath = Storage::disk('public')->path($webmPath);

            // Create WAV filename in the same directory
            $wavFilename = str_replace('.webm', '_converted.wav', basename($webmPath));
            $wavPath = dirname($webmPath).'/'.$wavFilename;
            $fullWavPath = Storage::disk('public')->path($wavPath);

            // FFmpeg command: convert to 16kHz mono WAV (optimal for speech recognition)
            $command = sprintf(
                'ffmpeg -i %s -ar 16000 -ac 1 -c:a pcm_s16le %s 2>&1',
                escapeshellarg($fullWebmPath),
                escapeshellarg($fullWavPath)
            );

            Log::info('Running FFmpeg conversion', [
                'command' => $command,
            ]);

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || ! file_exists($fullWavPath)) {
                Log::error('FFmpeg conversion failed', [
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output),
                ]);

                return null;
            }

            $wavSize = filesize($fullWavPath);
            if ($wavSize === 0) {
                Log::error('FFmpeg created empty WAV file');
                @unlink($fullWavPath);

                return null;
            }

            Log::info('FFmpeg conversion successful', [
                'output_file' => $wavPath,
                'file_size' => $wavSize,
            ]);

            return $wavPath;

        } catch (\Exception $e) {
            Log::error('Error during FFmpeg conversion: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Clean up converted file after transcription
     */
    protected function cleanupConvertedFile(string $filePath): void
    {
        try {
            if (strpos($filePath, '_converted.wav') !== false) {
                $fullPath = Storage::disk('public')->path($filePath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                    Log::info('Cleaned up converted file', ['file' => $filePath]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup converted file: '.$e->getMessage());
        }
    }

    /**
     * Validate audio file
     */
    protected function validateAudioFile(string $fullPath): array
    {
        $fileSize = filesize($fullPath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $info = [
            'valid' => false,
            'size' => $fileSize,
            'extension' => $extension,
        ];

        if ($fileSize === 0) {
            Log::error('Audio file is empty', ['file_path' => $fullPath]);

            return $info;
        }

        if ($fileSize < 100) {
            Log::warning('Audio file suspiciously small', [
                'file_path' => $fullPath,
                'size' => $fileSize,
            ]);
        }

        // For WebM files, do basic validation
        if ($extension === 'webm') {
            $handle = fopen($fullPath, 'rb');
            if ($handle) {
                $header = fread($handle, 4096);
                fclose($handle);

                $info['has_webm_signature'] = (substr($header, 0, 4) === "\x1A\x45\xDF\xA3");
                $info['valid'] = $info['has_webm_signature'] && $fileSize > 100;
            }
        } elseif ($extension === 'wav') {
            // WAV files should have RIFF header
            $handle = fopen($fullPath, 'rb');
            if ($handle) {
                $header = fread($handle, 12);
                fclose($handle);
                $info['valid'] = (substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WAVE');
            }
        } else {
            // For other formats, just check size
            $info['valid'] = $fileSize > 0;
        }

        return $info;
    }

    /**
     * Transcribe using Deepgram API
     */
    protected function transcribeWithDeepgram(string $filePath): ?array
    {
        if (! $this->apiKey) {
            Log::error('Deepgram API key not configured');

            return null;
        }

        $fullPath = Storage::disk('public')->path($filePath);

        if (! file_exists($fullPath)) {
            Log::error('Audio file not found: '.$fullPath);

            return null;
        }

        $validation = $this->validateAudioFile($fullPath);
        if (! $validation['valid']) {
            Log::error('Audio file validation failed for Deepgram', [
                'file_path' => $filePath,
                'validation' => $validation,
            ]);

            return null;
        }

        $fileContents = file_get_contents($fullPath);

        if ($fileContents === false || empty($fileContents)) {
            Log::error('Failed to read audio file: '.$fullPath);

            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $contentType = $this->getContentTypeForExtension($extension);

        Log::info('Uploading to Deepgram', [
            'file_path' => $filePath,
            'file_size' => strlen($fileContents),
            'content_type' => $contentType,
        ]);

        // Deepgram takes parameters via URL query string, not body. The previous
        // call passed them as the second arg to ->post(), but withBody() had
        // already claimed the body slot — so Laravel's HTTP client silently
        // discarded the array. That meant Deepgram defaulted to its weak base
        // model and produced low-accuracy transcripts (hallucinated text on
        // accented / browser-mic recordings). Building the URL explicitly so
        // these params actually reach Deepgram.
        $deepgramUrl = 'https://api.deepgram.com/v1/listen?'.http_build_query([
            'model' => 'nova-2',
            'language' => 'en',
            'punctuate' => 'true',
            'diarize' => 'false',
            'smart_format' => 'true',
            'utterances' => 'false',
            'numerals' => 'true',
        ]);

        try {
            $response = Http::timeout($this->uploadTimeout)
                ->withHeaders([
                    'Authorization' => 'Token '.$this->apiKey,
                    'Content-Type' => $contentType,
                ])
                ->withBody($fileContents, $contentType)
                ->post($deepgramUrl);

            if (! $response->successful()) {
                Log::error('Deepgram API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'file_path' => $filePath,
                ]);

                return null;
            }

            $data = $response->json();
            $alt = $data['results']['channels'][0]['alternatives'][0] ?? null;
            $transcript = $alt['transcript'] ?? null;

            if (! $transcript) {
                return null;
            }

            $rawWords = $alt['words'] ?? [];
            $words = [];
            foreach ($rawWords as $w) {
                if (! isset($w['start'], $w['end'])) {
                    continue;
                }
                // Deepgram prefers "punctuated_word" when smart_format is on,
                // falling back to "word".
                $text = $w['punctuated_word'] ?? ($w['word'] ?? '');
                $words[] = [
                    'text' => (string) $text,
                    'start' => (float) $w['start'],
                    'end' => (float) $w['end'],
                    'confidence' => isset($w['confidence']) ? (float) $w['confidence'] : 0.0,
                ];
            }

            Log::info('Deepgram transcription successful', [
                'transcript_length' => strlen($transcript),
                'word_count' => count($words),
            ]);

            return [
                'text' => trim($transcript),
                'words' => $words,
            ];

        } catch (\Exception $e) {
            Log::error('Deepgram transcription exception: '.$e->getMessage(), [
                'file_path' => $filePath,
            ]);

            return null;
        }
    }

    /**
     * Get content type for file extension
     */
    protected function getContentTypeForExtension(string $extension): string
    {
        $types = [
            'webm' => 'audio/webm;codecs=opus',
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/m4a',
            'flac' => 'audio/flac',
        ];

        return $types[$extension] ?? 'audio/webm';
    }

    /**
     * Transcribe using AssemblyAI API
     */
    protected function transcribeWithAssemblyAI(string $filePath): ?array
    {
        if (! $this->apiKey) {
            Log::error('AssemblyAI API key not configured');

            return null;
        }

        $fullPath = Storage::disk('public')->path($filePath);

        if (! file_exists($fullPath)) {
            Log::error('Audio file not found: '.$fullPath);

            return null;
        }

        $validation = $this->validateAudioFile($fullPath);
        if (! $validation['valid']) {
            Log::error('Audio file validation failed for AssemblyAI', [
                'file_path' => $filePath,
                'validation' => $validation,
            ]);

            return null;
        }

        try {
            $fileContents = file_get_contents($fullPath);

            if ($fileContents === false || empty($fileContents)) {
                Log::error('Failed to read audio file contents: '.$fullPath);

                return null;
            }

            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $contentType = $this->getContentTypeForExtension($extension);

            Log::info('Uploading to AssemblyAI', [
                'file_path' => $filePath,
                'file_size' => strlen($fileContents),
                'content_type' => $contentType,
            ]);

            // Upload file
            $uploadResponse = Http::timeout($this->uploadTimeout)
                ->withHeaders([
                    'authorization' => $this->apiKey,
                ])
                ->withBody($fileContents, $contentType)
                ->post('https://api.assemblyai.com/v2/upload');

            if (! $uploadResponse->successful()) {
                Log::error('AssemblyAI upload error', [
                    'status' => $uploadResponse->status(),
                    'body' => $uploadResponse->body(),
                    'file_size' => strlen($fileContents),
                    'content_type' => $contentType,
                ]);

                return null;
            }

            $uploadData = $uploadResponse->json();
            $uploadUrl = $uploadData['upload_url'] ?? null;

            if (! $uploadUrl) {
                Log::error('AssemblyAI did not return upload_url', [
                    'response' => $uploadData,
                ]);

                return null;
            }

            Log::info('AssemblyAI upload successful', [
                'upload_url' => $uploadUrl,
            ]);

            // Start transcription
            $transcribeResponse = Http::timeout(30)
                ->withHeaders([
                    'authorization' => $this->apiKey,
                    'content-type' => 'application/json',
                ])
                ->post('https://api.assemblyai.com/v2/transcript', [
                    'audio_url' => $uploadUrl,
                    'language_code' => 'en',
                    'punctuate' => true,
                    'format_text' => true,
                ]);

            if (! $transcribeResponse->successful()) {
                Log::error('AssemblyAI transcription start error', [
                    'status' => $transcribeResponse->status(),
                    'body' => $transcribeResponse->body(),
                ]);

                return null;
            }

            $transcriptData = $transcribeResponse->json();
            $transcriptId = $transcriptData['id'] ?? null;

            if (! $transcriptId) {
                Log::error('AssemblyAI did not return transcript ID', [
                    'response' => $transcriptData,
                ]);

                return null;
            }

            Log::info('AssemblyAI transcription started', [
                'transcript_id' => $transcriptId,
            ]);

            // Poll for completion
            $maxAttempts = 90;
            $attempt = 0;

            while ($attempt < $maxAttempts) {
                sleep(2);

                $statusResponse = Http::timeout(30)
                    ->withHeaders([
                        'authorization' => $this->apiKey,
                    ])
                    ->get("https://api.assemblyai.com/v2/transcript/{$transcriptId}");

                if (! $statusResponse->successful()) {
                    Log::error('AssemblyAI status check error', [
                        'status' => $statusResponse->status(),
                        'attempt' => $attempt,
                    ]);
                    $attempt++;

                    continue;
                }

                $statusData = $statusResponse->json();
                $status = $statusData['status'] ?? 'unknown';

                if ($status === 'completed') {
                    $text = $statusData['text'] ?? '';

                    if (! $text) {
                        return null;
                    }

                    // AssemblyAI returns word timestamps in milliseconds —
                    // normalise to seconds to match Deepgram + downstream
                    // SpeakingAcousticAnalyzer expectations.
                    $rawWords = $statusData['words'] ?? [];
                    $words = [];
                    foreach ($rawWords as $w) {
                        if (! isset($w['start'], $w['end'])) {
                            continue;
                        }
                        $words[] = [
                            'text' => (string) ($w['text'] ?? ''),
                            'start' => ((float) $w['start']) / 1000.0,
                            'end' => ((float) $w['end']) / 1000.0,
                            'confidence' => isset($w['confidence']) ? (float) $w['confidence'] : 0.0,
                        ];
                    }

                    Log::info('AssemblyAI transcription completed', [
                        'transcript_id' => $transcriptId,
                        'text_length' => strlen($text),
                        'word_count' => count($words),
                    ]);

                    return [
                        'text' => trim($text),
                        'words' => $words,
                    ];
                }

                if ($status === 'error') {
                    $error = $statusData['error'] ?? 'Unknown error';
                    Log::error('AssemblyAI transcription error', [
                        'transcript_id' => $transcriptId,
                        'error' => $error,
                    ]);

                    return null;
                }

                $attempt++;
            }

            Log::error('AssemblyAI transcription timeout', [
                'transcript_id' => $transcriptId,
                'attempts' => $maxAttempts,
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('AssemblyAI transcription exception: '.$e->getMessage(), [
                'file_path' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
