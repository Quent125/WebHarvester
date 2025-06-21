<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\UserRepository::class,
            \App\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\RegistrationTokenRepository::class,
            \App\Repositories\RegistrationTokenRepository::class
        );

        $this->app->bind(
            \App\Services\AuthService::class,
            \App\Services\AuthService::class
        );

        $this->app->bind(
            \App\Services\UserService::class,
            \App\Services\UserService::class
        );

        $this->app->bind(
            \App\Services\ScrawlService::class,
            \App\Services\ScrawlService::class
        );
    }
}
