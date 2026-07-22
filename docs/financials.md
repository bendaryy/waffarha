# Financial fields (EGP)

Every Waffarha money field is in **EGP**. Maat converts property base currency
to EGP before returning amounts. Do not send or expect other currencies for
settlement.

## Total formula (guest pays)

```
total = subtotal_after_discount + cleaning_fee + access + tax_from_host
      + service_fee + tax
```

When there is no long-stay and no partner discount, `subtotal_after_discount`
equals `subtotal`.

Order of discounts on the nightly subtotal:

1. **Long-stay discount** (automatic when an active long-stay rule matches the
   trip length).
2. **Partner percentage** (`discount_in_percentage`) on the post–long-stay
   amount.

```
tax_from_host = round(subtotal × host_tax_rate / 100, 2)
```

Host tax is always on the **original** `subtotal`, even when long-stay or
`discount_in_percentage` applies. Cleaning fee, access, host tax, service fee,
and tax on the service fee are never reduced by either discount.

`tax` is the platform tax on `service_fee` (rate in `tax_rate`). It is **not**
the same as `tax_from_host`.

Commission is **not** part of `total`.

---

## Where each field appears

| Field | Check | Preview / create / get | Guest receipt (`bookDetails`) | Notes |
|-------|:-----:|:----------------------:|:-----------------------------:|-------|
| `currency` | ✓ | ✓ | ✓ | Always `"EGP"` |
| `base_price` | ✓ | — | — | Check only — nightly base in EGP |
| `subtotal` | ✓ | ✓ | ✓ | Sum of nightly `price` |
| `long_stay_discount` | ✓* | ✓* | ✓* | When a long-stay rule applied |
| `long_stay_applied` | ✓* | ✓* | ✓* | `true` when long-stay applied |
| `discount_percentage` | ✓† | ✓† | ✓† | Only when partner % used |
| `discount_amount` | ✓† | ✓† | ✓† | Only when partner % used |
| `subtotal_after_discount` | ✓‡ | ✓‡ | ✓‡ | After long-stay and/or partner % |
| `cleaning_fee` | ✓ | ✓ | ✓ | One-time |
| `access` | ✓ | ✓ | ✓ | One-time access fee |
| `service_fee` | ✓ | ✓ | ✓ | Platform service fee |
| `tax_rate` | ✓ | ✓§ | — | % applied to `service_fee` |
| `tax` | ✓ | ✓ | ✓ | Tax amount on `service_fee` |
| `host_tax_rate` | ✓ | ✓ | ✓ | Host property tax % |
| `tax_from_host` | ✓ | ✓ | ✓ | Amount on original subtotal |
| `total` | ✓ | ✓ | ✓¶ | Guest payable total |
| `commission_percentage` | ✓ | — | — | Check only (informational) |
| `commission_amount` | ✓ | — | — | Check only; **not** in `total` |

\* Present when long-stay applied (booking/receipt omit when zero).  
† Omitted when no `discount_in_percentage` was used.  
‡ Present when long-stay and/or partner discount applied.  
§ Present on preview; may be omitted on persisted booking rows.  
¶ Receipt uses `total` at the top level and `financial_summary.total_amount`.

---

## Check — `AvailabilityFinancial`

Returned by [`units()->checkAvailability()`](check-availability.md) under
`financial`. Typed as [`AvailabilityFinancial`](data-objects.md#availabilityfinancial).

| JSON key | DTO property | Type | Description |
|----------|--------------|------|-------------|
| `currency` | `currency` | string | Always `"EGP"` |
| `base_price` | `basePrice` | ?float | Nightly base before special/weekend |
| `subtotal` | `subtotal` | float | Nightly sum before discounts |
| `long_stay_discount` | `longStayDiscount` | ?float | Long-stay reduction on subtotal |
| `long_stay_applied` | `longStayApplied` | ?bool | Whether long-stay applied |
| `discount_percentage` | `discountPercentage` | ?float | Echo of `discount_in_percentage` |
| `discount_amount` | `discountAmount` | ?float | Partner % amount (after long-stay) |
| `subtotal_after_discount` | `subtotalAfterDiscount` | ?float | After long-stay + partner % |
| `cleaning_fee` | `cleaningFee` | float | One-time cleaning fee |
| `access` | `access` | float | One-time access fee |
| `service_fee` | `serviceFee` | float | Platform service fee |
| `tax_rate` | `taxRate` | ?float | Tax % on `service_fee` |
| `tax` | `tax` | ?float | Tax amount on `service_fee` |
| `host_tax_rate` | `hostTaxRate` | float | Host tax % |
| `tax_from_host` | `taxFromHost` | float | Host tax amount |
| `commission_percentage` | `commissionPercentage` | float | Maat platform commission % |
| `commission_amount` | `commissionAmount` | float | Commission on post–long-stay base |
| `total` | `total` | float | What the guest / partner pays |

```php
$check = Waffarha::units()->checkAvailability($uuid, [
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'discount_in_percentage' => 10,
]);

$check->financial->total;              // send as create total_amount
$check->financial->longStayDiscount;   // automatic when eligible
$check->financial->serviceFee;
$check->financial->tax;
$check->financial->commissionAmount;   // informational — not billed to guest
```

Check also returns top-level channel metadata (not inside `financial`):

| JSON key | DTO property | Description |
|----------|--------------|-------------|
| `is_xuru_unit` | `isXuruUnit` | Unit is synced to an external channel manager |
| `xuru_status` | `xuruStatus` | Channel connectivity hint when applicable |
| `xuru_price_applied` | `xuruPriceApplied` | Nightly prices came from the channel override |
| `effective_minimum_stay` | `effectiveMinimumStay` | Nights required for this window |

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
| `long_stay_discount` | `longStayDiscount` | ?float | When long-stay applied |
| `long_stay_applied` | `longStayApplied` | ?bool | When long-stay applied |
| `discount_percentage` | `discountPercentage` | ?float | Present only if partner % applied |
| `discount_amount` | `discountAmount` | ?float | Present only if partner % applied |
| `subtotal_after_discount` | `subtotalAfterDiscount` | ?float | After long-stay and/or partner % |
| `cleaning_fee` | `cleaningFee` | float | One-time |
| `access` | `access` | float | One-time |
| `service_fee` | `serviceFee` | float | Platform service fee |
| `tax_rate` | `taxRate` | float | Tax % on service fee (0 when omitted upstream) |
| `tax` | `tax` | float | Tax on service fee |
| `host_tax_rate` | `hostTaxRate` | float | Host tax % |
| `tax_from_host` | `taxFromHost` | float | Host tax amount |
| `total` | `total` | float | Guest payable (= `total_amount` on the booking) |

```php
$preview = Waffarha::bookings()->preview([/* … */]);
$preview->financial->total;
$preview->financial->longStayDiscount;
$preview->financial->serviceFee;
$preview->financial->tax;
```

Top-level booking also has `total_amount` / `currency` mirroring `financial.total`.

---

## Guest receipt — `GuestBookDetails`

Returned by [`bookDetails()`](book-details.md). Shape is `bookdetails` (not
`booking.financial`). Money fields:

| JSON key | DTO property | Description |
|----------|--------------|-------------|
| `subtotal` | `subtotal` | Nightly sum |
| `long_stay_discount` | `longStayDiscount` | When long-stay applied |
| `long_stay_applied` | `longStayApplied` | When long-stay applied |
| `cleaning_fee` | `cleaningFee` | One-time |
| `access` | `access` | One-time |
| `service_fee` | `serviceFee` | Platform service fee |
| `tax` | `tax` | Tax on service fee |
| `host_tax_rate` | `hostTaxRate` | Host tax % |
| `tax_from_host` | `taxFromHost` | Host tax amount |
| `total` | `total` | Guest payable |
| `financial_summary.*` | `financialSummary` | Same amounts; total key is `total_amount` |
| `day_breakdown[]` | `dayBreakdown` | Per-night rows |

Discount keys may appear on the receipt when a Waffarha percentage discount
was stored on the booking.

---

## Never exposed to partners

These are computed for Maat host accounting, but are **not** returned on
booking / preview / receipt / webhook partner payloads:

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

1. Applied to the nightly subtotal **after** any long-stay discount.
2. Cleaning / access / host tax / service fee / tax unchanged.
3. Commission (internal + check display) stays on the post–long-stay base —
   Maat eats the partner discount, not the host.
4. Re-send the **same** percentage on create that you used on check/preview.

---

## Example numbers

3 nights, subtotal `4500`, cleaning `250`, access `100`, host tax `14%`,
service fee `50`, tax on service fee `7`, no long-stay, no partner discount:

| Field | Value |
|-------|------:|
| `subtotal` | 4500.00 |
| `cleaning_fee` | 250.00 |
| `access` | 100.00 |
| `service_fee` | 50.00 |
| `tax` | 7.00 |
| `host_tax_rate` | 14.00 |
| `tax_from_host` | 630.00 |
| `total` | **5537.00** |

Same trip with `discount_in_percentage = 10` (no long-stay):

| Field | Value |
|-------|------:|
| `subtotal` | 4500.00 |
| `discount_amount` | 450.00 |
| `subtotal_after_discount` | 4050.00 |
| `cleaning_fee` | 250.00 |
| `access` | 100.00 |
| `service_fee` | 50.00 |
| `tax` | 7.00 |
| `tax_from_host` | 630.00 *(still on 4500)* |
| `total` | **5087.00** |
| `commission_amount` (check only) | still on post–long-stay base |
