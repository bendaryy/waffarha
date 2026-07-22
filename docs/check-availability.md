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
  "is_xuru_unit": false,
  "xuru_status": null,
  "xuru_price_applied": false,
  "effective_minimum_stay": 1,
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
    "bathroom": 2,
    "minimum_days": 1
  },
  "financial": {
    "currency": "EGP",
    "base_price": 1500.00,
    "subtotal": 4500.00,
    "long_stay_discount": 0,
    "long_stay_applied": false,
    "discount_percentage": 10,
    "discount_amount": 450.00,
    "subtotal_after_discount": 4050.00,
    "cleaning_fee": 250.00,
    "access": 100.00,
    "service_fee": 50.00,
    "tax_rate": 14.00,
    "tax": 7.00,
    "host_tax_rate": 14.00,
    "tax_from_host": 630.00,
    "commission_percentage": 1.00,
    "commission_amount": 45.00,
    "total": 5087.00
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
as Maat's booking pricing pipeline. All amounts are in EGP. Per-night
commission is **not** included — commission is only surfaced at the trip
level under `financial.commission_amount`.

The `booking_dates` block echoes back the partner-supplied dates in
canonical `Y-m-d` form and surfaces the night counts up-front so partners
can render summaries without re-walking `breakdown`. `normal_days +
weekend_days` always equals `total_days`. Weekend = Thursday / Friday /
Saturday on properties that have a configured weekend percentage.

The `property` block carries just enough metadata for partners to render a
confirmation card for the guest without a second round-trip through
[`units()->show()`](unit-show.md). The primary identifier is always `uuid`
— Maat's internal numeric `id` is never exposed.

All money fields live under the `financial` block. Full matrix:
**[financials.md](financials.md)**.

- `subtotal` is the sum of the per-night `price` values in `breakdown`.
- `long_stay_discount` / `long_stay_applied` — automatic when a long-stay
  rule matches the trip; applied **before** any partner percentage.
- `cleaning_fee` / `access` — **one-time** fees per booking (EGP).
- `service_fee` / `tax_rate` / `tax` — platform service fee and tax on that
  fee.
- `host_tax_rate` / `tax_from_host` — host property tax on the **original**
  `subtotal`.
- `total` =
  `subtotal_after_discount + cleaning_fee + access + tax_from_host + service_fee + tax`
  — send this as `total_amount` on [`bookings()->create()`](create-booking.md).
- `commission_percentage` / `commission_amount` — reconcile only; **not**
  added to guest `total`.

Top-level (outside `financial`):

- `effective_minimum_stay` — nights required for this window (base /
  special override / channel min, whichever is highest).
- `is_xuru_unit` / `xuru_status` / `xuru_price_applied` — channel-manager
  unit hints; when `xuru_price_applied` is true, nightly prices came from
  the channel override instead of the local SpecialRate pipeline.

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
| `"minimum_stay"` | Trip is shorter than `effective_minimum_stay`. Body may include `required_nights`, `requested_nights`, `base_minimum_stay`. |
| `"linked_date_violation"` | The window conflicts with a linked-date rule. Look at `violated_linked_dates` — same shape as [`UnitCalendar::$linkedDates`](unit-calendar.md). |
| `"invalid_dates"` | Check-out is not after check-in. |

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

> **Pricing parity.** Nightly `subtotal` uses the same
> `base price → SpecialRate → weekend percentage` pipeline as create/preview
> (or a channel price override when `xuru_price_applied` is true). Long-stay,
> partner %, cleaning / access, host tax, service fee, and tax on the service
> fee are included in `financial.total` — that figure is what
> [`bookings()->create()`](create-booking.md) expects as `total_amount`.
> `commission_*` is informational only.
