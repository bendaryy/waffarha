# Booking statuses

Booking status is the string on `Booking::$status` (JSON key `status`).
Values are **case-sensitive** TitleCase as listed below.

## Canonical values

| Status | Meaning |
|--------|---------|
| `Booked` | New reservation |
| `Confirmed` | Reservation accepted / active |
| `Check_in` | Guest has checked in |
| `Completed` | Stay finished (checked out) |
| `Cancelled` | Reservation cancelled |

### Legacy alias

| Value | Treat as |
|-------|----------|
| `Cancel` | Same as `Cancelled`. Prefer `Cancelled` in new code. |

---

## Lifecycle

```text
create() ──► Booked ──► Confirmed ──► Check_in ──► Completed
               │            │
               └────────────┴──► Cancelled
```

1. **`bookings()->create()`** → typically `"Booked"` (may become `"Confirmed"` shortly after).
2. **`bookings()->preview()`** → `status = null` (no row yet).
3. Stay progresses to `Check_in`, then `Completed`.
4. Cancel → `Cancelled` (and webhook `reservation.cancelled` when configured).

---

## Filtering list

[`bookings()->list()`](list-bookings.md) accepts a `status` query param — exact
match against the values above:

```php
Waffarha::bookings()->list(['status' => 'Confirmed']);
Waffarha::bookings()->list(['status' => 'Booked']);
Waffarha::bookings()->list(['status' => 'Completed']);
Waffarha::bookings()->list(['status' => 'Cancelled']);
```

---

## Webhooks

Outbound events ([webhooks](webhooks.md)) carry `reservation.status` with the
same strings. Event names:

| Header `x-webhook-event` | When |
|--------------------------|------|
| `reservation.confirmed` | Booking created |
| `reservation.updated` | Booking changed (dates, guest, status, …) without cancel |
| `reservation.cancelled` | Status became cancelled |

---

## Availability impact

Calendar / check availability treat a night as booked when there is a booking
whose status is **not** cancelled (`Cancelled` / `Cancel`). `Confirmed`,
`Booked`, `Check_in`, and `Completed` all block overlapping nights.

---

## SDK usage

```php
$booking = Waffarha::bookings()->get($uuid);

match ($booking->status) {
    'Booked' => /* new reservation */,
    'Confirmed' => /* active */,
    'Check_in' => /* guest on property */,
    'Completed' => /* archive */,
    'Cancelled', 'Cancel' => /* cancelled UI */,
    default => /* unknown — log and handle safely */,
};
```

Always keep a `default` branch — Maat may introduce additional statuses later.
