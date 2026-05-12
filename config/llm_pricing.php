<?php

/**
 * Per-million-token USD prices for the LLM providers we route through.
 * Used by LLMRouter to compute and log per-call cost into llm_call_logs.
 *
 * Keep this in sync with provider pricing pages. Last reviewed: May 2026.
 *   - OpenAI:  https://openai.com/api/pricing/
 *   - Groq:    https://groq.com/pricing/  (free tier — recorded as $0)
 *   - Gemini:  https://ai.google.dev/pricing  (free tier through OpenAI-compat
 *              endpoint — recorded as $0; bump when paid tier kicks in)
 */
return [

    // OpenAI — pay-per-token at the real OpenAI endpoint.
    'openai' => [
        'gpt-4o-mini'    => ['input' => 0.15, 'output' => 0.60],
        'gpt-4o'         => ['input' => 2.50, 'output' => 10.00],
        'gpt-4.1-mini'   => ['input' => 0.40, 'output' => 1.60],
        'gpt-4.1'        => ['input' => 2.00, 'output' => 8.00],
        '_default'       => ['input' => 0.15, 'output' => 0.60],
    ],

    // OpenRouter — passthrough; same prices as the underlying provider
    // (OpenRouter takes its margin from the credit purchase, not per-call).
    // Model IDs are namespaced: 'openai/gpt-4o-mini', 'anthropic/claude-haiku-4.5'.
    'openrouter' => [
        'openai/gpt-4o-mini'         => ['input' => 0.15, 'output' => 0.60],
        'openai/gpt-4o'              => ['input' => 2.50, 'output' => 10.00],
        'openai/gpt-4.1-mini'        => ['input' => 0.40, 'output' => 1.60],
        'openai/gpt-4.1'             => ['input' => 2.00, 'output' => 8.00],
        'anthropic/claude-haiku-4.5' => ['input' => 1.00, 'output' => 5.00],
        '_default'                   => ['input' => 0.15, 'output' => 0.60],
    ],

    // Groq — free tier (rate-limited 14.4k req/day). Cost = $0 until paid plan.
    'groq' => [
        '_default' => ['input' => 0.00, 'output' => 0.00],
    ],

    // Gemini — free tier via OpenAI-compat endpoint. Cost = $0.
    'gemini' => [
        '_default' => ['input' => 0.00, 'output' => 0.00],
    ],
];
