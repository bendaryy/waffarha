# `units()->checkAvailability()` — confirm a date range before booking

Verify that a specific check-in / check-out window is bookable on a Maat unit
and get back the per-night price breakdown the partner can show to the guest
before they pay.

```php
Waffarha::units()->checkAvailability(string $uuid, array $payload): AvailabilityCheck
```

- **HTTP:** `POST {base_url}/unit/{uuid}/check`
- **Returns:** [`AvailabilityCheck`](data-objects.md#availabilitycheck) when
  the range is bookable.
- **Throws:** `WaffarhaRequestException` with HTTP **409** when the range is
  unavailable (inspect `$exception->body['reason']`), or with the usual 4xx /
  5xx codes on validation / transport failure.

A `200` response means the same date range will pass the same checks when you
next call [`bookings()->create()`](create-booking.md) (modulo a small race
window during which somebody else might book the same dates).

## Payload

| Key | Type | Required | Description |
|-----|------|----------|-------------|
| `check_in` | string (`Y-m-d`) | yes | Must be today or later. |
| `check_out` | string (`Y-m-d`) | yes | Must be strictly after `check_in`. |
| `guests_count` | int | no | Optional sanity hint — not used for availability filtering today, but accepted for forward-compat. |
| `discount_in_percentage` | number | no | Optional percentage discount, `0`–`100`. When set the response's `financial` block also exposes `discount_percentage`, `discount_amount`, `subtotal_after_discount`, and `total` is reduced accordingly (cleaning fee is never discounted). Commission stays on the **original `subtotal`**. Re-send the same percentage to [`bookings()->create()`](create-booking.md). |

## Response — `200 OK`

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "That Date Range Available!",
  "available": true,
  "property_uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
  "booking_dates": {
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "total_days": 3,
    "normal_days": 1,
    "weekend_days": 2
  },
  "property": {
    "uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
    "title": "Catalina Updated",
    "image": "https://cdn.maat.example/uploads/catalina.jpg",
    "address": "2FXH+75M, New Cairo 1, Cairo Governorate",
    "city": "New Cairo",
    "beds": 1,
    "bathroom": 2
  },
  "financial": {
    "currency": "EGP",
    "subtotal": 4500.00,
    "discount_percentage": 10,
    "discount_amount": 450.00,
    "subtotal_after_discount": 4050.00,
    "cleaning_fee": 250.00,
    "access": 100.00,
    "host_tax_rate": 14.00,
    "tax_from_host": 630.00,
    "commission_percentage": 1.00,
    "commission_amount": 45.00,
    "total": 5030.00
  },
  "special_rates_applied": [
    {
      "id": 15739,
      "name": "Winter Promo",
      "start_date": "2026-08-12",
      "end_date": "2026-08-20",
      "nightly_price_override": 20,
      "effective_nightly_price": 1800.00,
      "base_price": 1500.00,
      "is_increase": true,
      "is_discount": false,
      "is_premium": true,
      "discount_percentage": null,
      "increase_percentage": 20
    }
  ],
  "breakdown": [
    {
      "date": "2026-08-12",
      "day_name_english": "Wednesday",
      "day_name_arabic": "الأربعاء",
      "is_weekend": false,
      "base_price": 1500.00,
      "price_after_special_rate": 1500.00,
      "price": 1500.00,
      "has_special_rate": false,
      "special_rate_id": null,
      "special_rate_name": null,
      "special_rate_percentage": null,
      "special_rate_is_increase": null,
      "is_discount": false,
      "is_premium": false,
      "discount_percentage": null,
      "increase_percentage": null,
      "weekend_percentage": null,
      "weekend_amount": null
    },
    {
      "date": "2026-08-14",
      "day_name_english": "Friday",
      "day_name_arabic": "الجمعة",
      "is_weekend": true,
      "base_price": 1500.00,
      "price_after_special_rate": 1800.00,
      "price": 1980.00,
      "has_special_rate": true,
      "special_rate_id": 15739,
      "special_rate_name": "Winter Promo",
      "special_rate_percentage": 20.00,
      "special_rate_is_increase": true,
      "is_discount": false,
      "is_premium": true,
      "discount_percentage": null,
      "increase_percentage": 20.00,
      "weekend_percentage": 10.00,
      "weekend_amount": 180.00
    }
  ]
}
```

`special_rates_applied` is a deduplicated summary of every host-configured
SpecialRate that affected at least one night in the window — perfect for
rendering a "Promos applied" panel without walking `breakdown` and
deduping by `special_rate_id` yourself. Amounts are in EGP; dollar
equivalents are intentionally not exposed.

Each `breakdown` row describes a single night and follows the same
`base_price → price_after_special_rate → + weekend_amount = price` pipeline
as Maat's internal booking flow. All amounts are in EGP (the Waffarha
surface does not expose the dollar equivalents the legacy receipt API
emits). Per-night commission is **not** included — commission is only
surfaced at the trip level under `financial.commission_amount`.

The `booking_dates` block echoes back the partner-supplied dates in
canonical `Y-m-d` form and surfaces the night counts up-front so partners
can render summaries without re-walking `breakdown`. `normal_days +
weekend_days` always equals `total_days`. Weekend = Thursday / Friday /
Saturday on properties that have a configured weekend percentage.

The `property` block carries just enough metadata for partners to render a
confirmation card for the guest without a second round-trip through
[`units()->show()`](unit-show.md). The primary identifier is always `uuid`
— Maat's internal numeric `id` is never exposed.

All money fields live under the `financial` block:

- `subtotal` is the sum of the per-night `price` values in `breakdown`.
- `cleaning_fee` is a **one-time** charge per booking (not per night),
  already converted to EGP. It is `0` when the host has not configured a
  cleaning fee for the unit.
- `access` is a **one-time** access fee (`tbl_property.access`, EGP).
- `host_tax_rate` / `tax_from_host` — host property tax on the **original**
  `subtotal` (Maat-coupon shape). Added to guest `total`; commission-free.
- `total` = `subtotal + cleaning_fee + access + tax_from_host` — this is
  the headline number the partner should display to the guest and is the
  figure the subsequent [`bookings()->create()`](create-booking.md) call
  expects.
- `commission_percentage` is Maat's platform commission rate (read from
  `tbl_setting.commission`, e.g. `1.00` means 1%).
- `commission_amount` is the calculated commission applied to `subtotal`
  (cleaning / access / tax_from_host are commission-free). Commission is
  reported separately and **not** added to `total`.

## Response — `409 Conflict` (unavailable)

```json
{
  "ResponseCode": "409",
  "Result": "false",
  "available": false,
  "reason": "booking_overlap",
  "ResponseMsg": "That Date Range Already Booked!"
}
```

Possible `reason` values:

| Value | Meaning |
|-------|---------|
| `"booking_overlap"` | An existing non-cancelled booking overlaps the window. |
| `"blocked"` | The host has manually blocked one or more days in the window. |
| `"linked_date_violation"` | The window conflicts with a linked-date / minimum-stay rule. Look at `violated_linked_dates` for the specific rule(s) — same shape as [`UnitCalendar::$linkedDates`](unit-calendar.md). |

## Example

```php
use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Facades\Waffarha;

try {
    $check = Waffarha::units()->checkAvailability(
        'b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d',
        [
            'check_in' => '2026-08-12',
            'check_out' => '2026-08-15',
            'guests_count' => 2,
        ],
    );

    echo "Total: {$check->total} {$check->currency} over {$check->nights} nights";
    echo " (subtotal {$check->subtotal} + cleaning fee {$check->cleaningFee})\n";
    echo "Maat commission: {$check->commissionPercentage}% = {$check->commissionAmount}\n";
    foreach ($check as $night) {
        echo "{$night->date}: {$night->price}\n";
    }
} catch (WaffarhaRequestException $e) {
    if ($e->status === 409) {
        echo "Unavailable: " . ($e->body['reason'] ?? 'unknown');
    } else {
        throw $e;
    }
}
```

> **Pricing parity.** The nightly `subtotal` is computed using the same
> `base price → SpecialRate → weekend percentage` pipeline as the real
> booking flow, in EGP — it will match the sum of the per-night prices you'd
> read from [`calendar()`](unit-calendar.md) for the same nights. The
> `cleaning_fee` / `access` mirror `tbl_property` (converted to EGP),
> `tax_from_host` uses `tbl_property.tax` on the original subtotal, and
> `total = subtotal + cleaning_fee + access + tax_from_host` is the figure
> to send as `total_amount` in [`bookings()->create()`](create-booking.md).
> `commission_*` mirrors `tbl_setting.commission` (applied to `subtotal`)
> and is reported separately — it is **not** part of guest `total`.
