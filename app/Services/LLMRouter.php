<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LLM Provider Note
 * -----------------
 * As of L4 the active PRIMARY provider is Groq (Llama 3.3 70B Versatile,
 * free tier: 14,400 req/day, OpenAI-compatible endpoint). Gemini 2.5 Flash
 * is the FALLBACK pool (round-robin across 5 keys, ~7500 req/day total).
 * Gemini 2.5 Pro is unavailable on free tier (quota = 0).
 *
 * Despite the OPENAI_* env vars elsewhere in the codebase, no real OpenAI
 * key is in play here. ScoringService delegates all transport to this
 * router, which speaks OpenAI-shaped chat/completions to whichever provider
 * is currently primary.
 *
 * Provider selection chain on each call:
 *   1. Groq (Llama 3.3 70B) — single key, fast, generous free quota.
 *   2. Gemini Flash — round-robin across 5 keys, fallback on Groq 429.
 *   3. Throw — caller catches and returns null.
 *
 * Migration to a real OpenAI key (production launch):
 *   Update config('services.groq') or replace with an 'openai' upstream in
 *   the chain below. The chat-completions HTTP shape is identical, so only
 *   config + env changes are needed.
 */
class LLMRouter
{
    private const CACHE_KEY_RR = 'llm_rr_idx';

    /**
     * Send a chat-completions payload. Returns the decoded JSON response from
     * the first 2xx call across the provider chain.
     *
     * @param array $payload Chat-completions request body (without 'model' —
     *                       set by the router based on which tier is being
     *                       tried).
     * @return array Decoded JSON response body. Throws on total exhaustion.
     */
    public function chatCompletion(array $payload): array
    {
        // 1. Try Groq (Llama 3.3 70B) — single key, primary tier.
        $groqKey   = config('services.groq.api_key');
        $groqBase  = config('services.groq.base_url');
        $groqModel = config('services.groq.model');
        if ($groqKey) {
            $payload['model'] = $groqModel;
            $resp = $this->httpCall($groqBase, $groqKey, $payload);
            if ($this->isHardSuccess($resp)) {
                Log::info('LLM ok', ['provider' => 'groq', 'model' => $groqModel, 'status' => $resp['status']]);
                return $resp['body'] ?? [];
            }
            if ($this->isHardError($resp)) {
                throw new \RuntimeException(
                    'Groq call failed: status=' . $resp['status'] .
                    ' body=' . substr(json_encode($resp['body']), 0, 500)
                );
            }
            Log::warning('Groq 429/network — falling back to Gemini', [
                'status' => $resp['status'],
            ]);
        }

        // 2. Fall back to Gemini Flash pool, round-robin across all keys.
        $keys     = config('services.gemini.keys', []);
        $baseUrl  = config('services.gemini.base_url');
        $primary  = config('services.gemini.primary_model');
        $fallback = config('services.gemini.fallback_model');

        if (empty($keys)) {
            throw new \RuntimeException(
                'No Gemini keys configured for fallback. Set GEMINI_API_KEY_1..5 in .env.'
            );
        }

        $offset  = (int) Cache::increment(self::CACHE_KEY_RR);
        $rotated = $this->rotate($keys, $offset % count($keys));

        foreach ($rotated as $i => $key) {
            $payload['model'] = $primary;
            $resp = $this->httpCall($baseUrl, $key, $payload);
            if ($this->isHardSuccess($resp)) {
                Log::info('LLM fallback ok', ['provider' => 'gemini', 'model' => $primary, 'key_index' => $i, 'status' => $resp['status']]);
                return $resp['body'] ?? [];
            }
            if ($this->isHardError($resp)) {
                throw new \RuntimeException(
                    'Gemini call failed: status=' . $resp['status'] .
                    ' model=' . $primary .
                    ' body=' . substr(json_encode($resp['body']), 0, 500)
                );
            }
            Log::warning('Gemini 429/network', ['model' => $primary, 'key_index' => $i]);
        }

        // 3. If primary != fallback, retry with the fallback model.
        if ($fallback !== $primary) {
            Log::warning('All Gemini primary keys quota-exhausted; trying fallback model');
            foreach ($rotated as $i => $key) {
                $payload['model'] = $fallback;
                $resp = $this->httpCall($baseUrl, $key, $payload);
                if ($this->isHardSuccess($resp)) {
                    Log::info('LLM fallback-2 ok', ['provider' => 'gemini', 'model' => $fallback, 'key_index' => $i]);
                    return $resp['body'] ?? [];
                }
                if ($this->isHardError($resp)) {
                    throw new \RuntimeException(
                        'Gemini fallback failed: status=' . $resp['status'] .
                        ' body=' . substr(json_encode($resp['body']), 0, 500)
                    );
                }
            }
        }

        throw new \RuntimeException(
            'All providers exhausted (Groq + Gemini ' . count($keys) . ' keys). ' .
            'Daily quota resets at UTC midnight.'
        );
    }

    private function isHardSuccess(array $resp): bool
    {
        return $resp['status'] >= 200 && $resp['status'] < 300;
    }

    private function isHardError(array $resp): bool
    {
        // Anything non-2xx that's NOT a 429/timeout/network-glitch — these are
        // contract bugs (auth, model name, malformed body) and should throw,
        // not silently fall through.
        return $resp['status'] !== 0
            && $resp['status'] !== 429
            && ($resp['status'] < 200 || $resp['status'] >= 300);
    }

    /**
     * Rotate $arr so element at $offset becomes the new head.
     */
    private function rotate(array $arr, int $offset): array
    {
        if ($offset === 0) {
            return $arr;
        }
        return array_merge(array_slice($arr, $offset), array_slice($arr, 0, $offset));
    }

    /**
     * Fire a single chat-completions HTTP call. Never logs the API key — only
     * its anonymized index in caller context.
     *
     * Returns ['status' => int, 'body' => array|null]. status=0 indicates
     * a transport-layer exception (timeout, DNS, etc.) — treated as 429-like
     * for fallthrough purposes.
     */
    private function httpCall(string $baseUrl, string $key, array $payload): array
    {
        try {
            $resp = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ])
                ->post(rtrim($baseUrl, '/') . '/chat/completions', $payload);

            return [
                'status' => $resp->status(),
                'body'   => $resp->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('LLM transport error: ' . $e->getMessage());
            return ['status' => 0, 'body' => null];
        }
    }
}
