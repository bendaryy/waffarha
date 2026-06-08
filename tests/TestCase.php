<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Maat\Waffarha\WaffarhaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [WaffarhaServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('waffarha.base_url', 'https://maat.test/waffarha');
        $app['config']->set('waffarha.client_id', 'test-client-id');
        $app['config']->set('waffarha.client_secret', 'test-secret');
        $app['config']->set('waffarha.timeout', 5);
        $app['config']->set('waffarha.connect_timeout', 2);
        $app['config']->set('waffarha.retries', 0);
        $app['config']->set('cache.default', 'array');
    }
}
