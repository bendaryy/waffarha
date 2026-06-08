<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Illuminate\Support\Facades\Http;
use Maat\Waffarha\Auth\TokenManager;
use Maat\Waffarha\Exceptions\WaffarhaAuthenticationException;

class TokenManagerTest extends TestCase
{
    private function tokenUrl(): string
    {
        return 'maat.test/waffarha/oauth/token';
    }

    public function test_it_fetches_and_caches_an_access_token(): void
    {
        Http::fake([
            $this->tokenUrl() => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'access-1',
                'refresh_token' => 'refresh-1',
            ]),
        ]);

        $manager = $this->app->make(TokenManager::class);

        $this->assertSame('access-1', $manager->token());
        // Second call must come from cache, not a second HTTP round-trip.
        $this->assertSame('access-1', $manager->token());

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return $request['grant_type'] === 'client_credentials'
                && $request['client_id'] === 'test-client-id';
        });
    }

    public function test_refresh_uses_the_stored_refresh_token(): void
    {
        Http::fake([
            $this->tokenUrl() => Http::sequence()
                ->push(['expires_in' => 3600, 'access_token' => 'access-1', 'refresh_token' => 'refresh-1'])
                ->push(['expires_in' => 3600, 'access_token' => 'access-2', 'refresh_token' => 'refresh-2']),
        ]);

        $manager = $this->app->make(TokenManager::class);

        $this->assertSame('access-1', $manager->token());
        $this->assertSame('access-2', $manager->refresh());

        Http::assertSent(function ($request) {
            return $request['grant_type'] === 'refresh_token'
                && $request['refresh_token'] === 'refresh-1';
        });
    }

    public function test_refresh_falls_back_to_client_credentials_when_refresh_is_rejected(): void
    {
        Http::fake([
            $this->tokenUrl() => Http::sequence()
                ->push(['expires_in' => 3600, 'access_token' => 'access-1', 'refresh_token' => 'refresh-1'])
                ->push(['error' => 'invalid_grant'], 400)        // refresh attempt rejected
                ->push(['expires_in' => 3600, 'access_token' => 'access-3', 'refresh_token' => 'refresh-3']),
        ]);

        $manager = $this->app->make(TokenManager::class);

        $this->assertSame('access-1', $manager->token());
        $this->assertSame('access-3', $manager->refresh());
    }

    public function test_it_throws_when_the_token_endpoint_fails(): void
    {
        Http::fake([
            $this->tokenUrl() => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $manager = $this->app->make(TokenManager::class);

        $this->expectException(WaffarhaAuthenticationException::class);
        $manager->token();
    }

    public function test_it_throws_when_no_access_token_is_returned(): void
    {
        Http::fake([
            $this->tokenUrl() => Http::response(['expires_in' => 3600], 200),
        ]);

        $manager = $this->app->make(TokenManager::class);

        $this->expectException(WaffarhaAuthenticationException::class);
        $manager->token();
    }
}
