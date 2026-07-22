<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Paginated units inside one city folder
 * (`GET /waffarha/city-folders/{id}/units`).
 *
 * Units use the same {@see Unit} shape as `units()->list()`. Only properties
 * present in `waffarha_units` (and currently available for display) are
 * included.
 *
 * @implements IteratorAggregate<int, Unit>
 *
 * @phpstan-type UnitsPayload array{city_folder?: array<string, mixed>, units?: list<array<string, mixed>>, pagination?: array<string, mixed>}
 */
final readonly class CityFolderUnits implements Countable, IteratorAggregate
{
    /**
     * @param  list<Unit>  $items
     */
    public function __construct(
        public ?CityFolder $cityFolder,
        public array $items,
        public ?PaginationMeta $meta = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $cityFolder = isset($data['city_folder']) && is_array($data['city_folder'])
            ? CityFolder::fromArray($data['city_folder'])
            : null;

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

        return new self($cityFolder, $items, $meta);
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
