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

## Response — `200 OK`

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "That Date Range Available!",
  "available": true,
  "property_uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
  "check_in": "2026-08-12",
  "check_out": "2026-08-15",
  "nights": 3,
  "currency": "EGP",
  "subtotal": 4500.00,
  "breakdown": [
    { "date": "2026-08-12", "price": 1500.00, "is_weekend": false, "has_special_rate": false },
    { "date": "2026-08-13", "price": 1500.00, "is_weekend": false, "has_special_rate": false },
    { "date": "2026-08-14", "price": 1500.00, "is_weekend": true,  "has_special_rate": false }
  ]
}
```

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

    echo "Subtotal: {$check->subtotal} {$check->currency} over {$check->nights} nights\n";
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

> **Pricing parity.** The subtotal is computed using the same
> `base price → SpecialRate → weekend percentage` pipeline as the real
> booking flow, in EGP. It will match the `total_amount` you'd compute
> client-side from the [`calendar()`](unit-calendar.md) prices for the same
> nights.
