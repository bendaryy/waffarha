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
| `start_date` | string (`Y-m-d`) | today | First day in the window (inclusive). |
| `end_date` | string (`Y-m-d`) | day 180 of the window | Last day (inclusive). Must be ≥ `start_date`. |

Both `start_date` and `end_date` are **inclusive** — a request with
`start_date = 2026-06-13` and `end_date = 2026-12-09` returns exactly 180
day rows.

The window is hard-capped at **180 days** per call (inclusive count) —
wider requests come back as HTTP 422, so partners must paginate the
calendar client-side by sending smaller `start_date` / `end_date` pairs.

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
  "same_day_booking": true,
  "base_minimum_stay": 2,
  "minimum_stay_overrides": [
    {
      "start_date": "2026-08-10",
      "end_date": "2026-08-20",
      "minimum_nights": 3,
      "base_minimum_stay": 2,
      "effective_minimum_nights": 3
    }
  ],
  "blocklist": ["2026-08-10", "2026-08-11"],
  "orphan_gaps": [
    {
      "start_date": "2026-04-27",
      "end_date": "2026-04-28",
      "gap_nights": 1,
      "base_minimum_stay": 2,
      "dynamic_minimum_nights": 1
    }
  ],
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
    { "date": "2026-08-01", "price": 1500.00, "currency": "EGP", "available": true,  "is_booked": false, "available_for_checkin": true,  "available_for_checkout": true,  "is_weekend": false, "reason": null },
    { "date": "2026-08-02", "price": 1800.00, "currency": "EGP", "available": true,  "is_booked": false, "available_for_checkin": true,  "available_for_checkout": true,  "is_weekend": true,  "reason": "weekend_rate" },
    { "date": "2026-08-03", "price": 1500.00, "currency": "EGP", "available": false, "is_booked": true,  "available_for_checkin": false, "available_for_checkout": false, "is_weekend": false, "reason": "booked" },
    { "date": "2026-08-04", "price": 2000.00, "currency": "EGP", "available": true,  "is_booked": false, "available_for_checkin": true,  "available_for_checkout": true,  "is_weekend": false, "reason": "linked_date" },
    { "date": "2026-08-05", "price": 2000.00, "currency": "EGP", "available": true,  "is_booked": false, "available_for_checkin": true,  "available_for_checkout": true,  "is_weekend": false, "reason": "linked_date" }
  ]
}
```

### Per-day flags

Each `calendar` entry carries three booleans the partner can wire straight
into a calendar UI:

| Field | True means | False means |
|-------|-----------|-------------|
| `is_booked` | The night is occupied — existing booking or host-blocked. | Free. |
| `available_for_checkin` | A NEW guest can begin a stay on this day. | Either booked, or another booking is checking in here, or the host has `same_day_booking = false` and an existing booking is checking out today. |
| `available_for_checkout` | A NEW guest can end a stay on this day. | Either booked, or another booking is checking out here. |

### Top-level fields

| Field | Type | Meaning |
|-------|------|---------|
| `same_day_booking` | `bool` | Whether the host allows a new guest to check in on the same day someone else is checking out. When `false`, `available_for_checkin` is forced to `false` on existing check-out days. |
| `base_minimum_stay` | `int` | Property default minimum nights. |
| `minimum_stay_overrides` | `MinimumStayOverride[]` | Date-ranged special minimums that raise the stay requirement when a trip overlaps the window. Each entry: `{start_date, end_date, minimum_nights, base_minimum_stay, effective_minimum_nights}`. |
| `blocklist` | `string[]` | Sorted unique list of host-blocked dates (Y-m-d). Already mirrored per-day on `is_booked`, but exposed here so partners can render block bars without iterating the whole calendar. |
| `orphan_gaps` | `OrphanGap[]` | Short bookable gaps between existing bookings/blocks that are smaller than the unit's minimum stay. Maat relaxes the minimum stay for these ranges so the calendar doesn't carry tiny unfillable holes. Each entry carries `{start_date, end_date, gap_nights, base_minimum_stay, dynamic_minimum_nights}`. |

### `reason` (UI hint)

`reason` is a hint for the UI — when more than one signal applies to the same
day, the higher-priority one wins (in the order below):

| Value | Meaning | `available` |
|-------|---------|-------------|
| `"booked"` | Existing non-cancelled booking that night. | `false` |
| `"blocked"` | Host has manually blocked this day. | `false` |
| `"linked_date"` | Day is inside an active minimum-stay rule — still individually available, but bookable **only** as part of a stay that satisfies the rule. Scan the top-level `linked_dates` list and pick the entry whose `start_date`..`end_date` covers this day. | `true` |
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

foreach ($calendar as $day) {
    if (! $day->available) {
        continue;
    }

    $note = '';
    if ($day->reason === 'linked_date') {
        foreach ($calendar->linkedDates as $rule) {
            if ($day->date >= $rule->startDate && $day->date <= $rule->endDate) {
                $note = ' ⚠ ' . ($rule->message ?? 'min-stay rule applies');
                break;
            }
        }
    }

    echo "{$day->date}: {$day->price} {$day->currency}{$note}\n";
}

echo "Window: {$calendar->startDate} → {$calendar->endDate} ({$calendar->totalDays} days)\n";

// Render block bars without iterating every day.
foreach ($calendar->blocklist as $blockedDate) {
    echo "Blocked: {$blockedDate}\n";
}

// Highlight short gaps Maat will accept with a relaxed minimum stay.
foreach ($calendar->orphanGaps as $gap) {
    echo "Orphan gap {$gap->startDate} → {$gap->endDate} "
        . "({$gap->gapNights} nights, min {$gap->dynamicMinimumNights})\n";
}

if ($calendar->sameDayBooking === false) {
    echo "Host does not allow same-day check-in.\n";
}
```
