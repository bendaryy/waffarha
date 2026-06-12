<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed mirror of the `financial` block returned by every Waffarha booking
 * endpoint (`POST /waffarha/bookings`, `GET /waffarha/bookings/{uuid}`, the
 * collection list, plus the outbound `reservation.*` webhooks).
 *
 * The numbers are exactly what Maat persisted on `tbl_book` after running the
 * server-side pricing pipeline — they are the source of truth for partner
 * payouts and reconciliation, **not** the `total_amount` the partner sent in
 * the create request (which is informational and logged-on-mismatch).
 *
 * All monetary fields are floats in EGP (other currencies were never stored;
 * the property's base currency is converted at booking time). `netAmount` is
 * nullable because legacy bookings created before the financials refactor may
 * have `tbl_book.net_amount` set to `NULL`.
 */
final readonly class BookingFinancial
{
    public function __construct(
        public string $currency,
        public float $subtotal,
        public float $cleaningFee,
        public float $total,
        public float $commissionPerDay,
        public float $totalCommission,
        public ?float $netAmount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $float = static fn (string $key, float $default = 0.0): float => isset($data[$key]) && is_numeric($data[$key])
            ? (float) $data[$key]
            : $default;

        $netAmount = $data['net_amount'] ?? null;

        return new self(
            currency: isset($data['currency']) && is_string($data['currency']) && $data['currency'] !== ''
                ? $data['currency']
                : 'EGP',
            subtotal: $float('subtotal'),
            cleaningFee: $float('cleaning_fee'),
            total: $float('total'),
            commissionPerDay: $float('commission_per_day'),
            totalCommission: $float('total_commission'),
            netAmount: is_numeric($netAmount) ? (float) $netAmount : null,
        );
    }
}
