<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single entry inside {@see AvailabilityCheck::$specialRatesApplied}.
 *
 * Represents one distinct host-configured SpecialRate that affected at
 * least one night in the booking window. Use this list when you want to
 * surface the applied promos to the guest ("Winter Promo — 20% premium")
 * without having to walk every row in `$breakdown` and dedupe by
 * `specialRateId`.
 *
 * All monetary values are in EGP — matches {@see AvailabilityFinancial::$currency}.
 * Dollar equivalents are intentionally not exposed on the Waffarha surface.
 *
 * `nightlyPriceOverride` is the raw stored percentage (e.g. `20` = 20%);
 * `isIncrease` tells you whether it pushed the price above the base.
 *
 * @phpstan-type SpecialRateAppliedPayload array{id?: int|string|null, name?: string|null, start_date?: string|null, end_date?: string|null, nightly_price_override?: int|float|string|null, effective_nightly_price?: int|float|string|null, base_price?: int|float|string|null, is_increase?: bool|int|string|null, is_discount?: bool|int|string|null, is_premium?: bool|int|string|null, discount_percentage?: int|float|string|null, increase_percentage?: int|float|string|null}
 */
final readonly class SpecialRateApplied
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $startDate,
        public ?string $endDate,
        public ?float $nightlyPriceOverride,
        public ?float $effectiveNightlyPrice,
        public ?float $basePrice,
        public ?bool $isIncrease,
        public ?bool $isDiscount,
        public ?bool $isPremium,
        public ?float $discountPercentage,
        public ?float $increasePercentage,
    ) {}

    /**
     * @param  SpecialRateAppliedPayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::nullableInt($data['id'] ?? null),
            name: self::nullableString($data['name'] ?? null),
            startDate: self::nullableString($data['start_date'] ?? null),
            endDate: self::nullableString($data['end_date'] ?? null),
            nightlyPriceOverride: self::nullableFloat($data['nightly_price_override'] ?? null),
            effectiveNightlyPrice: self::nullableFloat($data['effective_nightly_price'] ?? null),
            basePrice: self::nullableFloat($data['base_price'] ?? null),
            isIncrease: self::nullableBool($data['is_increase'] ?? null),
            isDiscount: self::nullableBool($data['is_discount'] ?? null),
            isPremium: self::nullableBool($data['is_premium'] ?? null),
            discountPercentage: self::nullableFloat($data['discount_percentage'] ?? null),
            increasePercentage: self::nullableFloat($data['increase_percentage'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function nullableBool(mixed $value): ?bool
    {
        return $value !== null ? (bool) $value : null;
    }
}
