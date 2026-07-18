# Booking statuses

Booking status is the string on `Booking::$status` (JSON key `status`,
stored on Maat as `tbl_book.book_status`). Values are **case-sensitive**
TitleCase as listed below.

## Canonical values

| Status | Meaning | Typical for Waffarha |
|--------|---------|----------------------|
| `Confirmed` | Reservation accepted / active | **Default on create** — `bookings()->create()` always writes `Confirmed` |
| `Booked` | Legacy / pending-acceptance style status used elsewhere on Maat | Rare on the Waffarha channel; may appear if Maat staff change status |
| `Check_in` | Guest has checked in | Set by Maat ops / host flow (not by create) |
| `Completed` | Stay finished (checked out) | Terminal success state |
| `Cancelled` | Reservation cancelled | Terminal; blocks further updates |

### Legacy alias

| Value | Treat as |
|-------|----------|
| `Cancel` | Same as `Cancelled` (older rows). Overlap / cancel checks treat both as cancelled. Prefer `Cancelled` in new code. |

> Do **not** rely on a `Pending` status for Waffarha creates — the create
> endpoint persists `Confirmed` immediately. Older docs/examples that mention
> `Pending` are illustrative only.

---

## Lifecycle (Waffarha)

```text
create() ──► Confirmed ──► Check_in ──► Completed
                 │
                 └──► Cancelled
```

1. **`bookings()->create()`** → `status = "Confirmed"`.
2. **`bookings()->preview()`** → `status = null` (no row yet).
3. Stay progresses on Maat to `Check_in`, then `Completed`.
4. Cancel → `Cancelled` (and webhook `reservation.cancelled` when configured).

---

## Filtering list

[`bookings()->list()`](list-bookings.md) accepts a `status` query param — exact
match against the values above:

```php
Waffarha::bookings()->list(['status' => 'Confirmed']);
Waffarha::bookings()->list(['status' => 'Completed']);
Waffarha::bookings()->list(['status' => 'Cancelled']);
```

---

## Webhooks

Outbound events ([webhooks](webhooks.md)) carry `reservation.status` with the
same strings. Event names:

| Header `x-webhook-event` | When |
|--------------------------|------|
| `reservation.confirmed` | Booking created (or confirmed) |
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
    'Confirmed' => /* show upcoming */,
    'Check_in' => /* guest on property */,
    'Completed' => /* archive */,
    'Cancelled', 'Cancel' => /* cancelled UI */,
    'Booked' => /* treat as active / awaiting */,
    default => /* unknown — log and handle safely */,
};
```

Always keep a `default` branch — Maat may introduce additional statuses later.
