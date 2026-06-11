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
 *                       part of a longer stay. Look up `$linkedDateId` in
 *                       {@see UnitCalendar::$linkedDates} for the full rule.
 * - `"special_rate"`  — available; price reflects an active SpecialRate.
 * - `"weekend_rate"`  — available; price reflects the property's weekend %.
 * - `null`            — regular available day at base price.
 *
 * Monetary values are floats (rounded to 2 decimals server-side) for ease of
 * use; full precision is preserved in {@see UnitCalendarDay::$attributes}.
 *
 * @phpstan-type UnitCalendarDayPayload array{date?: string|null, price?: int|float|string|null, currency?: string|null, available?: bool|int|string|null, is_weekend?: bool|int|string|null, linked_date_id?: int|string|null, reason?: string|null}
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
        public ?bool $isWeekend,
        public ?int $linkedDateId,
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
        $isWeekend = $data['is_weekend'] ?? null;
        $linkedDateId = $data['linked_date_id'] ?? null;

        return new self(
            date: isset($data['date']) && is_scalar($data['date']) ? (string) $data['date'] : null,
            price: is_numeric($price) ? (float) $price : null,
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            available: $available !== null ? (bool) $available : null,
            isWeekend: $isWeekend !== null ? (bool) $isWeekend : null,
            linkedDateId: is_numeric($linkedDateId) ? (int) $linkedDateId : null,
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
