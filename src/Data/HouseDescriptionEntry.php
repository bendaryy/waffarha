<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single description line within a {@see HouseDescription} category.
 *
 * @phpstan-type EntryPayload array{description_id?: int|null, description?: string|null, sort_order?: int|null}
 */
final readonly class HouseDescriptionEntry
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?int $descriptionId,
        public ?string $description,
        public ?int $sortOrder,
        public array $attributes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            descriptionId: isset($data['description_id']) && is_scalar($data['description_id']) ? (int) $data['description_id'] : null,
            description: isset($data['description']) && is_scalar($data['description']) ? (string) $data['description'] : null,
            sortOrder: isset($data['sort_order']) && is_scalar($data['sort_order']) ? (int) $data['sort_order'] : null,
            attributes: $data,
        );
    }
}
