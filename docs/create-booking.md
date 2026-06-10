# `bookings()->create()` — create a booking

Create a booking on a Maat unit. Identify the source with
`'provider' => 'waffarha'`.

```php
Waffarha::bookings()->create(array $payload): Booking
```

- **HTTP:** `POST {base_url}/bookings`
- **Returns:** [`Booking`](data-objects.md#booking) — the created booking.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

## Payload

| Key | Type | Required | Description |
|-----|------|----------|-------------|
| `provider` | string | yes | Provider slug — `waffarha`. |
| `provider_booking_id` | string | yes | External reservation reference. |
| `property_uuid` | string | yes | Maat property UUID. |
| `check_in` / `check_out` | string | yes | Dates (`Y-m-d`). |
| `guests_count` | int | yes | Number of guests. |
| `total_amount` | number | yes | Booking total. |
| `currency` | string | no | 3-letter ISO currency code. |
| `notes` | string | no | Free-text notes. |
| `guest.name` | string | yes | Guest name. |
| `guest.email` / `phone` / `nationality` / `passport_number` / `date_of_birth` | string | no | Guest details. |

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$booking = Waffarha::bookings()->create([
    'provider' => 'waffarha',
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

> **Provisional response mapping.** The create response shape is not yet
> confirmed; the returned [`Booking`](data-objects.md#booking) keeps the full
> payload in `$booking->attributes`. See [webhooks](webhooks.md) and
> [data objects](data-objects.md#booking).

To update or cancel a booking afterwards, see
[`bookings()->update()` / `cancel()`](manage-booking.md).
