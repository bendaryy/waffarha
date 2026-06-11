<?php

declare(strict_types=1);

namespace Maat\Waffarha;

use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Http\Transport;
use Maat\Waffarha\Resources\Bookings;
use Maat\Waffarha\Resources\Units;

/**
 * Entry point for the Waffarha API. Endpoints are grouped into resource classes
 * reachable via accessors (e.g. {@see WaffarhaClient::units()},
 * {@see WaffarhaClient::bookings()}); a raw {@see WaffarhaClient::request()}
 * escape hatch is also provided. All wire-level concerns (auth, retries,
 * logging, error translation) live in {@see Transport}.
 */
class WaffarhaClient
{
    private ?Units $units = null;

    private ?Bookings $bookings = null;

    public function __construct(
        private readonly Transport $transport,
    ) {}

    /**
     * The `units` API: list/get units, plus the per-unit `calendar` and
     * `checkAvailability` helpers used before creating a booking.
     */
    public function units(): Units
    {
        return $this->units ??= new Units($this->transport);
    }

    /**
     * The `bookings` API: list, fetch, and create provider bookings.
     */
    public function bookings(): Bookings
    {
        return $this->bookings ??= new Bookings($this->transport);
    }

    /**
     * Send a raw HTTP request to any Waffarha endpoint and return the decoded
     * JSON body. Use this for endpoints not covered by a resource method.
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
    public function request(string $method, string $endpoint, array $data = [], array $query = []): array
    {
        return $this->transport->send($method, $endpoint, $data, $query);
    }
}
