<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed mirror of the `financial` block returned by every Waffarha booking
 * endpoint (`POST /waffarha/bookings`, `GET /waffarha/bookings/{uuid}`, the
 * collection list, plus the outbound `reservation.*` webhooks).
 *
 * Partner-safe money only — commission / net-amount stay internal to Maat.
 * All monetary fields are floats in EGP.
 */
final readonly class BookingFinancial
{
    public function __construct(
        public string $currency,
        public float $subtotal,
        public ?float $longStayDiscount,
        public ?bool $longStayApplied,
        public ?float $discountPercentage,
        public ?float $discountAmount,
        public ?float $subtotalAfterDiscount,
        public float $cleaningFee,
        public float $access,
        public float $serviceFee,
        public float $taxRate,
        public float $tax,
        public float $hostTaxRate,
        public float $taxFromHost,
        public float $total,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $float = static fn (string $key, float $default = 0.0): float => isset($data[$key]) && is_numeric($data[$key])
            ? (float) $data[$key]
            : $default;
        $nullableFloat = static fn (string $key): ?float => isset($data[$key]) && is_numeric($data[$key])
            ? (float) $data[$key]
            : null;

        $longStayDiscount = $nullableFloat('long_stay_discount');

        return new self(
            currency: isset($data['currency']) && is_string($data['currency']) && $data['currency'] !== ''
                ? $data['currency']
                : 'EGP',
            subtotal: $float('subtotal'),
            longStayDiscount: $longStayDiscount,
            longStayApplied: array_key_exists('long_stay_applied', $data)
                ? (bool) $data['long_stay_applied']
                : ($longStayDiscount !== null && $longStayDiscount > 0 ? true : null),
            discountPercentage: $nullableFloat('discount_percentage'),
            discountAmount: $nullableFloat('discount_amount'),
            subtotalAfterDiscount: $nullableFloat('subtotal_after_discount'),
            cleaningFee: $float('cleaning_fee'),
            access: $float('access'),
            serviceFee: $float('service_fee'),
            taxRate: $float('tax_rate'),
            tax: $float('tax'),
            hostTaxRate: $float('host_tax_rate'),
            taxFromHost: $float('tax_from_host'),
            total: $float('total'),
        );
    }
}
