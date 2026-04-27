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
            $apiKey = (string) config('services.openai.api_key', '');
            if ($apiKey === '' || str_contains($apiKey, 'PUT_GEMINI') || str_starts_with($apiKey, 'PUT_')) {
                throw new \RuntimeException(
                    'LLM API key not configured. Refusing to start in production. ' .
                    'Set OPENAI_API_KEY (and ideally GEMINI_API_KEY_1..5) ' .
                    'via your production secrets store.'
                );
            }
        }
    }
}
