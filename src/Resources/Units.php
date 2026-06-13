<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\AvailabilityCheck;
use Maat\Waffarha\Data\UnitCalendar;
use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\UnitDetail;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * The `units` API: listing units, fetching a single unit's full details, and
 * the per-unit pricing/availability helpers (`calendar`, `checkAvailability`)
 * partners typically call between the unit-detail page and the booking
 * confirmation step.
 */
final class Units extends Resource
{
    /**
     * Fetch a paginated list of syndicated units.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): UnitCollection
    {
        return UnitCollection::fromArray(
            $this->transport->send('GET', 'units', query: $query)
        );
    }

    /**
     * Retrieve the full details of a single unit by UUID.
     *
     * Note the singular `unit` path — the list endpoint is `units`, but a single
     * unit is fetched from `unit/{uuid}`.
     *
     * @throws WaffarhaRequestException
     */
    public function get(string $uuid): UnitDetail
    {
        return UnitDetail::fromArray(
            $this->transport->send('GET', "unit/{$uuid}")
        );
    }

    /**
     * Per-day pricing + availability calendar for a unit.
     *
     * - **HTTP:** `GET unit/{uuid}/calendar`
     * - **Query:** `start_date`, `end_date` (`Y-m-d`). Both optional — Maat
     *   defaults to a 180-day window starting today and rejects any window
     *   larger than 180 days with HTTP 422.
     * - **Prices:** always returned in EGP and already include the same
     *   `SpecialRate` + `weekend percentage` adjustments the booking flow
     *   uses, so the displayed prices line up with what
     *   {@see Bookings::create()} will charge.
     *
     * See `docs/unit-calendar.md` for the full payload reference.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function calendar(string $uuid, array $query = []): UnitCalendar
    {
        return UnitCalendar::fromArray(
            $this->transport->send('GET', "unit/{$uuid}/calendar", query: $query)
        );
    }

    /**
     * Confirm that a specific date range is bookable on a unit and return the
     * per-night price breakdown the partner can show before the guest pays.
     *
     * - **HTTP:** `POST unit/{uuid}/check`
     * - **Body:** `check_in`, `check_out` (`Y-m-d`), optional `guests_count`.
     * - **Returns:** {@see AvailabilityCheck} for the happy path. An
     *   unavailable date range is surfaced as a {@see WaffarhaRequestException}
     *   with HTTP 409 — inspect `$exception->body['reason']` for one of
     *   `booking_overlap`, `blocked`, or `linked_date_violation`.
     *
     * A 200 response guarantees the same dates will pass the same checks when
     * you next call {@see Bookings::create()} (modulo a small race window
     * during which somebody else might book the same dates).
     *
     * See `docs/check-availability.md` for the full payload reference.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws WaffarhaRequestException
     */
    public function checkAvailability(string $uuid, array $payload): AvailabilityCheck
    {
        return AvailabilityCheck::fromArray(
            $this->transport->send('POST', "unit/{$uuid}/check", $payload)
        );
    }
}
