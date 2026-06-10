<?php

declare(strict_types=1);

namespace Maat\Waffarha\Auth;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Maat\Waffarha\Data\TokenResponse;
use Maat\Waffarha\Exceptions\WaffarhaAuthenticationException;
use Maat\Waffarha\WaffarhaClient;

/**
 * Owns the OAuth token lifecycle for the Waffarha integration.
 *
 * Responsibilities:
 *  - obtain an access token via the `client_credentials` grant,
 *  - cache it (keyed per client id) until shortly before it expires,
 *  - transparently refresh it using the stored `refresh_token`, falling back to
 *    a fresh `client_credentials` grant when the refresh token is gone/rejected.
 *
 * The token endpoint is hit WITHOUT an Authorization header (it is the auth
 * bootstrap), so this class never depends on {@see WaffarhaClient}.
 */
class TokenManager
{
    /**
     * Seconds to subtract from a token's lifetime so we refresh slightly before
     * the real expiry and never present an already-expired token.
     */
    private const EXPIRY_SKEW_SECONDS = 60;

    /**
     * How long to retain a refresh token (README documents a ~1 month TTL).
     */
    private const REFRESH_TTL_SECONDS = 30 * 24 * 60 * 60;

    private readonly string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly CacheRepository $cache,
        private readonly int $timeout = 30,
        private readonly string $scope = '*',
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Return a valid access token, fetching a new one if none is cached.
     *
     * @throws WaffarhaAuthenticationException
     */
    public function token(): string
    {
        $cached = $this->cache->get($this->accessTokenKey());

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        return $this->store($this->requestClientCredentials())->accessToken;
    }

    /**
     * Force a new access token, preferring the stored refresh token and falling
     * back to a fresh `client_credentials` grant. Returns the new access token.
     *
     * @throws WaffarhaAuthenticationException
     */
    public function refresh(): string
    {
        $refreshToken = $this->cache->get($this->refreshTokenKey());

        if (is_string($refreshToken) && $refreshToken !== '') {
            try {
                return $this->store($this->requestRefresh($refreshToken))->accessToken;
            } catch (WaffarhaAuthenticationException) {
                // Refresh token expired or was rejected — fall through to a fresh grant.
            }
        }

        return $this->store($this->requestClientCredentials())->accessToken;
    }

    /**
     * Drop all cached tokens for this client (e.g. on teardown or rotation).
     */
    public function forget(): void
    {
        $this->cache->forget($this->accessTokenKey());
        $this->cache->forget($this->refreshTokenKey());
    }

    private function requestClientCredentials(): TokenResponse
    {
        return $this->requestToken([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
        ]);
    }

    private function requestRefresh(string $refreshToken): TokenResponse
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => $this->scope,
        ]);
    }

    /**
     * @param  array<string, string>  $payload
     *
     * @throws WaffarhaAuthenticationException
     */
    private function requestToken(array $payload): TokenResponse
    {
        $url = $this->baseUrl.'/oauth/token';

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout($this->timeout)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new WaffarhaAuthenticationException(
                'Could not reach the Waffarha token endpoint: '.$e->getMessage(),
                0,
                $e,
            );
        }

        if ($response->failed()) {
            // Never echo the response body here — it can contain token material.
            throw new WaffarhaAuthenticationException(
                "Waffarha token request failed with status {$response->status()}.",
                $response->status(),
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];
        $token = TokenResponse::fromArray($json);

        if ($token->accessToken === '') {
            throw new WaffarhaAuthenticationException(
                'Waffarha token endpoint returned a response without an access token.'
            );
        }

        return $token;
    }

    /**
     * Persist a freshly issued token pair and return it.
     */
    private function store(TokenResponse $token): TokenResponse
    {
        $ttl = max(1, $token->expiresIn - self::EXPIRY_SKEW_SECONDS);
        $this->cache->put($this->accessTokenKey(), $token->accessToken, $ttl);

        if ($token->refreshToken !== null && $token->refreshToken !== '') {
            $this->cache->put($this->refreshTokenKey(), $token->refreshToken, self::REFRESH_TTL_SECONDS);
        }

        return $token;
    }

    private function accessTokenKey(): string
    {
        return 'waffarha:access_token:'.$this->clientFingerprint();
    }

    private function refreshTokenKey(): string
    {
        return 'waffarha:refresh_token:'.$this->clientFingerprint();
    }

    /**
     * Stable, non-reversible cache discriminator so multiple client ids never
     * collide and the raw id never lands in a cache key.
     */
    private function clientFingerprint(): string
    {
        return substr(hash('sha256', $this->clientId), 0, 16);
    }
}
