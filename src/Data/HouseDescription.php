<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A house-description category (e.g. "Calm & Cozy") and its description lines,
 * from the single-unit response `house_descriptions` list.
 *
 * @phpstan-type HouseDescriptionPayload array{
 *     category_name?: string|null,
 *     category_icon?: string|null,
 *     sort_order?: int|null,
 *     descriptions?: list<array<string, mixed>>
 * }
 */
final readonly class HouseDescription
{
    /**
     * @param  list<HouseDescriptionEntry>  $descriptions
     */
    public function __construct(
        public ?string $categoryName,
        public ?string $categoryIcon,
        public ?int $sortOrder,
        public array $descriptions,
    ) {}

    /**
     * @param  HouseDescriptionPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $entries = [];
        if (isset($data['descriptions']) && is_array($data['descriptions'])) {
            foreach ($data['descriptions'] as $entry) {
                if (is_array($entry)) {
                    /** @var array<string, mixed> $entry */
                    $entries[] = HouseDescriptionEntry::fromArray($entry);
                }
            }
        }

        return new self(
            categoryName: isset($data['category_name']) && is_scalar($data['category_name']) ? (string) $data['category_name'] : null,
            categoryIcon: isset($data['category_icon']) && is_scalar($data['category_icon']) ? (string) $data['category_icon'] : null,
            sortOrder: isset($data['sort_order']) && is_scalar($data['sort_order']) ? (int) $data['sort_order'] : null,
            descriptions: $entries,
        );
    }
}
