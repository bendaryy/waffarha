<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A city folder summary returned by `GET /waffarha/city-folders`.
 *
 * Only folders that currently contain at least one Waffarha-exposed unit
 * are returned. `unit_count` / `cover_images` reflect that same scope.
 *
 * @phpstan-type CityFolderPayload array<string, mixed>
 */
final readonly class CityFolder
{
    /**
     * @param  list<string>  $coverImages
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $nameEn,
        public ?string $nameAr,
        public ?int $unitCount,
        public array $coverImages,
        public array $attributes,
    ) {}

    /**
     * @param  CityFolderPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $int = static fn (string $key): ?int => isset($data[$key]) && is_numeric($data[$key])
            ? (int) $data[$key]
            : null;

        /** @var list<string> $coverImages */
        $coverImages = isset($data['cover_images']) && is_array($data['cover_images'])
            ? array_values(array_map(static fn ($image): string => (string) $image, $data['cover_images']))
            : [];

        return new self(
            id: $int('id'),
            name: $str('name'),
            nameEn: $str('name_en'),
            nameAr: $str('name_ar'),
            unitCount: $int('unit_count'),
            coverImages: $coverImages,
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
