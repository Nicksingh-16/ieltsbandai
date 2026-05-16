<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LLM Provider Note
 * -----------------
 * Provider chain (tried in order on each call):
 *   1. OpenRouter — paid passthrough (UPI-billed). Activates when
 *      OPENROUTER_API_KEY starts with 'sk-or-'. App-side daily/total USD
 *      caps (LLM_OPENROUTER_*_USD_CAP) skip this tier once exceeded so we
 *      can never overspend even if the dashboard cap fails.
 *   2. OpenAI (direct) — activates when OPENAI_API_KEY starts with 'sk-'
 *      AND is not an 'sk-or-' (those go to tier 1 above).
 *   3. Groq (Llama 3.3 70B Versatile) — single key, free tier.
 *   4. Gemini Flash — round-robin across up to 5 keys, free tier.
 *
 * All upstreams speak the OpenAI chat/completions shape, so the only
 * per-tier difference is base URL, auth header, and model name.
 */
class LLMRouter
{
    private const CACHE_KEY_RR = 'llm_rr_idx';

    /**
     * Per-call context for cost logging. Callers can set these before
     * chatCompletion() via withContext() so each row in llm_call_logs is
     * attributable to the user/test/feature that triggered the call.
     */
    private ?int $ctxUserId = null;

    private ?int $ctxTestId = null;

    private ?string $ctxPurpose = null;

    private ?string $ctxPromptVersion = null;

    private ?string $ctxModelTier = null;

    /**
     * Last successful provider/model that returned a 2xx for this router
     * instance. Cleared at the start of each chatCompletion() call.
     * Callers can read it to record which model produced the response
     * (e.g. for analytics or persisted audit trails).
     */
    private ?string $lastProvider = null;

    private ?string $lastModel = null;

    public function withContext(?int $userId = null, ?int $testId = null, ?string $purpose = null, ?string $promptVersion = null): self
    {
        $this->ctxUserId = $userId;
        $this->ctxTestId = $testId;
        $this->ctxPurpose = $purpose;
        // Default to the active ScoringService prompt version so callers that
        // don't pass one still produce diff-able log rows.
        $this->ctxPromptVersion = $promptVersion ?? ScoringService::PROMPT_VERSION;

        // Resolve the user's model tier for premium routing (Pro Plus → gpt-4o).
        // Cached on users.model_tier so this is one fast lookup per scoring
        // call, no DB JOIN against subscriptions.
        $this->ctxModelTier = null;
        if ($userId) {
            try {
                $this->ctxModelTier = \App\Models\User::whereKey($userId)->value('model_tier') ?: 'standard';
            } catch (\Throwable $e) {
                $this->ctxModelTier = 'standard';
            }
        }

        return $this;
    }

    /**
     * Resolve the OpenRouter model name based on the active user's tier.
     * Premium subscribers get the full gpt-4o (better Band 7+ accuracy, ~17x
     * cost). Everyone else gets the default model from config.
     */
    private function resolveOpenRouterModel(string $defaultModel): string
    {
        if ($this->ctxModelTier === 'premium') {
            return (string) config('services.openrouter.premium_model', 'openai/gpt-4o');
        }

        return $defaultModel;
    }

    /**
     * Send a chat-completions payload. Returns the decoded JSON response from
     * the first 2xx call across the provider chain.
     *
     * @param  array  $payload  Chat-completions request body (without 'model' —
     *                          set by the router based on which tier is being
     *                          tried).
     * @return array Decoded JSON response body. Throws on total exhaustion.
     */
    /**
     * Returns the provider name (openrouter|openai|groq|gemini) of the last
     * successful LLM call on this router instance. Null before the first call
     * or if every tier failed. Read by callers (e.g. ScoringService) to
     * stamp the result with which model actually answered.
     */
    public function lastProvider(): ?string
    {
        return $this->lastProvider;
    }

    public function lastModel(): ?string
    {
        return $this->lastModel;
    }

    public function chatCompletion(array $payload): array
    {
        // Reset per-call state so callers see only this call's provider/model.
        $this->lastProvider = null;
        $this->lastModel = null;

        // 1. Try OpenRouter — paid passthrough (UPI-billed). Gate on the
        // 'sk-or-' prefix so a misconfigured OPENAI_API_KEY can't route
        // here. Pre-flight budget check skips this tier (falling through
        // to free Groq/Gemini) once the configured daily or total USD cap
        // is reached.
        $orKey = (string) config('services.openrouter.api_key', '');
        $orBase = (string) config('services.openrouter.base_url', '');
        $orModel = (string) config('services.openrouter.model', '');
        if (str_starts_with($orKey, 'sk-or-') && $orBase && $orModel) {
            if ($this->openRouterBudgetExceeded()) {
                Log::warning('OpenRouter budget cap reached — skipping to Groq/Gemini');
            } else {
                // Premium subscribers route to gpt-4o; everyone else uses the
                // configured default (gpt-4o-mini).
                $orModel = $this->resolveOpenRouterModel($orModel);
                $payload['model'] = $orModel;
                $resp = $this->httpCall($orBase, $orKey, $payload, 'openrouter', $orModel);
                if ($this->isHardSuccess($resp)) {
                    Log::info('LLM ok', ['provider' => 'openrouter', 'model' => $orModel, 'status' => $resp['status']]);
                    $this->lastProvider = 'openrouter';
                    $this->lastModel = $orModel;

                    return $resp['body'] ?? [];
                }
                if ($this->isHardError($resp)) {
                    throw new \RuntimeException(
                        'OpenRouter call failed: status='.$resp['status'].
                        ' body='.substr(json_encode($resp['body']), 0, 500)
                    );
                }
                Log::warning('OpenRouter 429/network — falling back', ['status' => $resp['status']]);
            }
        }

        // 2. Try OpenAI (direct) — only if a genuine OpenAI key is set.
        // 'sk-or-' OpenRouter keys are excluded (handled in tier 1 above).
        // Gemini keys ('AIza...') and placeholders ('PUT_...') skip this
        // tier so dev/free-tier setups keep using Groq + Gemini.
        $openaiKey = (string) config('services.openai.api_key', '');
        $openaiBase = (string) config('services.openai.base_url', '');
        $openaiModel = (string) config('services.openai.model', '');
        if (str_starts_with($openaiKey, 'sk-') && ! str_starts_with($openaiKey, 'sk-or-') && $openaiBase && $openaiModel) {
            $payload['model'] = $openaiModel;
            $resp = $this->httpCall($openaiBase, $openaiKey, $payload, 'openai', $openaiModel);
            if ($this->isHardSuccess($resp)) {
                Log::info('LLM ok', ['provider' => 'openai', 'model' => $openaiModel, 'status' => $resp['status']]);
                $this->lastProvider = 'openai';
                $this->lastModel = $openaiModel;

                return $resp['body'] ?? [];
            }
            if ($this->isHardError($resp)) {
                throw new \RuntimeException(
                    'OpenAI call failed: status='.$resp['status'].
                    ' body='.substr(json_encode($resp['body']), 0, 500)
                );
            }
            Log::warning('OpenAI 429/network — falling back to Groq', [
                'status' => $resp['status'],
            ]);
        }

        // 2. Try Groq (Llama 3.3 70B) — single key, secondary tier.
        $groqKey = config('services.groq.api_key');
        $groqBase = config('services.groq.base_url');
        $groqModel = config('services.groq.model');
        if ($groqKey) {
            $payload['model'] = $groqModel;
            $resp = $this->httpCall($groqBase, $groqKey, $payload, 'groq', $groqModel);
            if ($this->isHardSuccess($resp)) {
                Log::info('LLM ok', ['provider' => 'groq', 'model' => $groqModel, 'status' => $resp['status']]);
                $this->lastProvider = 'groq';
                $this->lastModel = $groqModel;

                return $resp['body'] ?? [];
            }
            if ($this->isHardError($resp)) {
                throw new \RuntimeException(
                    'Groq call failed: status='.$resp['status'].
                    ' body='.substr(json_encode($resp['body']), 0, 500)
                );
            }
            Log::warning('Groq 429/network — falling back to Gemini', [
                'status' => $resp['status'],
            ]);
        }

        // 2. Fall back to Gemini Flash pool, round-robin across all keys.
        $keys = config('services.gemini.keys', []);
        $baseUrl = config('services.gemini.base_url');
        $primary = config('services.gemini.primary_model');
        $fallback = config('services.gemini.fallback_model');

        if (empty($keys)) {
            throw new \RuntimeException(
                'No Gemini keys configured for fallback. Set GEMINI_API_KEY_1..5 in .env.'
            );
        }

        $offset = (int) Cache::increment(self::CACHE_KEY_RR);
        $rotated = $this->rotate($keys, $offset % count($keys));

        foreach ($rotated as $i => $key) {
            $payload['model'] = $primary;
            $resp = $this->httpCall($baseUrl, $key, $payload, 'gemini', $primary);
            if ($this->isHardSuccess($resp)) {
                Log::info('LLM fallback ok', ['provider' => 'gemini', 'model' => $primary, 'key_index' => $i, 'status' => $resp['status']]);
                $this->lastProvider = 'gemini';
                $this->lastModel = $primary;

                return $resp['body'] ?? [];
            }
            if ($this->isHardError($resp)) {
                throw new \RuntimeException(
                    'Gemini call failed: status='.$resp['status'].
                    ' model='.$primary.
                    ' body='.substr(json_encode($resp['body']), 0, 500)
                );
            }
            Log::warning('Gemini 429/network', ['model' => $primary, 'key_index' => $i]);
        }

        // 3. If primary != fallback, retry with the fallback model.
        if ($fallback !== $primary) {
            Log::warning('All Gemini primary keys quota-exhausted; trying fallback model');
            foreach ($rotated as $i => $key) {
                $payload['model'] = $fallback;
                $resp = $this->httpCall($baseUrl, $key, $payload, 'gemini', $fallback);
                if ($this->isHardSuccess($resp)) {
                    Log::info('LLM fallback-2 ok', ['provider' => 'gemini', 'model' => $fallback, 'key_index' => $i]);
                    $this->lastProvider = 'gemini';
                    $this->lastModel = $fallback;

                    return $resp['body'] ?? [];
                }
                if ($this->isHardError($resp)) {
                    throw new \RuntimeException(
                        'Gemini fallback failed: status='.$resp['status'].
                        ' body='.substr(json_encode($resp['body']), 0, 500)
                    );
                }
            }
        }

        throw new \RuntimeException(
            'All providers exhausted (Groq + Gemini '.count($keys).' keys). '.
            'Daily quota resets at UTC midnight.'
        );
    }

    /**
     * App-side OpenRouter spend guard. Sums cost_usd from llm_call_logs for
     * provider='openrouter' (today + all-time) and returns true if either the
     * daily or total cap is exceeded. Caps live in config/services.php and
     * default to null (disabled). Cached for 60s so we don't query on every
     * single LLM call. Fails open (returns false) on any error so a logging
     * outage can't take down scoring.
     */
    private function openRouterBudgetExceeded(): bool
    {
        $dailyCap = config('services.openrouter.daily_usd_cap');
        $totalCap = config('services.openrouter.total_usd_cap');

        if ($dailyCap === null && $totalCap === null) {
            return false;
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('llm_call_logs')) {
            return false;
        }

        try {
            [$dailySpend, $totalSpend] = Cache::remember(
                'or_budget_spend',
                60,
                function () {
                    $q = \App\Models\LlmCallLog::where('provider', 'openrouter')->where('ok', true);

                    return [
                        (float) (clone $q)->whereDate('created_at', now()->toDateString())->sum('cost_usd'),
                        (float) $q->sum('cost_usd'),
                    ];
                }
            );

            if ($dailyCap !== null && $dailySpend >= (float) $dailyCap) {
                Log::warning('OpenRouter daily cap hit', ['spent' => $dailySpend, 'cap' => $dailyCap]);

                return true;
            }
            if ($totalCap !== null && $totalSpend >= (float) $totalCap) {
                Log::warning('OpenRouter total cap hit', ['spent' => $totalSpend, 'cap' => $totalCap]);

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::warning('OpenRouter budget check failed: '.$e->getMessage());

            return false;
        }
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
     * Per-provider timeout in seconds. R2 (circuit breaker): OpenRouter
     * gpt-4o-mini p95 is <15s; allowing 120s lets a stuck call block
     * writing evaluation for two minutes and creates a "still evaluating..."
     * stall the user perceives as a hang. Tighter timeouts here force a
     * faster fall-through to Groq/Gemini.
     *
     * Defaults are also tier-aware: Gemini Flash is faster on the wire so
     * it gets a shorter budget; Groq is reliable but the model is heavier.
     */
    private const TIMEOUTS = [
        'openrouter' => 25,
        'openai' => 25,
        'groq' => 30,
        'gemini' => 30,
    ];

    /**
     * Status codes that warrant a single in-tier retry before falling
     * through to the next provider. 5xx upstream errors are usually
     * transient (rate-limit-adjacent, model overloaded); a transport
     * failure (status=0) is even more likely transient. We retry ONCE
     * to absorb the blip without doubling the latency budget.
     */
    private function shouldRetryWithinTier(int $status): bool
    {
        return $status === 0 || ($status >= 500 && $status < 600);
    }

    /**
     * Fire a single chat-completions HTTP call. Never logs the API key — only
     * its anonymized index in caller context.
     *
     * Returns ['status' => int, 'body' => array|null]. status=0 indicates
     * a transport-layer exception (timeout, DNS, etc.) — treated as 429-like
     * for fallthrough purposes.
     *
     * R2: now retries once on 5xx / transport failures within the same tier
     * before returning. A second 5xx falls through naturally.
     */
    private function httpCall(string $baseUrl, string $key, array $payload, string $provider = '?', string $model = '?'): array
    {
        $timeout = self::TIMEOUTS[$provider] ?? 30;

        $attempt = $this->doHttpCall($baseUrl, $key, $payload, $provider, $model, $timeout);

        if ($this->shouldRetryWithinTier($attempt['status'])) {
            Log::warning('LLM tier retry', [
                'provider' => $provider,
                'model' => $model,
                'first_status' => $attempt['status'],
            ]);
            $retry = $this->doHttpCall($baseUrl, $key, $payload, $provider, $model, $timeout);
            // Only adopt the retry if it's a hard success OR a different
            // non-transient status — otherwise keep the first attempt for
            // the caller's fall-through decision (avoids a 5xx→transport
            // flip that confuses isHardError).
            if ($retry['status'] >= 200 && $retry['status'] < 300) {
                return $retry;
            }
            if ($retry['status'] !== 0 && ! ($retry['status'] >= 500 && $retry['status'] < 600)) {
                return $retry;
            }
        }

        return $attempt;
    }

    /**
     * Single underlying HTTP attempt. Pulled out of httpCall() so the
     * retry path can reuse it without recursing.
     */
    private function doHttpCall(string $baseUrl, string $key, array $payload, string $provider, string $model, int $timeoutSec): array
    {
        $start = microtime(true);
        $status = 0;
        $body = null;
        try {
            $resp = Http::timeout($timeoutSec)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$key,
                    'Content-Type' => 'application/json',
                ])
                ->post(rtrim($baseUrl, '/').'/chat/completions', $payload);

            $status = $resp->status();
            $body = $resp->json();

            return ['status' => $status, 'body' => $body];
        } catch (\Throwable $e) {
            Log::error('LLM transport error: '.$e->getMessage(), [
                'provider' => $provider,
                'model' => $model,
                'timeout_s' => $timeoutSec,
            ]);

            return ['status' => 0, 'body' => null];
        } finally {
            // Best-effort cost logging — never let it break the request flow.
            try {
                $this->logCall($provider, $model, $body, $status, (int) round((microtime(true) - $start) * 1000));
            } catch (\Throwable $logErr) {
                Log::warning('LLM call logging failed: '.$logErr->getMessage());
            }
        }
    }

    /**
     * Persist one row to llm_call_logs with token counts + computed USD cost.
     * Pulls usage from the standard OpenAI-compat `usage` object (Groq +
     * Gemini both populate it via their compat endpoints).
     */
    private function logCall(string $provider, string $model, ?array $body, int $status, int $latencyMs): void
    {
        // Skip if logging table doesn't exist yet (e.g. before migration ran).
        if (! \Illuminate\Support\Facades\Schema::hasTable('llm_call_logs')) {
            return;
        }

        $usage = $body['usage'] ?? [];
        $in = (int) ($usage['prompt_tokens'] ?? 0);
        $out = (int) ($usage['completion_tokens'] ?? 0);

        $pricing = config("llm_pricing.{$provider}.{$model}")
                 ?? config("llm_pricing.{$provider}._default")
                 ?? ['input' => 0.0, 'output' => 0.0];
        $costUsd = ($in / 1_000_000) * $pricing['input'] + ($out / 1_000_000) * $pricing['output'];
        $costUsd = round($costUsd, 6);

        \App\Models\LlmCallLog::create([
            'user_id' => $this->ctxUserId,
            'test_id' => $this->ctxTestId,
            'provider' => $provider,
            'model' => substr($model, 0, 64),
            'purpose' => $this->ctxPurpose,
            'prompt_version' => $this->ctxPromptVersion,
            'input_tokens' => $in,
            'output_tokens' => $out,
            'cost_usd' => $costUsd,
            'http_status' => $status,
            'latency_ms' => $latencyMs,
            'ok' => $status >= 200 && $status < 300,
            'created_at' => now(),
        ]);
    }
}
