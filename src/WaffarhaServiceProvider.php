<?php

declare(strict_types=1);

namespace Maat\Waffarha;

use Illuminate\Support\ServiceProvider;
use Maat\Waffarha\Auth\TokenManager;
use Maat\Waffarha\Exceptions\WaffarhaConfigurationException;
use Maat\Waffarha\Http\Transport;
use Maat\Waffarha\Resources\Units;

class WaffarhaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration with host configuration.
        $this->mergeConfigFrom(__DIR__.'/../config/waffarha.php', 'waffarha');

        $this->app->singleton(TokenManager::class, function ($app): TokenManager {
            return new TokenManager(
                baseUrl: $this->requiredConfig('base_url'),
                clientId: $this->requiredConfig('client_id'),
                clientSecret: $this->requiredConfig('client_secret'),
                cache: $app['cache']->store(config('waffarha.cache_store')),
                timeout: (int) config('waffarha.timeout', 30),
            );
        });

        $this->app->singleton(Transport::class, function ($app): Transport {
            return new Transport(
                baseUrl: $this->requiredConfig('base_url'),
                tokenManager: $app->make(TokenManager::class),
                timeout: (int) config('waffarha.timeout', 30),
                connectTimeout: (int) config('waffarha.connect_timeout', 10),
                retries: (int) config('waffarha.retries', 2),
            );
        });

        $this->app->singleton(WaffarhaClient::class, function ($app): WaffarhaClient {
            return new WaffarhaClient($app->make(Transport::class));
        });

        // Allow resources to be injected directly (e.g. app(Units::class)).
        $this->app->bind(Units::class, function ($app): Units {
            return new Units($app->make(Transport::class));
        });

        // Resolve the facade accessor ('waffarha') and typehinted dependency
        // injection (WaffarhaClient) to the same singleton instance.
        $this->app->alias(WaffarhaClient::class, 'waffarha');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/waffarha.php' => config_path('waffarha.php'),
            ], 'waffarha-config');
        }
    }

    /**
     * Read a required config value, failing fast with a clear message instead of
     * letting a null reach a non-nullable constructor (which would surface as an
     * opaque TypeError at resolution time).
     *
     * @throws WaffarhaConfigurationException
     */
    private function requiredConfig(string $name): string
    {
        $value = config("waffarha.{$name}");

        if (! is_string($value) || trim($value) === '') {
            throw WaffarhaConfigurationException::missing($name);
        }

        return $value;
    }
}
