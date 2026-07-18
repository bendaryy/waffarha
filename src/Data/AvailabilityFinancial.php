<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use Maat\Waffarha\Resources\Bookings;

/**
 * Money fields returned inside the `financial` block of a
 * `POST /waffarha/unit/{uuid}/check` response.
 *
 *  - `$subtotal` is the nightly sum (already in `$currency`).
 *  - `$discountPercentage` / `$discountAmount` / `$subtotalAfterDiscount`
 *    are populated when the request carried `discount_in_percentage` —
 *    they mirror the Maat-coupon math (`subtotal × pct ÷ 100`, applied
 *    before commission and `total`). All three are `null` when no discount
 *    applied so partners can branch on `discountPercentage`.
 *  - `$cleaningFee` is the one-time per-booking cleaning fee (`0.0` when
 *    the host has not configured one). Discounts never apply to it.
 *  - `$access` is the one-time access fee (EGP). Discounts never apply to it.
 *  - `$hostTaxRate` / `$taxFromHost` are the host property tax (% on the
 *    **original** subtotal). Added to guest `total`; commission-free.
 *  - `$total` = `$subtotalAfterDiscount + $cleaningFee + $access +
 *    $taxFromHost` (or `$subtotal + …` when no discount) — this is the
 *    headline figure the partner should display to the guest and send
 *    back as `total_amount` on {@see Bookings::create()}.
 *  - `$commissionPercentage` is Maat's platform rate (e.g. `1.00` means 1%),
 *    and `$commissionAmount` is calculated against the **original
 *    `$subtotal`** (NOT `$subtotalAfterDiscount`) — Maat absorbs the
 *    discount so the host is paid as if none existed. Cleaning fee /
 *    access / tax_from_host are always commission-free. Commission is
 *    **NOT** added to `$total`.
 *
 * @phpstan-type FinancialPayload array{currency?: string|null, subtotal?: int|float|string|null, discount_percentage?: int|float|string|null, discount_amount?: int|float|string|null, subtotal_after_discount?: int|float|string|null, cleaning_fee?: int|float|string|null, access?: int|float|string|null, host_tax_rate?: int|float|string|null, tax_from_host?: int|float|string|null, commission_percentage?: int|float|string|null, commission_amount?: int|float|string|null, total?: int|float|string|null}
 */
final readonly class AvailabilityFinancial
{
    public function __construct(
        public ?string $currency,
        public ?float $subtotal,
        public ?float $discountPercentage,
        public ?float $discountAmount,
        public ?float $subtotalAfterDiscount,
        public ?float $cleaningFee,
        public ?float $access,
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
        $discountPercentage = self::nullableFloat($data['discount_percentage'] ?? null);
        $discountAmount = self::nullableFloat($data['discount_amount'] ?? null);
        $subtotalAfterDiscount = self::nullableFloat($data['subtotal_after_discount'] ?? null);
        $cleaningFee = self::nullableFloat($data['cleaning_fee'] ?? null);
        $access = self::nullableFloat($data['access'] ?? null);
        $hostTaxRate = self::nullableFloat($data['host_tax_rate'] ?? null);
        $taxFromHost = self::nullableFloat($data['tax_from_host'] ?? null);
        $total = self::nullableFloat($data['total'] ?? null);

        // Effective subtotal once the discount (if any) has been applied —
        // used as the fallback base for `$total` when the API omits it.
        $subtotalForTotal = $subtotalAfterDiscount
            ?? ($discountAmount !== null && $subtotal !== null
                ? round(max(0.0, $subtotal - $discountAmount), 2)
                : $subtotal);

        return new self(
            currency: isset($data['currency']) && is_scalar($data['currency']) ? (string) $data['currency'] : null,
            subtotal: $subtotal,
            discountPercentage: $discountPercentage,
            discountAmount: $discountAmount,
            subtotalAfterDiscount: $subtotalAfterDiscount,
            cleaningFee: $cleaningFee,
            access: $access,
            hostTaxRate: $hostTaxRate,
            taxFromHost: $taxFromHost,
            commissionPercentage: self::nullableFloat($data['commission_percentage'] ?? null),
            commissionAmount: self::nullableFloat($data['commission_amount'] ?? null),
            // Fall back to `subtotal_after_discount + cleaning_fee + access +
            // tax_from_host` so older responses still parse. Commission is
            // intentionally NOT part of the fallback.
            total: $total ?? ($subtotalForTotal !== null
                ? round($subtotalForTotal + ($cleaningFee ?? 0.0) + ($access ?? 0.0) + ($taxFromHost ?? 0.0), 2)
                : null),
        );
    }

    /**
     * @param  array{currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, total?: int|float|string|null}  $legacy
     *
     * Build a `financial` block from the legacy top-level keys returned by
     * pre-commission Maat responses. `commission_*`, discount, access, and
     * host-tax fields are left null in that case.
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
