<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * An amenity from the single-unit `amenities` list, or a catalogue row
 * nested under {@see AmenityGroup}.
 *
 * @phpstan-type AmenityPayload array{
 *     id?: int|null,
 *     img?: string|null,
 *     image?: string|null,
 *     title?: string|null,
 *     title_en?: string|null,
 *     title_ar?: string|null
 * }
 */
final readonly class Amenity
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?string $titleAr,
        public ?string $image,
        public ?string $titleEn = null,
    ) {}

    /**
     * @param  AmenityPayload|array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;

        return new self(
            id: isset($data['id']) && is_scalar($data['id']) ? (int) $data['id'] : null,
            title: $str('title'),
            titleAr: $str('title_ar'),
            image: $str('image') ?? $str('img'),
            titleEn: $str('title_en'),
        );
    }
}
