<?php

declare(strict_types=1);

namespace Maat\Waffarha;

use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Http\Transport;
use Maat\Waffarha\Resources\Bookings;
use Maat\Waffarha\Resources\CityFolders;
use Maat\Waffarha\Resources\Payouts;
use Maat\Waffarha\Resources\Units;
use Maat\Waffarha\Resources\WhatsApp;

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

    private ?CityFolders $cityFolders = null;

    private ?Bookings $bookings = null;

    private ?Payouts $payouts = null;

    private ?WhatsApp $whatsapp = null;

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
     * City folders browse: list folders for a country, then list/filter units
     * inside a folder (Waffarha-exposed units only).
     */
    public function cityFolders(): CityFolders
    {
        return $this->cityFolders ??= new CityFolders($this->transport);
    }

    /**
     * The `bookings` API: list, fetch, create, and guest `bookDetails()`.
     */
    public function bookings(): Bookings
    {
        return $this->bookings ??= new Bookings($this->transport);
    }

    /**
     * The `payouts` API: per-booking settlement queue raised by Maat. Use
     * `list()`/`get()` to pick up pending transfers and `submitProof()` to
     * upload the bank-receipt file once the transfer has been issued.
     */
    public function payouts(): Payouts
    {
        return $this->payouts ??= new Payouts($this->transport);
    }

    /**
     * Maat support WhatsApp contact (`tbl_setting.app_phone_number`).
     */
    public function whatsapp(): WhatsApp
    {
        return $this->whatsapp ??= new WhatsApp($this->transport);
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
