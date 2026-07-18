<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Per-day price + availability calendar for a single Maat unit.
 *
 * Iterable + countable so callers can write:
 *
 *     $calendar = Waffarha::units()->calendar($uuid, ['start_date' => '2026-08-01']);
 *     foreach ($calendar as $day) {
 *         echo "{$day->date}: {$day->price} {$day->currency}\n";
 *     }
 *     $total = count($calendar);            // number of days
 *     $first = $calendar->days[0] ?? null;  // direct list access
 *
 * The list endpoint always returns prices in **EGP** (Maat converts from the
 * property's base currency server-side), and applies the same
 * `base price → SpecialRate → weekend percentage` pipeline as the real booking
 * flow so the displayed prices line up with what `bookings()->create()` will
 * actually charge.
 *
 * Active host-defined minimum-stay rules that overlap the window are exposed
 * as a flat {@see UnitCalendar::$linkedDates} list. Affected days carry
 * `reason: "linked_date"` on {@see UnitCalendarDay::$reason} so the UI can
 * highlight them; scan the top-level list to find the rule that covers a
 * given date.
 *
 * @implements IteratorAggregate<int, UnitCalendarDay>
 *
 * Top-level fields:
 *  - {@see UnitCalendar::$blocklist} — sorted unique list of host-blocked
 *    dates (Y-m-d). Already mirrored per-day on `UnitCalendarDay::$isBooked`,
 *    but exposed here too so partners can render block bars without
 *    iterating the whole window.
 *  - {@see UnitCalendar::$orphanGaps} — short gaps between bookings /
 *    blocked dates Maat will accept with a relaxed minimum stay so the
 *    calendar doesn't carry tiny unfillable holes.
 *  - {@see UnitCalendar::$sameDayBooking} — whether the host allows a new
 *    check-in on the same day someone else is checking out. When `false`,
 *    `UnitCalendarDay::$availableForCheckin` is forced to `false` on
 *    existing check-out days.
 *
 * @phpstan-type CalendarPayload array{property_uuid?: string|null, currency?: string|null, base_price?: int|float|string|null, window?: array{start_date?: string|null, end_date?: string|null, days?: int|string|null}|null, linked_dates?: list<array<string, mixed>>, calendar?: list<array<string, mixed>>, blocklist?: list<string>, orphan_gaps?: list<array<string, mixed>>, same_day_booking?: bool|int|string|null}
 */
final readonly class UnitCalendar implements Countable, IteratorAggregate
{
    /**
     * @param  list<UnitCalendarDay>  $days
     * @param  list<LinkedDateSummary>  $linkedDates
     * @param  list<string>  $blocklist
     * @param  list<OrphanGap>  $orphanGaps
     */
    public function __construct(
        public ?string $propertyUuid,
        public ?string $currency,
        public ?float $basePrice,
        public ?string $startDate,
        public ?string $endDate,
        public ?int $totalDays,
        public array $days,
        public array $linkedDates = [],
        public array $blocklist = [],
        public array $orphanGaps = [],
        public ?bool $sameDayBooking = null,
    ) {}

    /**
     * @param  CalendarPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['calendar']) && is_array($data['calendar']) ? $data['calendar'] : [];

        $days = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $days[] = UnitCalendarDay::fromArray($row);
            }
        }

        $linkedDateRows = isset($data['linked_dates']) && is_array($data['linked_dates'])
            ? $data['linked_dates']
            : [];

        $linkedDates = [];
        foreach (array_values($linkedDateRows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $linkedDates[] = LinkedDateSummary::fromArray($row);
            }
        }

        $blocklistRows = isset($data['blocklist']) && is_array($data['blocklist'])
            ? $data['blocklist']
            : [];

        $blocklist = [];
        foreach (array_values($blocklistRows) as $row) {
            if (is_scalar($row)) {
                $blocklist[] = (string) $row;
            }
        }

        $orphanGapRows = isset($data['orphan_gaps']) && is_array($data['orphan_gaps'])
            ? $data['orphan_gaps']
            : [];

        $orphanGaps = [];
        foreach (array_values($orphanGapRows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $orphanGaps[] = OrphanGap::fromArray($row);
            }
        }

        $window = isset($data['window']) && is_array($data['window']) ? $data['window'] : [];
        $basePrice = $data['base_price'] ?? null;
        $totalDays = $window['days'] ?? null;
        $sameDayBooking = $data['same_day_booking'] ?? null;

        return new self(
            propertyUuid: isset($data['property_uuid']) && is_scalar($data['property_uuid']) ? (string) $data['property_uuid'] : null,
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            basePrice: is_numeric($basePrice) ? (float) $basePrice : null,
            startDate: isset($window['start_date']) && is_scalar($window['start_date']) ? (string) $window['start_date'] : null,
            endDate: isset($window['end_date']) && is_scalar($window['end_date']) ? (string) $window['end_date'] : null,
            totalDays: is_numeric($totalDays) ? (int) $totalDays : null,
            days: $days,
            linkedDates: $linkedDates,
            blocklist: $blocklist,
            orphanGaps: $orphanGaps,
            sameDayBooking: $sameDayBooking !== null ? (bool) $sameDayBooking : null,
        );
    }

    public function count(): int
    {
        return count($this->days);
    }

    /**
     * @return Traversable<int, UnitCalendarDay>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->days);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (UnitCalendarDay $day): array => $day->toArray(), $this->days);
    }
}
