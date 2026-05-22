<?php

namespace Maat\Waffarha;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaffarhaClient
{
    protected string $baseUrl;

    /**
     * WaffarhaClient constructor.
     */
    public function __construct(
        string $baseUrl,
        protected ?string $clientId = null,
        protected ?string $clientSecret = null,
        protected int $timeout = 30
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get default headers for all Waffarha API requests.
     *
     * @return array<string, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Send a raw HTTP request to Waffarha API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     *
     * @throws Exception
     */
    public function request(string $method, string $endpoint, array $data = []): ?array
    {
        $url = $this->baseUrl.'/'.ltrim($endpoint, '/');
        $headers = $this->getHeaders();

        try {
            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->send($method, $url, [
                    'json' => $data,
                ]);

            if ($response->failed()) {
                Log::error('Waffarha API request failed', [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception("Waffarha API call failed with status {$response->status()}: {$response->body()}");
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Waffarha API connection error', [
                'url' => $url,
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetch all syndicated units from Waffarha API.
     *
     * @return array<string, mixed>|null
     */
    public function getUnits(array $queryParameters = []): ?array
    {
        $endpoint = 'units';
        if (! empty($queryParameters)) {
            $endpoint .= '?'.http_build_query($queryParameters);
        }

        return $this->request('GET', $endpoint);
    }

    /**
     * Retrieve specific unit details from Waffarha API by UUID.
     */
    public function getUnit(string $uuid): ?array
    {
        return $this->request('GET', "units/{$uuid}");
    }
}
