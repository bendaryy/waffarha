<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A safety item from the single-unit `house_safety` list, with its English and
 * Arabic copy and the category it belongs to.
 *
 * @phpstan-type SafetyItemPayload array<string, mixed>
 */
final readonly class SafetyItem
{
    public function __construct(
        public ?int $id,
        public ?int $categoryId,
        public ?int $sortOrder,
        public ?string $icon,
        public ?string $name,
        public ?string $description,
        public ?string $nameAr,
        public ?string $descriptionAr,
        public ?string $categoryName,
        public ?string $categoryIcon,
    ) {}

    /**
     * @param  SafetyItemPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;
        $int = static fn (string $key): ?int => isset($data[$key]) && is_scalar($data[$key]) ? (int) $data[$key] : null;

        return new self(
            id: $int('id'),
            categoryId: $int('category_id'),
            sortOrder: $int('sort_order'),
            icon: $str('icon'),
            name: $str('name'),
            description: $str('description'),
            nameAr: $str('name_ar'),
            descriptionAr: $str('description_ar'),
            categoryName: $str('category_name'),
            categoryIcon: $str('category_icon'),
        );
    }
}
