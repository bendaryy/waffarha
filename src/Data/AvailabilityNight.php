<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single night row inside an {@see AvailabilityCheck::$breakdown}.
 *
 * @phpstan-type AvailabilityNightPayload array{date?: string|null, price?: int|float|string|null, is_weekend?: bool|int|string|null, has_special_rate?: bool|int|string|null}
 */
final readonly class AvailabilityNight
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this night.
     */
    public function __construct(
        public ?string $date,
        public ?float $price,
        public ?bool $isWeekend,
        public ?bool $hasSpecialRate,
        public array $attributes,
    ) {}

    /**
     * @param  AvailabilityNightPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $price = $data['price'] ?? null;
        $isWeekend = $data['is_weekend'] ?? null;
        $hasSpecialRate = $data['has_special_rate'] ?? null;

        return new self(
            date: isset($data['date']) && is_scalar($data['date']) ? (string) $data['date'] : null,
            price: is_numeric($price) ? (float) $price : null,
            isWeekend: $isWeekend !== null ? (bool) $isWeekend : null,
            hasSpecialRate: $hasSpecialRate !== null ? (bool) $hasSpecialRate : null,
            attributes: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
