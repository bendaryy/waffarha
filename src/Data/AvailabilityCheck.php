<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Result of a `POST /waffarha/unit/{uuid}/check` call.
 *
 * A successful (`available === true`) instance comes back from the SDK; an
 * unavailable date range surfaces as a {@see \Maat\Waffarha\Exceptions\WaffarhaRequestException}
 * (HTTP 409) so consumers can `try { } catch { }` the unhappy path without
 * branching on a status code. The exception's `body` carries the same
 * `reason` / `violated_blocks` keys documented in `docs/check-availability.md`.
 *
 * Iterable + countable over the per-night `$breakdown` so:
 *
 *     foreach ($check as $night) { echo "{$night->date}: {$night->price}\n"; }
 *     $nights = count($check);
 *
 * @implements IteratorAggregate<int, AvailabilityNight>
 *
 * `$subtotal` is the nightly sum (before the one-time cleaning fee).
 * `$cleaningFee` is the one-time, per-booking cleaning fee charged on top
 * (already converted to EGP). `$total` = `$subtotal + $cleaningFee` — this
 * is the headline number partners should display to the guest.
 *
 * @phpstan-type AvailabilityPayload array{available?: bool|int|string|null, property_uuid?: string|null, check_in?: string|null, check_out?: string|null, nights?: int|string|null, currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, total?: int|float|string|null, breakdown?: list<array<string, mixed>>}
 */
final readonly class AvailabilityCheck implements Countable, IteratorAggregate
{
    /**
     * @param  list<AvailabilityNight>  $breakdown
     */
    public function __construct(
        public bool $available,
        public ?string $propertyUuid,
        public ?string $checkIn,
        public ?string $checkOut,
        public ?int $nights,
        public ?string $currency,
        public ?float $subtotal,
        public ?float $cleaningFee,
        public ?float $total,
        public array $breakdown,
    ) {}

    /**
     * @param  AvailabilityPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['breakdown']) && is_array($data['breakdown']) ? $data['breakdown'] : [];

        $breakdown = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $breakdown[] = AvailabilityNight::fromArray($row);
            }
        }

        $subtotal = $data['subtotal'] ?? null;
        $cleaningFee = $data['cleaning_fee'] ?? null;
        $total = $data['total'] ?? null;
        $nights = $data['nights'] ?? null;

        $resolvedSubtotal = is_numeric($subtotal) ? (float) $subtotal : null;
        $resolvedCleaningFee = is_numeric($cleaningFee) ? (float) $cleaningFee : null;
        // Fall back to `subtotal + cleaning_fee` so older API responses (which
        // omit `total`) still surface a usable headline number for partners.
        $resolvedTotal = is_numeric($total)
            ? (float) $total
            : ($resolvedSubtotal !== null
                ? round($resolvedSubtotal + ($resolvedCleaningFee ?? 0.0), 2)
                : null);

        return new self(
            available: isset($data['available']) ? (bool) $data['available'] : true,
            propertyUuid: isset($data['property_uuid']) && is_scalar($data['property_uuid']) ? (string) $data['property_uuid'] : null,
            checkIn: isset($data['check_in']) && is_scalar($data['check_in']) ? (string) $data['check_in'] : null,
            checkOut: isset($data['check_out']) && is_scalar($data['check_out']) ? (string) $data['check_out'] : null,
            nights: is_numeric($nights) ? (int) $nights : null,
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            subtotal: $resolvedSubtotal,
            cleaningFee: $resolvedCleaningFee,
            total: $resolvedTotal,
            breakdown: $breakdown,
        );
    }

    public function count(): int
    {
        return count($this->breakdown);
    }

    /**
     * @return Traversable<int, AvailabilityNight>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->breakdown);
    }
}
