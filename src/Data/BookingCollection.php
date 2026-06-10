<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A collection of {@see Booking} objects.
 *
 * Iterable and countable so callers can `foreach ($bookings as $booking)` and
 * `count($bookings)` directly:
 *
 *     $bookings = Waffarha::bookings()->list(['status' => 'Confirmed']);
 *     foreach ($bookings as $booking) {
 *         echo $booking->uuid;
 *     }
 *     $total = $bookings->meta?->total;
 *
 * The list envelope is confirmed against the live API: the response wraps the
 * rows under `bookings` and carries the same `pagination` block as the units
 * endpoint (verified via a live call — the account had zero bookings, so the
 * per-row {@see Booking} field mapping remains provisional). Rows are still
 * resolved defensively (`bookings` → `data` → bare list) to tolerate variants.
 *
 * @implements IteratorAggregate<int, Booking>
 *
 * @phpstan-type ListPayload array{bookings?: list<array<string, mixed>>, data?: list<array<string, mixed>>, pagination?: array<string, mixed>}
 */
final readonly class BookingCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<Booking>  $items
     */
    public function __construct(
        public array $items,
        public ?PaginationMeta $meta = null,
    ) {}

    /**
     * Build a collection from a decoded bookings response. The list rows are
     * looked up under `bookings`, then `data`, then a bare top-level list.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = match (true) {
            isset($data['bookings']) && is_array($data['bookings']) => $data['bookings'],
            isset($data['data']) && is_array($data['data']) => $data['data'],
            $data !== [] && array_is_list($data) => $data,
            default => [],
        };

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = Booking::fromArray($row);
            }
        }

        $meta = isset($data['pagination']) && is_array($data['pagination'])
            ? PaginationMeta::fromArray($data['pagination'])
            : null;

        return new self($items, $meta);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, Booking>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (Booking $booking): array => $booking->toArray(), $this->items);
    }
}
