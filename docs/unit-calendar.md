# `units()->calendar()` — per-day pricing + availability

Fetch a day-by-day calendar for a single unit so partners can render a date
picker that shows which nights are bookable and at what price.

```php
Waffarha::units()->calendar(string $uuid, array $query = []): UnitCalendar
```

- **HTTP:** `GET {base_url}/unit/{uuid}/calendar`
- **Returns:** [`UnitCalendar`](data-objects.md#unitcalendar) — iterable list
  of [`UnitCalendarDay`](data-objects.md#unitcalendarday) entries.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport
  failure (e.g. unknown UUID → 404, window too wide → 422).

> **Always EGP.** Prices are returned in EGP, converted server-side from the
> property's base currency. The same `base price → SpecialRate → weekend
> percentage` pipeline used by the real booking flow is applied, so calendar
> prices line up with what [`bookings()->create()`](create-booking.md) will
> actually charge.

## Query parameters

| Key | Type | Default | Notes |
|-----|------|---------|-------|
| `start_date` | string (`Y-m-d`) | today | First day in the window. |
| `end_date` | string (`Y-m-d`) | `start_date + 60 days` | Last day (inclusive). Must be ≥ `start_date`. |

The window is hard-capped at **365 days** per call — wider requests come back
as HTTP 422, so partners must paginate the calendar client-side.

## Response shape

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "Calendar retrieved successfully.",
  "property_uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
  "currency": "EGP",
  "base_price": 1500.00,
  "window": {
    "start_date": "2026-08-01",
    "end_date": "2026-08-05",
    "days": 5
  },
  "linked_dates": [
    {
      "id": 42,
      "name": "Eid Al-Adha",
      "start_date": "2026-08-04",
      "end_date": "2026-08-05",
      "required_nights": 2,
      "message": "Must book the whole Eid Al-Adha holiday."
    }
  ],
  "calendar": [
    { "date": "2026-08-01", "price": 1500.00, "currency": "EGP", "available": true,  "is_weekend": false, "linked_date_id": null, "reason": null },
    { "date": "2026-08-02", "price": 1800.00, "currency": "EGP", "available": true,  "is_weekend": true,  "linked_date_id": null, "reason": "weekend_rate" },
    { "date": "2026-08-03", "price": 1500.00, "currency": "EGP", "available": false, "is_weekend": false, "linked_date_id": null, "reason": "booked" },
    { "date": "2026-08-04", "price": 2000.00, "currency": "EGP", "available": true,  "is_weekend": false, "linked_date_id": 42,   "reason": "linked_date" },
    { "date": "2026-08-05", "price": 2000.00, "currency": "EGP", "available": true,  "is_weekend": false, "linked_date_id": 42,   "reason": "linked_date" }
  ]
}
```

`reason` is a hint for the UI — when more than one signal applies to the same
day, the higher-priority one wins (in the order below):

| Value | Meaning | `available` |
|-------|---------|-------------|
| `"booked"` | Existing non-cancelled booking that night. | `false` |
| `"blocked"` | Host has manually blocked this day (`tbl_blocked_dates` / `property_availability_blocks`). | `false` |
| `"linked_date"` | Day is inside an active minimum-stay rule — still individually available, but bookable **only** as part of a stay that satisfies the rule (look up `linked_date_id` in `linked_dates`). | `true` |
| `"special_rate"` | Available; `price` reflects an active `SpecialRate` window. | `true` |
| `"weekend_rate"` | Available; `price` reflects the property's weekend percentage. | `true` |
| `null` | Regular available day at base price. | `true` |

> **Linked dates vs. booked/blocked.** A linked-date day is *available* on the
> calendar but a `POST /waffarha/bookings` call that doesn't satisfy the rule
> will be rejected with HTTP 409
> (`reason: "linked_date_violation"` — see
> [`check-availability.md`](check-availability.md)). Surface a small warning
> badge / tooltip on these days using the `message` from `linked_dates`.

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$calendar = Waffarha::units()->calendar('b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d', [
    'start_date' => '2026-08-01',
    'end_date' => '2026-08-31',
]);

// Quick lookup table for linked-date warnings.
$linkedDatesById = [];
foreach ($calendar->linkedDates as $rule) {
    $linkedDatesById[$rule->id] = $rule;
}

foreach ($calendar as $day) {
    if (! $day->available) {
        continue;
    }

    $note = $day->linkedDateId !== null
        ? ' ⚠ ' . ($linkedDatesById[$day->linkedDateId]->message ?? 'min-stay rule applies')
        : '';

    echo "{$day->date}: {$day->price} {$day->currency}{$note}\n";
}

echo "Window: {$calendar->startDate} → {$calendar->endDate} ({$calendar->totalDays} days)\n";
```
