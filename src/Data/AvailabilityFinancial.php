<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Money fields returned inside the `financial` block of a
 * `POST /waffarha/unit/{uuid}/check` response.
 *
 *  - `$subtotal` is the nightly sum (already in `$currency`).
 *  - `$cleaningFee` is the one-time per-booking cleaning fee (`0.0` when the
 *    host has not configured one).
 *  - `$total` = `$subtotal + $cleaningFee` — this is the headline figure
 *    the partner should display to the guest and send back as
 *    `total_amount` on {@see \Maat\Waffarha\Resources\Bookings::create()}.
 *  - `$commissionPercentage` mirrors `tbl_setting.commission` (e.g. `1.00`
 *    means 1%), and `$commissionAmount` is the calculated amount applied to
 *    the nightly subtotal (cleaning fee is commission-free). Commission is
 *    **NOT** added to `$total` — same convention as `v1/u_simulate_booking`
 *    on the regular Maat surface. It is exposed so partners can reconcile
 *    their share against Maat's host payouts.
 *
 * @phpstan-type FinancialPayload array{currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, commission_percentage?: int|float|string|null, commission_amount?: int|float|string|null, total?: int|float|string|null}
 */
final readonly class AvailabilityFinancial
{
    public function __construct(
        public ?string $currency,
        public ?float $subtotal,
        public ?float $cleaningFee,
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
        $cleaningFee = self::nullableFloat($data['cleaning_fee'] ?? null);
        $total = self::nullableFloat($data['total'] ?? null);

        return new self(
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            subtotal: $subtotal,
            cleaningFee: $cleaningFee,
            commissionPercentage: self::nullableFloat($data['commission_percentage'] ?? null),
            commissionAmount: self::nullableFloat($data['commission_amount'] ?? null),
            // Fall back to `subtotal + cleaning_fee` when the API omits
            // `total`. Commission is intentionally NOT part of the fallback
            // — same convention as v1/u_simulate_booking, where commission
            // is reported separately and never folded into `total_amount`.
            total: $total ?? ($subtotal !== null
                ? round($subtotal + ($cleaningFee ?? 0.0), 2)
                : null),
        );
    }

    /**
     * @param  array{currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, total?: int|float|string|null}  $legacy
     *
     * Build a `financial` block from the legacy top-level keys returned by
     * pre-commission Maat responses. `commission_*` is left null in that
     * case.
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
