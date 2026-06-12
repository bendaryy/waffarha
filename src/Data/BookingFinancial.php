<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed mirror of the `financial` block returned by every Waffarha booking
 * endpoint (`POST /waffarha/bookings`, `GET /waffarha/bookings/{uuid}`, the
 * collection list, plus the outbound `reservation.*` webhooks).
 *
 * Only fields the partner needs for reconciliation (subtotal, cleaning fee,
 * total) are exposed — commission / net-amount stay internal to Maat. The
 * `total` here is the server-computed amount that landed on `tbl_book.total`
 * (Maat re-runs the same pipeline as `units()->checkAvailability()`), so it
 * is the authoritative number even when it differs from the `total_amount`
 * the partner sent in the create request.
 *
 * All monetary fields are floats in EGP.
 */
final readonly class BookingFinancial
{
    public function __construct(
        public string $currency,
        public float $subtotal,
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

        return new self(
            currency: isset($data['currency']) && is_string($data['currency']) && $data['currency'] !== ''
                ? $data['currency']
                : 'EGP',
            subtotal: $float('subtotal'),
            cleaningFee: $float('cleaning_fee'),
            total: $float('total'),
        );
    }
}
