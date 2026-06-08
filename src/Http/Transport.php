<?php

declare(strict_types=1);

namespace Maat\Waffarha\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maat\Waffarha\Auth\TokenManager;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Throwable;

/**
 * Low-level HTTP transport for the Waffarha API.
 *
 * Owns everything wire-related: attaching the bearer token, transient-failure
 * retries, the 401 refresh-and-replay loop, decoding, logging, and translating
 * failures into typed exceptions. {@see \Maat\Waffarha\WaffarhaClient} sits on
 * top of this and deals only with the typed public API + DTO mapping.
 */
class Transport
{
    private readonly string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly TokenManager $tokenManager,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
        private readonly int $retries = 2,
        private readonly int $retryDelayMs = 200,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Send a request to any Waffarha endpoint and return the decoded JSON body.
     *
     * Query parameters belong in $query (correctly URL-encoded for every verb);
     * $data is sent as a JSON body for write verbs and ignored for GET/HEAD.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     *
     * @throws WaffarhaRequestException
     */
    public function send(string $method, string $endpoint, array $data = [], array $query = []): array
    {
        $response = $this->dispatch($method, $endpoint, $data, $query);

        // A 401 usually means the cached token is stale/revoked. Refresh once
        // and replay the request before giving up.
        if ($response->status() === 401) {
            $this->tokenManager->refresh();
            $response = $this->dispatch($method, $endpoint, $data, $query);
        }

        if ($response->failed()) {
            $this->logFailure($method, $endpoint, $response);

            throw WaffarhaRequestException::fromStatus(
                strtoupper($method),
                $this->url($endpoint),
                $response->status(),
                $response->body(),
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        return $json;
    }

    /**
     * Perform a single HTTP attempt (no 401 handling), returning the raw
     * response. Connection failures are logged and rethrown as a typed
     * {@see WaffarhaRequestException}.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    private function dispatch(string $method, string $endpoint, array $data, array $query): Response
    {
        $method = strtoupper($method);
        $url = $this->url($endpoint);

        $request = Http::withToken($this->tokenManager->token())
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            // Retry transient connection failures only; status errors (incl. 401)
            // are handled explicitly above.
            ->retry($this->retries, $this->retryDelayMs, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException;
            }, throw: false);

        try {
            if ($method === 'GET' || $method === 'HEAD') {
                return $request->get($url, $query);
            }

            if ($query !== []) {
                $url .= '?'.http_build_query($query);
            }

            return $request->send($method, $url, ['json' => $data]);
        } catch (ConnectionException $e) {
            $this->logConnectionError($method, $url, $e);

            throw WaffarhaRequestException::connectionError($method, $url, $e);
        }
    }

    private function url(string $endpoint): string
    {
        return $this->baseUrl.'/'.ltrim($endpoint, '/');
    }

    private function logFailure(string $method, string $endpoint, Response $response): void
    {
        Log::error('Waffarha API request failed', [
            'method' => strtoupper($method),
            'url' => $this->url($endpoint),
            'status' => $response->status(),
            // Truncate to keep logs readable and avoid dumping large payloads.
            'body' => mb_substr($response->body(), 0, 1000),
        ]);
    }

    private function logConnectionError(string $method, string $url, ConnectionException $e): void
    {
        Log::error('Waffarha API connection error', [
            'method' => $method,
            'url' => $url,
            'message' => $e->getMessage(),
        ]);
    }
}
