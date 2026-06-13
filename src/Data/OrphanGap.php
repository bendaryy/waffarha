<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A short gap between existing bookings / host-blocked dates that's too
 * small to satisfy the property's base minimum stay but is still bookable
 * with a relaxed minimum (Maat does this automatically to avoid leaving
 * tiny holes in the calendar that would otherwise sit empty).
 *
 * Ported from v1's `tbl_book` calendar:
 *  - `$startDate` / `$endDate` — inclusive bookable range (Y-m-d).
 *  - `$gapNights` — number of nights inside the gap.
 *  - `$baseMinimumStay` — the property's normal minimum, surfaced so
 *    partners can show the relaxation as a hint to the guest
 *    ("normally requires 3 nights — bookable from 1 because the gap is
 *    too short").
 *  - `$dynamicMinimumNights` — the effective minimum Maat will accept
 *    when this exact range is booked (always `1` today; field is kept
 *    so future per-gap minimums don't break partners).
 */
final readonly class OrphanGap
{
    public function __construct(
        public ?string $startDate,
        public ?string $endDate,
        public ?int $gapNights,
        public ?int $baseMinimumStay,
        public ?int $dynamicMinimumNights,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $int = static fn (string $key): ?int => isset($data[$key]) && is_numeric($data[$key])
            ? (int) $data[$key]
            : null;

        return new self(
            startDate: $str('start_date'),
            endDate: $str('end_date'),
            gapNights: $int('gap_nights'),
            baseMinimumStay: $int('base_minimum_stay'),
            dynamicMinimumNights: $int('dynamic_minimum_nights'),
        );
    }
}
