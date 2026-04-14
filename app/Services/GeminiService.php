<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    const CACHE_KEY = 'gemini_key_index';
    const BASE_URL  = 'https://generativelanguage.googleapis.com/v1beta/models';
    const MODEL     = 'gemini-1.5-flash';

    /** @var string[] */
    private array $keys;

    public function __construct()
    {
        $raw = config('services.gemini.api_keys', '');
        $this->keys = array_filter(array_map('trim', explode(',', $raw)));
    }

    public function isAvailable(): bool
    {
        return count($this->keys) > 0;
    }

    /**
     * Pick next key via round-robin and return it.
     */
    private function nextKey(): string
    {
        $total = count($this->keys);
        $index = (int) Cache::get(self::CACHE_KEY, 0);
        $key   = $this->keys[$index % $total];
        Cache::put(self::CACHE_KEY, ($index + 1) % $total, now()->addDays(1));
        return $key;
    }

    /**
     * Send a plain text prompt and return the text response.
     */
    public function generate(string $prompt, float $temperature = 0.4, int $maxTokens = 800): ?string
    {
        if (!$this->isAvailable()) {
            Log::warning('GeminiService: no API keys configured.');
            return null;
        }

        $apiKey = $this->nextKey();
        $url    = self::BASE_URL . '/' . self::MODEL . ':generateContent?key=' . $apiKey;

        try {
            $response = Http::timeout(45)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature'     => $temperature,
                    'maxOutputTokens' => $maxTokens,
                ],
            ]);

            if (!$response->successful()) {
                Log::error('GeminiService API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'key_index' => array_search($apiKey, $this->keys),
                ]);
                return null;
            }

            return $response->json('candidates.0.content.parts.0.text');

        } catch (\Throwable $e) {
            Log::error('GeminiService exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a prompt and parse the response as JSON.
     */
    public function generateJson(string $prompt, float $temperature = 0.2, int $maxTokens = 1500): ?array
    {
        $jsonPrompt = $prompt . "\n\nReturn ONLY valid JSON. No markdown. No code blocks.";
        $text = $this->generate($jsonPrompt, $temperature, $maxTokens);

        if (!$text) return null;

        // Strip markdown code blocks if present
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/i', '', $text);

        $decoded = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('GeminiService: invalid JSON response', ['raw' => $text]);
            return null;
        }

        return $decoded;
    }
}
