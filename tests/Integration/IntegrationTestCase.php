<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests\Integration;

use Maat\Waffarha\Tests\TestCase;

/**
 * Base class for tests that hit the REAL Maat API.
 *
 * These never run in CI (separate "Live" test suite) and self-skip unless live
 * credentials are provided via environment variables:
 *
 *   WAFFARHA_LIVE_BASE_URL     e.g. https://your-maat-host.example.com/waffarha
 *   WAFFARHA_LIVE_CLIENT_ID
 *   WAFFARHA_LIVE_CLIENT_SECRET
 *
 * For convenience a gitignored `tests/.env.live` file (KEY=VALUE per line) is
 * loaded automatically if present. See `tests/.env.live.example`.
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $config = self::liveCredentials();

        // Real endpoint + credentials. No Http::fake() is registered here.
        $app['config']->set('waffarha.base_url', $config['base_url'] ?? null);
        $app['config']->set('waffarha.client_id', $config['client_id'] ?? null);
        $app['config']->set('waffarha.client_secret', $config['client_secret'] ?? null);
        $app['config']->set('waffarha.timeout', 30);
        $app['config']->set('waffarha.connect_timeout', 10);
        $app['config']->set('waffarha.retries', 2);

        // Use an isolated array cache so live runs never read a stale token.
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void
    {
        if (self::liveCredentials() === null) {
            $this->markTestSkipped(
                'Live Maat credentials not set. Provide WAFFARHA_LIVE_BASE_URL, '.
                'WAFFARHA_LIVE_CLIENT_ID and WAFFARHA_LIVE_CLIENT_SECRET '.
                '(env vars or tests/.env.live) to run the live suite.'
            );
        }

        parent::setUp();
    }

    /**
     * @return array{base_url: string, client_id: string, client_secret: string}|null
     */
    protected static function liveCredentials(): ?array
    {
        self::loadEnvFile();

        $base = getenv('WAFFARHA_LIVE_BASE_URL') ?: null;
        $id = getenv('WAFFARHA_LIVE_CLIENT_ID') ?: null;
        $secret = getenv('WAFFARHA_LIVE_CLIENT_SECRET') ?: null;

        if ($base === null || $id === null || $secret === null) {
            return null;
        }

        return ['base_url' => $base, 'client_id' => $id, 'client_secret' => $secret];
    }

    /**
     * Load `tests/.env.live` (if present) into the environment without
     * overriding values already exported in the shell.
     */
    private static function loadEnvFile(): void
    {
        $file = __DIR__.'/../.env.live';

        if (! is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\"'");

            // Don't clobber values already provided by the shell environment.
            if ($key !== '' && getenv($key) === false) {
                putenv("{$key}={$value}");
            }
        }
    }
}
