<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Host-configured special minimum-stay window overlapping a calendar range.
 *
 * Returned inside {@see UnitCalendar::$minimumStayOverrides}. When a stay
 * overlaps one of these windows, the guest must book at least
 * `effective_minimum_nights` nights (or satisfy an orphan-gap relaxation).
 *
 * @phpstan-type MinimumStayOverridePayload array{start_date?: string|null, end_date?: string|null, minimum_nights?: int|string|null, base_minimum_stay?: int|string|null, effective_minimum_nights?: int|string|null}
 */
final readonly class MinimumStayOverride
{
    public function __construct(
        public ?string $startDate,
        public ?string $endDate,
        public ?int $minimumNights,
        public ?int $baseMinimumStay,
        public ?int $effectiveMinimumNights,
    ) {}

    /**
     * @param  MinimumStayOverridePayload  $data
     */
    public static function fromArray(array $data): self
    {
        $minimumNights = $data['minimum_nights'] ?? null;
        $baseMinimumStay = $data['base_minimum_stay'] ?? null;
        $effectiveMinimumNights = $data['effective_minimum_nights'] ?? null;

        return new self(
            startDate: isset($data['start_date']) && is_scalar($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) && is_scalar($data['end_date']) ? (string) $data['end_date'] : null,
            minimumNights: is_numeric($minimumNights) ? (int) $minimumNights : null,
            baseMinimumStay: is_numeric($baseMinimumStay) ? (int) $baseMinimumStay : null,
            effectiveMinimumNights: is_numeric($effectiveMinimumNights) ? (int) $effectiveMinimumNights : null,
        );
    }
}
