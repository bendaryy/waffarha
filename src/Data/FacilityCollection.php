<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection of {@see FacilityGroup} rows from `GET /waffarha/facilities`.
 *
 * @implements IteratorAggregate<int, FacilityGroup>
 *
 * @phpstan-type ListPayload array{facilities?: list<array<string, mixed>>}
 */
final readonly class FacilityCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<FacilityGroup>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['facilities']) && is_array($data['facilities'])
            ? $data['facilities']
            : [];

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = FacilityGroup::fromArray($row);
            }
        }

        return new self($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, FacilityGroup>
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
        return array_map(static fn (FacilityGroup $group): array => $group->toArray(), $this->items);
    }
}
