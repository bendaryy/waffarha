<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\Booking;
use Maat\Waffarha\Data\BookingCollection;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * The `bookings` API: listing provider bookings, fetching a single booking, and
 * creating / updating / cancelling bookings on Maat units.
 */
final class Bookings extends Resource
{
    /**
     * List provider bookings.
     *
     * Observed filters: `status`, `check_in_from`, `check_in_to` (plus the usual
     * pagination params). Unknown params are passed through as-is.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): BookingCollection
    {
        return BookingCollection::fromArray(
            $this->transport->send('GET', 'bookings', query: $query)
        );
    }

    /**
     * Retrieve a single booking by its Maat UUID.
     *
     * @throws WaffarhaRequestException
     */
    public function get(string $uuid): Booking
    {
        return Booking::fromArray(
            $this->transport->send('GET', "bookings/{$uuid}")
        );
    }

    /**
     * Create a booking on a Maat unit. Identify the source with
     * `'provider' => 'waffarha'` (see docs/create-booking.md for the payload).
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws WaffarhaRequestException
     */
    public function create(array $payload): Booking
    {
        return Booking::fromArray(
            $this->transport->send('POST', 'bookings', $payload)
        );
    }

    /**
     * Update a booking (status, dates, guests, total, notes, guest details).
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws WaffarhaRequestException
     */
    public function update(string $uuid, array $payload): Booking
    {
        return Booking::fromArray(
            $this->transport->send('PUT', "bookings/{$uuid}", $payload)
        );
    }

    /**
     * Cancel a booking, optionally recording a reason.
     *
     * @throws WaffarhaRequestException
     */
    public function cancel(string $uuid, ?string $reason = null): Booking
    {
        $payload = $reason !== null ? ['reason' => $reason] : [];

        return Booking::fromArray(
            $this->transport->send('DELETE', "bookings/{$uuid}", $payload)
        );
    }
}
