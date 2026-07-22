<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Money fields returned inside the `financial` block of a
 * `POST /waffarha/unit/{uuid}/check` response.
 *
 * Guest `total` =
 * `subtotal_after_discount + cleaning_fee + access + tax_from_host + service_fee + tax`.
 * Long-stay is applied before the partner percentage. Commission is
 * informational only and is **not** added to `total`.
 *
 * @phpstan-type FinancialPayload array{currency?: string|null, base_price?: int|float|string|null, subtotal?: int|float|string|null, long_stay_discount?: int|float|string|null, long_stay_applied?: bool|int|string|null, discount_percentage?: int|float|string|null, discount_amount?: int|float|string|null, subtotal_after_discount?: int|float|string|null, cleaning_fee?: int|float|string|null, access?: int|float|string|null, service_fee?: int|float|string|null, tax_rate?: int|float|string|null, tax?: int|float|string|null, host_tax_rate?: int|float|string|null, tax_from_host?: int|float|string|null, commission_percentage?: int|float|string|null, commission_amount?: int|float|string|null, total?: int|float|string|null}
 */
final readonly class AvailabilityFinancial
{
    public function __construct(
        public ?string $currency,
        public ?float $basePrice,
        public ?float $subtotal,
        public ?float $longStayDiscount,
        public ?bool $longStayApplied,
        public ?float $discountPercentage,
        public ?float $discountAmount,
        public ?float $subtotalAfterDiscount,
        public ?float $cleaningFee,
        public ?float $access,
        public ?float $serviceFee,
        public ?float $taxRate,
        public ?float $tax,
        public ?float $hostTaxRate,
        public ?float $taxFromHost,
        public ?float $commissionPercentage,
        public ?float $commissionAmount,
        public ?float $total,
    ) {}

    /**
     * @param  FinancialPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $subtotal = self::nullableFloat($data['subtotal'] ?? null);
        $longStayDiscount = self::nullableFloat($data['long_stay_discount'] ?? null);
        $discountPercentage = self::nullableFloat($data['discount_percentage'] ?? null);
        $discountAmount = self::nullableFloat($data['discount_amount'] ?? null);
        $subtotalAfterDiscount = self::nullableFloat($data['subtotal_after_discount'] ?? null);
        $cleaningFee = self::nullableFloat($data['cleaning_fee'] ?? null);
        $access = self::nullableFloat($data['access'] ?? null);
        $serviceFee = self::nullableFloat($data['service_fee'] ?? null);
        $tax = self::nullableFloat($data['tax'] ?? null);
        $hostTaxRate = self::nullableFloat($data['host_tax_rate'] ?? null);
        $taxFromHost = self::nullableFloat($data['tax_from_host'] ?? null);
        $total = self::nullableFloat($data['total'] ?? null);

        $subtotalForTotal = $subtotalAfterDiscount
            ?? ($discountAmount !== null && $subtotal !== null
                ? round(max(0.0, $subtotal - ($longStayDiscount ?? 0.0) - $discountAmount), 2)
                : ($longStayDiscount !== null && $subtotal !== null
                    ? round(max(0.0, $subtotal - $longStayDiscount), 2)
                    : $subtotal));

        return new self(
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            basePrice: self::nullableFloat($data['base_price'] ?? null),
            subtotal: $subtotal,
            longStayDiscount: $longStayDiscount,
            longStayApplied: array_key_exists('long_stay_applied', $data)
                ? (bool) $data['long_stay_applied']
                : ($longStayDiscount !== null && $longStayDiscount > 0 ? true : null),
            discountPercentage: $discountPercentage,
            discountAmount: $discountAmount,
            subtotalAfterDiscount: $subtotalAfterDiscount,
            cleaningFee: $cleaningFee,
            access: $access,
            serviceFee: $serviceFee,
            taxRate: self::nullableFloat($data['tax_rate'] ?? null),
            tax: $tax,
            hostTaxRate: $hostTaxRate,
            taxFromHost: $taxFromHost,
            commissionPercentage: self::nullableFloat($data['commission_percentage'] ?? null),
            commissionAmount: self::nullableFloat($data['commission_amount'] ?? null),
            total: $total ?? ($subtotalForTotal !== null
                ? round(
                    $subtotalForTotal
                    + ($cleaningFee ?? 0.0)
                    + ($access ?? 0.0)
                    + ($taxFromHost ?? 0.0)
                    + ($serviceFee ?? 0.0)
                    + ($tax ?? 0.0),
                    2
                )
                : null),
        );
    }

    /**
     * @param  array{currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, total?: int|float|string|null}  $legacy
     */
    public static function fromLegacyTopLevel(array $legacy): self
    {
        return self::fromArray([
            'currency' => $legacy['currency'] ?? null,
            'subtotal' => $legacy['subtotal'] ?? null,
            'cleaning_fee' => $legacy['cleaning_fee'] ?? null,
            'total' => $legacy['total'] ?? null,
        ]);
    }

    private static function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
