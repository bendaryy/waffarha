<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A paginated collection of {@see Unit} objects.
 *
 * Iterable and countable so callers can `foreach ($units as $unit)` and
 * `count($units)` directly:
 *
 *     $units = Waffarha::units()->list(['page' => 1]);
 *     foreach ($units as $unit) {
 *         echo $unit->uuid;
 *     }
 *     $total = $units->meta?->total;
 *
 * @implements IteratorAggregate<int, Unit>
 *
 * @phpstan-type ListPayload array{units?: list<array<string, mixed>>, pagination?: array<string, mixed>}
 */
final readonly class UnitCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<Unit>  $items
     */
    public function __construct(
        public array $items,
        public ?PaginationMeta $meta = null,
    ) {}

    /**
     * Build a collection from a decoded units response of the form
     * `{"units": [...], "pagination": {...}}`.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['units']) && is_array($data['units']) ? $data['units'] : [];

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = Unit::fromArray($row);
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
     * @return Traversable<int, Unit>
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
        return array_map(static fn (Unit $unit): array => $unit->toArray(), $this->items);
    }
}
