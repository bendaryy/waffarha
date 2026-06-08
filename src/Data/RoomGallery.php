<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A per-room image gallery from the single-unit `every_corner_count` list
 * (e.g. category "Bedroom 1" and its photos).
 *
 * @phpstan-type RoomGalleryPayload array{
 *     category_id?: int|null,
 *     category_name?: string|null,
 *     images?: list<array<string, mixed>>
 * }
 */
final readonly class RoomGallery
{
    /**
     * @param  list<GalleryImage>  $images
     */
    public function __construct(
        public ?int $categoryId,
        public ?string $categoryName,
        public array $images,
    ) {}

    /**
     * @param  RoomGalleryPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $images = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                if (is_array($image)) {
                    /** @var array<string, mixed> $image */
                    $images[] = GalleryImage::fromArray($image);
                }
            }
        }

        return new self(
            categoryId: isset($data['category_id']) && is_scalar($data['category_id']) ? (int) $data['category_id'] : null,
            categoryName: isset($data['category_name']) && is_scalar($data['category_name']) ? (string) $data['category_name'] : null,
            images: $images,
        );
    }
}
