<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
    \App\Repositories\TestRepositoryInterface::class,
    \App\Repositories\TestRepository::class
);

$this->app->bind(
    \App\Repositories\SpeakingRepository::class,
    \App\Repositories\SpeakingRepository::class
);

$this->app->bind(
    \App\Services\SpeakingTestService::class,
    \App\Services\SpeakingTestService::class
);


$this->app->bind(
    \App\Repositories\WritingRepository::class,
    \App\Repositories\WritingRepository::class
);

$this->app->bind(
    \App\Services\WritingTestService::class,
    \App\Services\WritingTestService::class
);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (\Illuminate\Support\Facades\App::environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');

            // Refuse to boot in production with placeholder LLM credentials.
            // Catches the case where a deploy reuses the .env.example values
            // or a missing prod secret leaves the placeholder string in place.
            //
            // Skip during console/CLI execution (artisan commands, queue
            // workers, package:discover during docker build) — Render only
            // injects env vars at HTTP-runtime, not during the build step, so
            // this guard would otherwise kill the deploy before keys are
            // available. The check still fires on every HTTP request, which is
            // the only path that actually needs LLM credentials.
            if ($this->app->runningInConsole()) {
                return;
            }

            $orKey      = (string) config('services.openrouter.api_key', '');
            $openaiKey  = (string) config('services.openai.api_key', '');
            $groqKey    = (string) config('services.groq.api_key', '');
            $geminiKeys = (array)  config('services.gemini.keys', []);

            $hasOpenRouter = str_starts_with($orKey, 'sk-or-');
            $hasOpenAI     = str_starts_with($openaiKey, 'sk-') && !str_starts_with($openaiKey, 'sk-or-');
            $hasGroq       = $groqKey !== '' && !str_starts_with($groqKey, 'PUT_');
            $hasGemini     = !empty(array_filter($geminiKeys, fn ($k) => $k !== '' && !str_starts_with((string) $k, 'PUT_')));

            if (!$hasOpenRouter && !$hasOpenAI && !$hasGroq && !$hasGemini) {
                throw new \RuntimeException(
                    'LLM API key not configured. Refusing to start in production. ' .
                    'Set OPENROUTER_API_KEY (sk-or-...) or OPENAI_API_KEY (sk-...) ' .
                    'or GROQ_API_KEY or GEMINI_API_KEY_1..5 via your production secrets store.'
                );
            }

            // Additional production guards. Each is an explicit assertion so
            // a misconfigured deploy fails loud at first HTTP hit rather than
            // silently misbehaving.
            if (config('app.debug') === true) {
                throw new \RuntimeException(
                    'APP_DEBUG must be false in production — leaks stack traces and config.'
                );
            }

            if (config('queue.default') === 'sync') {
                throw new \RuntimeException(
                    'QUEUE_CONNECTION=sync in production would make every mail / LLM job ' .
                    'block the request. Set QUEUE_CONNECTION=database (or redis) and run ' .
                    'a queue worker (php artisan queue:work).'
                );
            }

            $mailFrom = (string) config('mail.from.address', '');
            if (in_array(config('mail.default'), ['log', 'array'], true)
                || str_ends_with($mailFrom, '@resend.dev')) {
                throw new \RuntimeException(
                    'Mail not configured for production. MAIL_MAILER must be a real ' .
                    'transport (resend/smtp/ses) and MAIL_FROM_ADDRESS must use a ' .
                    'verified domain (not @resend.dev).'
                );
            }
        }
    }
}
