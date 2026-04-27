<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP wrapper for self-hosted LanguageTool (silviof/docker-languagetool).
 *
 * Default endpoint: http://localhost:8010/v2/check (Docker container).
 * Spin up with: docker compose up -d languagetool
 *
 * Graceful degradation: if the service is unreachable (Docker not running,
 * port not exposed, etc.) the client returns null counts so callers can
 * inject "n/a" into prompts instead of crashing the scoring pipeline.
 */
class LanguageToolClient
{
    /**
     * Run a text through LanguageTool's check endpoint.
     *
     * @param string $text   The essay/transcript to lint.
     * @param string $lang   Language code (default en-US for IELTS).
     * @return array{available: bool, grammar_errors: int|null, spelling_errors: int|null, total: int|null, raw: array|null}
     */
    public function check(string $text, string $lang = 'en-US'): array
    {
        $base = config('services.languagetool.base_url', 'http://localhost:8010');
        $url  = rtrim($base, '/') . '/v2/check';

        try {
            $resp = Http::asForm()
                ->timeout(30)
                ->post($url, [
                    'language' => $lang,
                    'text'     => $text,
                    // Skip suggestions to keep the response small — we only
                    // need counts, not corrections (the LLM still does that).
                    'enabledOnly' => 'false',
                ]);

            if (!$resp->successful()) {
                Log::warning('LanguageTool non-2xx response', [
                    'status' => $resp->status(),
                    'body' => substr($resp->body(), 0, 200),
                ]);
                return $this->unavailable();
            }

            $body = $resp->json();
            $matches = $body['matches'] ?? [];

            $grammar = 0;
            $spelling = 0;
            foreach ($matches as $m) {
                $catId = strtoupper($m['rule']['category']['id'] ?? '');
                if (str_contains($catId, 'TYPO') || str_contains($catId, 'SPELL')) {
                    $spelling++;
                } else {
                    $grammar++;
                }
            }

            return [
                'available' => true,
                'grammar_errors' => $grammar,
                'spelling_errors' => $spelling,
                'total' => $grammar + $spelling,
                'raw' => $matches,
            ];
        } catch (\Throwable $e) {
            // Connection refused / timeout / DNS: Docker probably not running.
            // Don't break scoring — let the caller note "n/a" in the prompt.
            Log::info('LanguageTool unavailable: ' . $e->getMessage());
            return $this->unavailable();
        }
    }

    private function unavailable(): array
    {
        return [
            'available' => false,
            'grammar_errors' => null,
            'spelling_errors' => null,
            'total' => null,
            'raw' => null,
        ];
    }
}
