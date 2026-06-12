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
| `total_amount` | number | yes | Booking total in EGP — must equal the `financial.total` you got back from [`units()->checkAvailability()`](check-availability.md). Maat **recomputes** every financial field server-side using the same pipeline as `/check`; if your number differs from the server total by more than 1 EGP the request still succeeds but Maat persists the server number, logs the mismatch, and the response mirrors that. |
| `currency` | string | no | 3-letter ISO currency code (informational only; everything is stored in EGP). |
| `notes` | string | no | Free-text notes. |
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

echo $booking->uuid, ' ', $booking->status;
```

## Response

A 201 returns the persisted booking with a `financial` block that mirrors
every column Maat wrote to `tbl_book` for the reservation:

```json
{
  "ResponseCode": "201",
  "Result": "true",
  "ResponseMsg": "Booking created successfully.",
  "booking": {
    "id": "9b3a1c6e-4d2f-4d1e-8a5b-2c8d8e9f0a1b",
    "provider": { "id": 1, "name": "Waffarha" },
    "provider_booking_id": "WAF-123456",
    "status": "Confirmed",
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "total_days": 3,
    "guests_count": 2,
    "total_amount": 18840,
    "currency": "EGP",
    "financial": {
      "currency": "EGP",
      "subtotal": 17280,
      "cleaning_fee": 1560,
      "total": 18840,
      "commission_per_day": 921.6,
      "total_commission": 2764.8,
      "net_amount": 16075.2
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
- `cleaning_fee` — `tbl_property.cleaning_fee` converted to EGP, charged
  once per booking (commission-free).
- `total` — `subtotal + cleaning_fee` — what the partner is billed.
  Commission is **not** added, same convention as `/v1/u_simulate_booking`.
- `commission_per_day` — `total_commission / total_days`, persisted on
  `tbl_book.commission` so legacy host reports keep working.
- `total_commission` — `subtotal × commission_percentage / 100`. The
  percentage is pulled from `tbl_setting.commission` at booking time.
- `net_amount` — `subtotal − total_commission + cleaning_fee` — what Maat
  owes the host (host keeps the cleaning fee in full).

The same `financial` block is mirrored on every webhook payload — see
[webhooks](webhooks.md) and [data objects](data-objects.md#booking).
