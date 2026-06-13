<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed mirror of the `financial` block returned by every Waffarha booking
 * endpoint (`POST /waffarha/bookings`, `GET /waffarha/bookings/{uuid}`, the
 * collection list, plus the outbound `reservation.*` webhooks).
 *
 * Only fields the partner needs for reconciliation (subtotal, optional
 * discount, cleaning fee, total) are exposed — commission / net-amount stay
 * internal to Maat. The `total` here is the server-computed amount that
 * landed on `tbl_book.total` (Maat re-runs the same pipeline as
 * `units()->checkAvailability()`), so it is the authoritative number even
 * when it differs from the `total_amount` the partner sent in the create
 * request.
 *
 * Discount keys (`discountPercentage`, `discountAmount`,
 * `subtotalAfterDiscount`) are populated only when the booking was created
 * with `discount_in_percentage` (Maat-coupon-style discount sourced from
 * Waffarha). When absent they are all `null`.
 *
 * All monetary fields are floats in EGP.
 */
final readonly class BookingFinancial
{
    public function __construct(
        public string $currency,
        public float $subtotal,
        public ?float $discountPercentage,
        public ?float $discountAmount,
        public ?float $subtotalAfterDiscount,
        public float $cleaningFee,
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

        return new self(
            currency: isset($data['currency']) && is_string($data['currency']) && $data['currency'] !== ''
                ? $data['currency']
                : 'EGP',
            subtotal: $float('subtotal'),
            discountPercentage: $nullableFloat('discount_percentage'),
            discountAmount: $nullableFloat('discount_amount'),
            subtotalAfterDiscount: $nullableFloat('subtotal_after_discount'),
            cleaningFee: $float('cleaning_fee'),
            total: $float('total'),
        );
    }
}
