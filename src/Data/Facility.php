<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use Maat\Waffarha\Resources\CityFolders;

/**
 * A single amenity / facility row inside a category group.
 *
 * Use {@see self::$id} with `facilities[]` on
 * {@see CityFolders::units()}.
 *
 * @phpstan-type FacilityPayload array<string, mixed>
 */
final readonly class Facility
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?string $titleEn,
        public ?string $titleAr,
        public ?string $image,
        public array $attributes,
    ) {}

    /**
     * @param  FacilityPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $id = $data['id'] ?? null;

        return new self(
            id: is_numeric($id) ? (int) $id : null,
            title: $str('title'),
            titleEn: $str('title_en'),
            titleAr: $str('title_ar'),
            image: $str('image') ?? $str('img'),
            attributes: $data,
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
