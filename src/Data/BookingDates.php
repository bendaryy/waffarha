<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Date summary returned inside the `booking_dates` block of a
 * `POST /waffarha/unit/{uuid}/check` response.
 *
 *  - `$checkIn` / `$checkOut` are the partner-supplied dates, echoed back
 *    in canonical `Y-m-d` form.
 *  - `$totalDays` is the number of nights between them (= count of rows in
 *    {@see AvailabilityCheck::$breakdown}).
 *  - `$normalDays` + `$weekendDays` always sum to `$totalDays` and mirror
 *    Maat's internal pricing pipeline (weekend = Thursday / Friday /
 *    Saturday on properties with a configured weekend percentage).
 *
 * @phpstan-type BookingDatesPayload array{check_in?: string|null, check_out?: string|null, total_days?: int|string|null, normal_days?: int|string|null, weekend_days?: int|string|null}
 */
final readonly class BookingDates
{
    public function __construct(
        public ?string $checkIn,
        public ?string $checkOut,
        public ?int $totalDays,
        public ?int $normalDays,
        public ?int $weekendDays,
    ) {}

    /**
     * @param  BookingDatesPayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            checkIn: isset($data['check_in']) && is_scalar($data['check_in']) ? (string) $data['check_in'] : null,
            checkOut: isset($data['check_out']) && is_scalar($data['check_out']) ? (string) $data['check_out'] : null,
            totalDays: self::nullableInt($data['total_days'] ?? null),
            normalDays: self::nullableInt($data['normal_days'] ?? null),
            weekendDays: self::nullableInt($data['weekend_days'] ?? null),
        );
    }

    /**
     * Build a `booking_dates` summary from the legacy top-level
     * `check_in` / `check_out` / `nights` keys so older Maat responses still
     * decode without crashing the SDK. `normalDays` / `weekendDays` are left
     * `null` because they're not derivable from the legacy payload.
     *
     * @param  array{check_in?: string|null, check_out?: string|null, nights?: int|string|null}  $legacy
     */
    public static function fromLegacyTopLevel(array $legacy): self
    {
        return self::fromArray([
            'check_in' => $legacy['check_in'] ?? null,
            'check_out' => $legacy['check_out'] ?? null,
            'total_days' => $legacy['nights'] ?? null,
        ]);
    }

    private static function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
