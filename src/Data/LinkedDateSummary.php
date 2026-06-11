<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single host-defined linked-date rule surfaced inside a {@see UnitCalendar}.
 *
 * Linked dates are **minimum-stay rules**, not blocked dates: each day inside
 * a rule is individually `available`, but a booking that overlaps the range
 * has to satisfy the host's stay requirement (typically "book the whole
 * range") or `POST /waffarha/bookings` will reject it with a 409.
 *
 * Use `$id` to cross-reference {@see UnitCalendarDay::$linkedDateId} and use
 * `$message` as the user-facing explanation to surface in your UI when the
 * guest hovers an affected day.
 *
 * @phpstan-type LinkedDateSummaryPayload array{id?: int|string|null, name?: string|null, start_date?: string|null, end_date?: string|null, required_nights?: int|string|null, message?: string|null}
 */
final readonly class LinkedDateSummary
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this rule.
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $startDate,
        public ?string $endDate,
        public ?int $requiredNights,
        public ?string $message,
        public array $attributes,
    ) {}

    /**
     * @param  LinkedDateSummaryPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $id = $data['id'] ?? null;
        $requiredNights = $data['required_nights'] ?? null;

        return new self(
            id: is_numeric($id) ? (int) $id : null,
            name: isset($data['name']) && is_scalar($data['name']) ? (string) $data['name'] : null,
            startDate: isset($data['start_date']) && is_scalar($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) && is_scalar($data['end_date']) ? (string) $data['end_date'] : null,
            requiredNights: is_numeric($requiredNights) ? (int) $requiredNights : null,
            message: isset($data['message']) && is_scalar($data['message']) ? (string) $data['message'] : null,
            attributes: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
