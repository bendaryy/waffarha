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

    /**
     * List provider bookings.
     *
     * @param  array<string, mixed>  $queryParameters
     * @return array<string, mixed>|null
     */
    public function listBookings(array $queryParameters = []): ?array
    {
        $endpoint = 'bookings';
        if (! empty($queryParameters)) {
            $endpoint .= '?'.http_build_query($queryParameters);
        }

        return $this->request('GET', $endpoint);
    }

    /**
     * Create a booking on Maat for an external provider (e.g. Airbnb, Booking.com).
     *
     * Expected payload shape:
     *  - provider                (string, required) provider slug, e.g. "airbnb"
     *  - provider_booking_id     (string, required) external reservation reference
     *  - property_uuid           (string, required) Maat property UUID
     *  - check_in / check_out    (string, required) Y-m-d
     *  - guests_count            (int,    required)
     *  - total_amount            (number, required)
     *  - currency                (string, optional, 3-letter ISO)
     *  - notes                   (string, optional)
     *  - guest.name              (string, required)
     *  - guest.email|phone|nationality|passport_number|date_of_birth (optional)
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function createBooking(array $payload): ?array
    {
        return $this->request('POST', 'bookings', $payload);
    }

    /**
     * Retrieve a previously created provider booking by its Maat UUID.
     *
     * @return array<string, mixed>|null
     */
    public function getBooking(string $uuid): ?array
    {
        return $this->request('GET', "bookings/{$uuid}");
    }

    /**
     * Update a provider booking (status, dates, guests count, total, notes, guest details).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function updateBooking(string $uuid, array $payload): ?array
    {
        return $this->request('PUT', "bookings/{$uuid}", $payload);
    }

    /**
     * Cancel a provider booking.
     *
     * @return array<string, mixed>|null
     */
    public function cancelBooking(string $uuid, ?string $reason = null): ?array
    {
        $payload = $reason !== null ? ['reason' => $reason] : [];

        return $this->request('DELETE', "bookings/{$uuid}", $payload);
    }
}
