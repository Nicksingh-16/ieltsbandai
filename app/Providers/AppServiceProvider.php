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
        }
    }
}
