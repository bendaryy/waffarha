<?php

namespace Maat\Waffarha;

use Illuminate\Support\ServiceProvider;

class WaffarhaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration with host configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/waffarha.php', 'waffarha'
        );

        // Bind WaffarhaClient into the service container as a singleton
        $this->app->singleton('waffarha', function ($app) {
            return new WaffarhaClient(
                config('waffarha.base_url'),
                config('waffarha.client_id'),
                config('waffarha.client_secret'),
                config('waffarha.timeout')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Allow publishing of configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/waffarha.php' => config_path('waffarha.php'),
            ], 'waffarha-config');
        }
    }
}
