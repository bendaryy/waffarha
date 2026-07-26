<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection of {@see AmenityGroup} rows from `GET /waffarha/amenities`.
 *
 * @implements IteratorAggregate<int, AmenityGroup>
 *
 * @phpstan-type ListPayload array{amenities?: list<array<string, mixed>>}
 */
final readonly class AmenityGroupCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<AmenityGroup>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['amenities']) && is_array($data['amenities'])
            ? $data['amenities']
            : [];

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = AmenityGroup::fromArray($row);
            }
        }

        return new self($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, AmenityGroup>
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
        return array_map(static fn (AmenityGroup $group): array => $group->toArray(), $this->items);
    }
}
