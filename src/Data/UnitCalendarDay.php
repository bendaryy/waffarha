<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single day in a {@see UnitCalendar}.
 *
 * `reason` is a hint that explains why the day's price or availability differs
 * from the unit's base price:
 *
 * - `"booked"`        — there's an existing non-cancelled booking that night.
 * - `"blocked"`       — the host has manually blocked the day.
 * - `"linked_date"`   — the day is inside an active minimum-stay rule; it's
 *                       still individually `available`, but bookable only as
 *                       part of a longer stay. Scan {@see UnitCalendar::$linkedDates}
 *                       to find the rule that covers this date.
 * - `"special_rate"`  — available; price reflects an active SpecialRate.
 * - `"weekend_rate"`  — available; price reflects the property's weekend %.
 * - `null`            — regular available day at base price.
 *
 * Three boolean flags drive partner calendar UIs (mirror of v1, but with
 * intent-friendly names):
 *
 *  - `$isBooked` — the night is occupied (booked or host-blocked). True
 *    for both reasons.
 *  - `$availableForCheckin` — a NEW guest can begin a stay on this day.
 *    Opposite of v1's `is_check_in`. False on booked nights, on days
 *    another booking is checking in, and on existing check-out days when
 *    the host has `same_day_booking = false`.
 *  - `$availableForCheckout` — a NEW guest can end a stay on this day.
 *    Opposite of v1's `is_check_out`.
 *
 * Monetary values are floats (rounded to 2 decimals server-side) for ease of
 * use; full precision is preserved in {@see UnitCalendarDay::$attributes}.
 *
 * @phpstan-type UnitCalendarDayPayload array{date?: string|null, price?: int|float|string|null, currency?: string|null, available?: bool|int|string|null, is_booked?: bool|int|string|null, available_for_checkin?: bool|int|string|null, available_for_checkout?: bool|int|string|null, is_weekend?: bool|int|string|null, reason?: string|null}
 */
final readonly class UnitCalendarDay
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this day.
     */
    public function __construct(
        public ?string $date,
        public ?float $price,
        public ?string $currency,
        public ?bool $available,
        public ?bool $isBooked,
        public ?bool $availableForCheckin,
        public ?bool $availableForCheckout,
        public ?bool $isWeekend,
        public ?string $reason,
        public array $attributes,
    ) {}

    /**
     * @param  UnitCalendarDayPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $price = $data['price'] ?? null;
        $available = $data['available'] ?? null;
        $isBooked = $data['is_booked'] ?? null;
        $availableForCheckin = $data['available_for_checkin'] ?? null;
        $availableForCheckout = $data['available_for_checkout'] ?? null;
        $isWeekend = $data['is_weekend'] ?? null;

        return new self(
            date: isset($data['date']) && is_scalar($data['date']) ? (string) $data['date'] : null,
            price: is_numeric($price) ? (float) $price : null,
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            available: $available !== null ? (bool) $available : null,
            isBooked: $isBooked !== null ? (bool) $isBooked : null,
            availableForCheckin: $availableForCheckin !== null ? (bool) $availableForCheckin : null,
            availableForCheckout: $availableForCheckout !== null ? (bool) $availableForCheckout : null,
            isWeekend: $isWeekend !== null ? (bool) $isWeekend : null,
            reason: isset($data['reason']) && is_scalar($data['reason']) ? (string) $data['reason'] : null,
            attributes: $data,
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
