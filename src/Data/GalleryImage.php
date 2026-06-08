<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single image within a {@see RoomGallery}.
 *
 * @phpstan-type GalleryImagePayload array{id?: int|null, img?: string|null}
 */
final readonly class GalleryImage
{
    public function __construct(
        public ?int $id,
        public ?string $image,
    ) {}

    /**
     * @param  GalleryImagePayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) && is_scalar($data['id']) ? (int) $data['id'] : null,
            image: isset($data['img']) && is_scalar($data['img']) ? (string) $data['img'] : null,
        );
    }
}
