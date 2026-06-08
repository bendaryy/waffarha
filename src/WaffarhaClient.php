<?php

declare(strict_types=1);

namespace Maat\Waffarha;

use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Http\Transport;
use Maat\Waffarha\Resources\Units;

/**
 * Entry point for the Waffarha API. Endpoints are grouped into resource classes
 * reachable via accessors (e.g. {@see WaffarhaClient::units()}); a raw
 * {@see WaffarhaClient::request()} escape hatch is also provided. All wire-level
 * concerns (auth, retries, logging, error translation) live in {@see Transport}.
 */
class WaffarhaClient
{
    private ?Units $units = null;

    public function __construct(
        private readonly Transport $transport,
    ) {}

    /**
     * The `units` API (list units, fetch a unit's details).
     */
    public function units(): Units
    {
        return $this->units ??= new Units($this->transport);
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
