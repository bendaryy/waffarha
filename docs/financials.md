# Financial fields (EGP)

Every Waffarha money field is in **EGP**. Maat converts property base currency
to EGP before returning amounts. Do not send or expect other currencies for
settlement.

## Total formula (guest pays)

```
total = subtotal_after_discount + cleaning_fee + access + tax_from_host
```

When there is no discount, `subtotal_after_discount` = `subtotal`.

```
tax_from_host = round(subtotal × host_tax_rate / 100, 2)
```

Host tax is always on the **original** `subtotal` (Maat-coupon shape), even
when `discount_in_percentage` is set. Cleaning fee, access, and host tax are
never discounted.

Commission is **not** part of `total`.

---

## Where each field appears

| Field | Check | Preview / create / get | Guest receipt (`bookDetails`) | Notes |
|-------|:-----:|:----------------------:|:-----------------------------:|-------|
| `currency` | ✓ | ✓ | ✓ | Always `"EGP"` |
| `subtotal` | ✓ | ✓ | ✓ | Sum of nightly `price` |
| `discount_percentage` | ✓* | ✓* | ✓* | Only when discount applied |
| `discount_amount` | ✓* | ✓* | ✓* | Only when discount applied |
| `subtotal_after_discount` | ✓* | ✓* | ✓* | Only when discount applied |
| `cleaning_fee` | ✓ | ✓ | ✓ | One-time |
| `access` | ✓ | ✓ | ✓ | One-time (`tbl_property.access`) |
| `host_tax_rate` | ✓ | ✓ | ✓ | `%` from `tbl_property.tax` |
| `tax_from_host` | ✓ | ✓ | ✓ | Amount on original subtotal |
| `total` | ✓ | ✓ | ✓† | Guest payable total |
| `commission_percentage` | ✓ | — | — | Check only (informational) |
| `commission_amount` | ✓ | — | — | Check only; **not** in `total` |

\* Omitted when no `discount_in_percentage` was used.  
† Receipt uses `total` at the top level and `financial_summary.total_amount`.

---

## Check — `AvailabilityFinancial`

Returned by [`units()->checkAvailability()`](check-availability.md) under
`financial`. Typed as [`AvailabilityFinancial`](data-objects.md#availabilityfinancial).

| JSON key | DTO property | Type | Description |
|----------|--------------|------|-------------|
| `currency` | `currency` | string | Always `"EGP"` |
| `subtotal` | `subtotal` | float | Nightly sum before partner discount |
| `discount_percentage` | `discountPercentage` | ?float | Echo of `discount_in_percentage` |
| `discount_amount` | `discountAmount` | ?float | `subtotal × discount_percentage / 100` |
| `subtotal_after_discount` | `subtotalAfterDiscount` | ?float | `subtotal − discount_amount` |
| `cleaning_fee` | `cleaningFee` | float | One-time cleaning fee |
| `access` | `access` | float | One-time access fee |
| `host_tax_rate` | `hostTaxRate` | float | Host tax % |
| `tax_from_host` | `taxFromHost` | float | Host tax amount |
| `commission_percentage` | `commissionPercentage` | float | Maat platform commission % |
| `commission_amount` | `commissionAmount` | float | Commission on **original** subtotal |
| `total` | `total` | float | What the guest / partner pays |

```php
$check = Waffarha::units()->checkAvailability($uuid, [
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'discount_in_percentage' => 10,
]);

$check->financial->total;              // send as create total_amount
$check->financial->commissionAmount;   // informational — not billed to guest
```

---

## Booking — `BookingFinancial`

Returned by [`preview()`](booking-preview.md), [`create()`](create-booking.md),
[`get()`](get-booking.md), and [`list()`](list-bookings.md) under
`booking.financial`. Typed as [`BookingFinancial`](data-objects.md#bookingfinancial).

Same guest money as check, **without** commission fields (those stay internal
to Maat for host payouts).

| JSON key | DTO property | Type | Description |
|----------|--------------|------|-------------|
| `currency` | `currency` | string | Always `"EGP"` |
| `subtotal` | `subtotal` | float | Nightly sum |
| `discount_percentage` | `discountPercentage` | ?float | Present only if discount applied |
| `discount_amount` | `discountAmount` | ?float | Present only if discount applied |
| `subtotal_after_discount` | `subtotalAfterDiscount` | ?float | Present only if discount applied |
| `cleaning_fee` | `cleaningFee` | float | One-time |
| `access` | `access` | float | One-time |
| `host_tax_rate` | `hostTaxRate` | float | Host tax % |
| `tax_from_host` | `taxFromHost` | float | Host tax amount |
| `total` | `total` | float | Guest payable (= `total_amount` on the booking) |

```php
$preview = Waffarha::bookings()->preview([/* … */]);
$preview->financial->total;
$preview->financial->access;
$preview->financial->taxFromHost;
```

Top-level booking also has `total_amount` / `currency` mirroring `financial.total`.

---

## Guest receipt — `GuestBookDetails`

Returned by [`bookDetails()`](book-details.md). Shape is `bookdetails` (not
`booking.financial`). Money fields:

| JSON key | DTO property | Description |
|----------|--------------|-------------|
| `subtotal` | `subtotal` | Nightly sum |
| `cleaning_fee` | `cleaningFee` | One-time |
| `access` | `access` | One-time |
| `host_tax_rate` | `hostTaxRate` | Host tax % |
| `tax_from_host` | `taxFromHost` | Host tax amount |
| `total` | `total` | Guest payable |
| `financial_summary.*` | `financialSummary` | Same amounts; total key is `total_amount` |
| `day_breakdown[]` | `dayBreakdown` | Per-night rows |

Discount keys may appear on the receipt when a Waffarha percentage discount
was stored on the booking.

---

## Never exposed to partners

These are computed and stored on `tbl_book` for Maat host accounting, but are
**not** returned on booking / preview / receipt / webhook partner payloads:

| Internal field | Meaning |
|----------------|---------|
| `commission` / `commission_per_day` | Per-night commission |
| `total_commission` | Trip commission total |
| `net_amount` | Host net after commission |

Check exposes `commission_percentage` / `commission_amount` only as a
reconcile hint — they are still **not** added to guest `total`.

---

## Discount (`discount_in_percentage`)

Optional `0`–`100` on check, preview, and create.

1. Applied to nightly **subtotal** only.
2. Cleaning / access / host tax unchanged.
3. Commission (internal + check display) stays on the **original** subtotal —
   Maat eats the discount, not the host.
4. Re-send the **same** percentage on create that you used on check/preview.

---

## Example numbers

3 nights, subtotal `4500`, cleaning `250`, access `100`, host tax `14%`,
no discount:

| Field | Value |
|-------|------:|
| `subtotal` | 4500.00 |
| `cleaning_fee` | 250.00 |
| `access` | 100.00 |
| `host_tax_rate` | 14.00 |
| `tax_from_host` | 630.00 |
| `total` | **5480.00** |

Same trip with `discount_in_percentage = 10`:

| Field | Value |
|-------|------:|
| `subtotal` | 4500.00 |
| `discount_amount` | 450.00 |
| `subtotal_after_discount` | 4050.00 |
| `cleaning_fee` | 250.00 |
| `access` | 100.00 |
| `tax_from_host` | 630.00 *(still on 4500)* |
| `total` | **5030.00** |
| `commission_amount` (check only) | still on 4500 |
