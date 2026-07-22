<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection of {@see CityFolder} rows from `GET /waffarha/city-folders`.
 *
 * @implements IteratorAggregate<int, CityFolder>
 *
 * @phpstan-type ListPayload array{city_folders?: list<array<string, mixed>>}
 */
final readonly class CityFolderCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<CityFolder>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['city_folders']) && is_array($data['city_folders'])
            ? $data['city_folders']
            : [];

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = CityFolder::fromArray($row);
            }
        }

        return new self($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, CityFolder>
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
        return array_map(static fn (CityFolder $folder): array => $folder->toArray(), $this->items);
    }
}
