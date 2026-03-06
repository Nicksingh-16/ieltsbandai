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
        
        if (!$this->apiKey) {
            Log::error('No API key configured for transcription provider: ' . $this->provider);
            throw new \Exception('Transcription API key not configured for provider: ' . $this->provider);
        }
    }

    /**
     * Transcribe an audio file
     */
    public function transcribe(string $filePath): ?string
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
                
                if (!$result && config('services.assemblyai.api_key')) {
                    Log::info('Deepgram failed, trying AssemblyAI as fallback', [
                        'file_path' => $workingFilePath,
                    ]);
                    $this->apiKey = config('services.assemblyai.api_key');
                    $result = $this->transcribeWithAssemblyAI($workingFilePath);
                }
                
                // Clean up converted file if it exists
                if ($workingFilePath !== $filePath) {
                    $this->cleanupConvertedFile($workingFilePath);
                }
                
                return $result;
            } else {
                $result = $this->transcribeWithAssemblyAI($workingFilePath);
                
                if (!$result && config('services.deepgram.api_key')) {
                    Log::info('AssemblyAI failed, trying Deepgram as fallback', [
                        'file_path' => $workingFilePath,
                    ]);
                    $this->apiKey = config('services.deepgram.api_key');
                    $result = $this->transcribeWithDeepgram($workingFilePath);
                }
                
                // Clean up converted file if it exists
                if ($workingFilePath !== $filePath) {
                    $this->cleanupConvertedFile($workingFilePath);
                }
                
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Transcription failed: ' . $e->getMessage(), [
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
            
            if (!$available) {
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
            $wavPath = dirname($webmPath) . '/' . $wavFilename;
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
            
            if ($returnCode !== 0 || !file_exists($fullWavPath)) {
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
            Log::error('Error during FFmpeg conversion: ' . $e->getMessage());
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
            Log::warning('Failed to cleanup converted file: ' . $e->getMessage());
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
    protected function transcribeWithDeepgram(string $filePath): ?string
    {
        if (!$this->apiKey) {
            Log::error('Deepgram API key not configured');
            return null;
        }

        $fullPath = Storage::disk('public')->path($filePath);
        
        if (!file_exists($fullPath)) {
            Log::error('Audio file not found: ' . $fullPath);
            return null;
        }

        $validation = $this->validateAudioFile($fullPath);
        if (!$validation['valid']) {
            Log::error('Audio file validation failed for Deepgram', [
                'file_path' => $filePath,
                'validation' => $validation,
            ]);
            return null;
        }

        $fileContents = file_get_contents($fullPath);
        
        if ($fileContents === false || empty($fileContents)) {
            Log::error('Failed to read audio file: ' . $fullPath);
            return null;
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $contentType = $this->getContentTypeForExtension($extension);
        
        Log::info('Uploading to Deepgram', [
            'file_path' => $filePath,
            'file_size' => strlen($fileContents),
            'content_type' => $contentType,
        ]);
        
        try {
            $response = Http::timeout($this->uploadTimeout)
                ->withHeaders([
                    'Authorization' => 'Token ' . $this->apiKey,
                    'Content-Type' => $contentType,
                ])
                ->withBody($fileContents, $contentType)
                ->post('https://api.deepgram.com/v1/listen', [
                    'model' => 'nova-2',
                    'language' => 'en',
                    'punctuate' => true,
                    'diarize' => false,
                    'smart_format' => true,
                ]);

            if (!$response->successful()) {
                Log::error('Deepgram API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'file_path' => $filePath,
                ]);
                return null;
            }

            $data = $response->json();
            $transcript = $data['results']['channels'][0]['alternatives'][0]['transcript'] ?? null;

            if ($transcript) {
                Log::info('Deepgram transcription successful', [
                    'transcript_length' => strlen($transcript),
                ]);
            }

            return $transcript ? trim($transcript) : null;
            
        } catch (\Exception $e) {
            Log::error('Deepgram transcription exception: ' . $e->getMessage(), [
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
    protected function transcribeWithAssemblyAI(string $filePath): ?string
    {
        if (!$this->apiKey) {
            Log::error('AssemblyAI API key not configured');
            return null;
        }

        $fullPath = Storage::disk('public')->path($filePath);
        
        if (!file_exists($fullPath)) {
            Log::error('Audio file not found: ' . $fullPath);
            return null;
        }

        $validation = $this->validateAudioFile($fullPath);
        if (!$validation['valid']) {
            Log::error('Audio file validation failed for AssemblyAI', [
                'file_path' => $filePath,
                'validation' => $validation,
            ]);
            return null;
        }

        try {
            $fileContents = file_get_contents($fullPath);
            
            if ($fileContents === false || empty($fileContents)) {
                Log::error('Failed to read audio file contents: ' . $fullPath);
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
            
            if (!$uploadResponse->successful()) {
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
            
            if (!$uploadUrl) {
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

            if (!$transcribeResponse->successful()) {
                Log::error('AssemblyAI transcription start error', [
                    'status' => $transcribeResponse->status(),
                    'body' => $transcribeResponse->body(),
                ]);
                return null;
            }

            $transcriptData = $transcribeResponse->json();
            $transcriptId = $transcriptData['id'] ?? null;

            if (!$transcriptId) {
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

                if (!$statusResponse->successful()) {
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
                    
                    Log::info('AssemblyAI transcription completed', [
                        'transcript_id' => $transcriptId,
                        'text_length' => strlen($text),
                    ]);
                    
                    return $text ? trim($text) : null;
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
            Log::error('AssemblyAI transcription exception: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}