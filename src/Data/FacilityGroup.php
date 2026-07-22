<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * One category group from `GET /waffarha/facilities`.
 *
 * Iterable over nested {@see Facility} rows.
 *
 * @implements IteratorAggregate<int, Facility>
 *
 * @phpstan-type FacilityGroupPayload array<string, mixed>
 */
final readonly class FacilityGroup implements Countable, IteratorAggregate
{
    /**
     * @param  list<Facility>  $facilities
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?int $categoryId,
        public ?string $categoryName,
        public ?string $categoryNameEn,
        public ?string $categoryNameAr,
        public ?string $categoryIcon,
        public array $facilities,
        public array $attributes,
    ) {}

    /**
     * @param  FacilityGroupPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $categoryId = $data['category_id'] ?? null;

        $rows = isset($data['facilities']) && is_array($data['facilities'])
            ? $data['facilities']
            : [];

        $facilities = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $facilities[] = Facility::fromArray($row);
            }
        }

        return new self(
            categoryId: is_numeric($categoryId) ? (int) $categoryId : null,
            categoryName: $str('category_name'),
            categoryNameEn: $str('category_name_en'),
            categoryNameAr: $str('category_name_ar'),
            categoryIcon: $str('category_icon'),
            facilities: $facilities,
            attributes: $data,
        );
    }

    public function count(): int
    {
        return count($this->facilities);
    }

    /**
     * @return Traversable<int, Facility>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->facilities);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
