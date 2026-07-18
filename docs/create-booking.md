# `bookings()->create()` — create a booking

Create a booking on a Maat unit.

```php
Waffarha::bookings()->create(array $payload): Booking
```

- **HTTP:** `POST {base_url}/bookings`
- **Returns:** [`Booking`](data-objects.md#booking) — the created booking.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

> **Provider isolation.** Each provider integration has its own dedicated
> Maat OAuth client. The acting provider is resolved server-side from the
> client behind your access token (`providers.passport_client_id` on Maat),
> so **do not** send a `provider` field in the payload — a token issued for
> one provider can never create, list, update, or cancel bookings for
> another.

## Payload

| Key | Type | Required | Description |
|-----|------|----------|-------------|
| `provider_booking_id` | string | yes | External reservation reference (idempotency key, unique per provider). |
| `property_uuid` | string | yes | Maat property UUID. |
| `check_in` / `check_out` | string | yes | Dates (`Y-m-d`). |
| `guests_count` | int | yes | Number of guests. |
| `total_amount` | number | yes | Booking total in EGP — must equal the `financial.total` you got back from [`units()->checkAvailability()`](check-availability.md) (re-call `/check` with the same `discount_in_percentage` if you used one). Maat **recomputes** every financial field server-side using the same pipeline as `/check`; if your number differs from the server total by more than 1 EGP the request still succeeds but Maat persists the server number, logs the mismatch, and the response mirrors that. |
| `currency` | string | no | 3-letter ISO currency code (informational only; everything is stored in EGP). |
| `notes` | string | no | Free-text notes. |
| `discount_in_percentage` | number | no | Optional percentage discount, `0`–`100`. Applied to the nightly subtotal **before** `total` (cleaning fee / access / host tax are never discounted). Re-send the *same* percentage you sent to `/check` so the server total matches. |
| `guest.name` | string | yes | Guest name. |
| `guest.email` / `phone` / `nationality` / `passport_number` / `date_of_birth` | string | no | Guest details. |

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$booking = Waffarha::bookings()->create([
    'provider_booking_id' => 'WAF-123456',
    'property_uuid' => 'b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d',
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'guests_count' => 2,
    'total_amount' => 4500.00,
    'currency' => 'EGP',
    'notes' => 'Late arrival around 11 PM.',
    'guest' => [
        'name' => 'Ahmed Mohamed',
        'email' => 'ahmed@example.com',
        'phone' => '+201234567890',
        'nationality' => 'Egyptian',
        'passport_number' => 'A12345678',
        'date_of_birth' => '1990-05-10',
    ],
]);

echo $booking->uuid, ' ', $booking->status; // e.g. "Booked"
```

On create, `status` is typically **`Booked`** and may become **`Confirmed`**
shortly after. Full list: [booking-statuses.md](booking-statuses.md).
Money fields: [financials.md](financials.md).

## Response

A 201 returns the persisted booking with a partner-safe `financial` block:

```json
{
  "ResponseCode": "201",
  "Result": "true",
  "ResponseMsg": "Booking created successfully.",
  "booking": {
    "id": "9b3a1c6e-4d2f-4d1e-8a5b-2c8d8e9f0a1b",
    "provider": "Waffarha",
    "provider_booking_id": "WAF-123456",
    "status": "Booked",
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "total_days": 3,
    "guests_count": 2,
    "total_amount": 18840,
    "currency": "EGP",
    "financial": {
      "currency": "EGP",
      "subtotal": 17280,
      "discount_percentage": 10,
      "discount_amount": 1728,
      "subtotal_after_discount": 15552,
      "cleaning_fee": 1560,
      "access": 200,
      "host_tax_rate": 14,
      "tax_from_host": 2419.2,
      "total": 19731.2
    },
    "property": { "uuid": "...", "title": "...", "city": "..." },
    "guest": { "name": "Ahmed Mohamed", "...": "..." },
    "created_at": "2026-06-12 21:30:14",
    "updated_at": "2026-06-12 21:30:14"
  }
}
```

How `financial` is derived (same pipeline as `/check`):

- `subtotal` — sum of every night's `price` from the per-day breakdown
  (base price → SpecialRate → weekend uplift), in EGP.
- `discount_percentage` / `discount_amount` / `subtotal_after_discount` —
  only present when `discount_in_percentage` was sent.
  `discount_amount = subtotal × discount_percentage / 100`,
  `subtotal_after_discount = subtotal − discount_amount`. Cleaning fee is
  never discounted. The discount only reduces what the guest pays
  (`total`); commission / host payout stay on the **original `subtotal`**.
- `cleaning_fee` — one-time cleaning fee in EGP.
- `access` — one-time access fee in EGP (discount-free).
- `host_tax_rate` / `tax_from_host` — host property tax (% on the
  **original** `subtotal`). Added to guest `total`; commission-free.
- `total` — `subtotal_after_discount + cleaning_fee + access +
  tax_from_host` (or `subtotal + …` when no discount). This is the figure
  to send as `total_amount` on create; if it differs from your number Maat
  persists the server total. Commission is **not** added to `total`.

> Maat's commission breakdown (`commission_per_day`, `total_commission`,
> `net_amount`) is computed on the server for host payouts, but it is
> **not** exposed on the partner-facing API or webhooks — that's internal
> accounting.

The same `financial` block is mirrored on every webhook payload — see
[webhooks](webhooks.md) and [data objects](data-objects.md#booking).
