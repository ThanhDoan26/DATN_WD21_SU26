<?php

namespace App\Providers;

use App\Hashing\LegacyBcryptHasher;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('hash', function ($app) {
            return new HashManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['hash']->extend('bcrypt', function () {
            return new LegacyBcryptHasher();
        });
    }
}
