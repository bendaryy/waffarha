<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single night row inside an {@see AvailabilityCheck::$breakdown}.
 *
 * Mirrors the rich `day_breakdown` produced by Maat's internal booking
 * pricing pipeline so partners can render the same UI they'd render for a
 * direct Maat checkout. All monetary fields are in EGP (matching
 * {@see AvailabilityFinancial::$currency}).
 *
 * `$price` is the **final** nightly amount the guest will be charged for
 * this date (= `price_after_special_rate + weekend_amount`).
 *
 * @phpstan-type AvailabilityNightPayload array{date?: string|null, day_name_english?: string|null, day_name_arabic?: string|null, is_weekend?: bool|int|string|null, base_price?: int|float|string|null, price_after_special_rate?: int|float|string|null, price?: int|float|string|null, has_special_rate?: bool|int|string|null, special_rate_id?: int|string|null, special_rate_name?: string|null, special_rate_percentage?: int|float|string|null, special_rate_is_increase?: bool|int|string|null, is_discount?: bool|int|string|null, is_premium?: bool|int|string|null, discount_percentage?: int|float|string|null, increase_percentage?: int|float|string|null, weekend_percentage?: int|float|string|null, weekend_amount?: int|float|string|null}
 */
final readonly class AvailabilityNight
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this night.
     */
    public function __construct(
        public ?string $date,
        public ?string $dayNameEnglish,
        public ?string $dayNameArabic,
        public ?bool $isWeekend,
        public ?float $basePrice,
        public ?float $priceAfterSpecialRate,
        public ?float $price,
        public ?bool $hasSpecialRate,
        public ?int $specialRateId,
        public ?string $specialRateName,
        public ?float $specialRatePercentage,
        public ?bool $specialRateIsIncrease,
        public ?bool $isDiscount,
        public ?bool $isPremium,
        public ?float $discountPercentage,
        public ?float $increasePercentage,
        public ?float $weekendPercentage,
        public ?float $weekendAmount,
        public array $attributes,
    ) {}

    /**
     * @param  AvailabilityNightPayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            date: self::nullableString($data['date'] ?? null),
            dayNameEnglish: self::nullableString($data['day_name_english'] ?? null),
            dayNameArabic: self::nullableString($data['day_name_arabic'] ?? null),
            isWeekend: self::nullableBool($data['is_weekend'] ?? null),
            basePrice: self::nullableFloat($data['base_price'] ?? null),
            priceAfterSpecialRate: self::nullableFloat($data['price_after_special_rate'] ?? null),
            price: self::nullableFloat($data['price'] ?? null),
            hasSpecialRate: self::nullableBool($data['has_special_rate'] ?? null),
            specialRateId: self::nullableInt($data['special_rate_id'] ?? null),
            specialRateName: self::nullableString($data['special_rate_name'] ?? null),
            specialRatePercentage: self::nullableFloat($data['special_rate_percentage'] ?? null),
            specialRateIsIncrease: self::nullableBool($data['special_rate_is_increase'] ?? null),
            isDiscount: self::nullableBool($data['is_discount'] ?? null),
            isPremium: self::nullableBool($data['is_premium'] ?? null),
            discountPercentage: self::nullableFloat($data['discount_percentage'] ?? null),
            increasePercentage: self::nullableFloat($data['increase_percentage'] ?? null),
            weekendPercentage: self::nullableFloat($data['weekend_percentage'] ?? null),
            weekendAmount: self::nullableFloat($data['weekend_amount'] ?? null),
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
