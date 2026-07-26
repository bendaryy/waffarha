<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * One category group from `GET /waffarha/amenities`.
 *
 * Iterable over nested {@see Amenity} rows.
 *
 * @implements IteratorAggregate<int, Amenity>
 *
 * @phpstan-type AmenityGroupPayload array<string, mixed>
 */
final readonly class AmenityGroup implements Countable, IteratorAggregate
{
    /**
     * @param  list<Amenity>  $amenities
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?int $categoryId,
        public ?string $categoryName,
        public ?string $categoryNameEn,
        public ?string $categoryNameAr,
        public ?string $categoryIcon,
        public array $amenities,
        public array $attributes,
    ) {}

    /**
     * @param  AmenityGroupPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $categoryId = $data['category_id'] ?? null;

        $rows = isset($data['amenities']) && is_array($data['amenities'])
            ? $data['amenities']
            : [];

        $amenities = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $amenities[] = Amenity::fromArray($row);
            }
        }

        return new self(
            categoryId: is_numeric($categoryId) ? (int) $categoryId : null,
            categoryName: $str('category_name'),
            categoryNameEn: $str('category_name_en'),
            categoryNameAr: $str('category_name_ar'),
            categoryIcon: $str('category_icon'),
            amenities: $amenities,
            attributes: $data,
        );
    }

    public function count(): int
    {
        return count($this->amenities);
    }

    /**
     * @return Traversable<int, Amenity>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->amenities);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
