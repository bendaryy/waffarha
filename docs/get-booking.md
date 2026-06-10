# `bookings()->get()` — booking details

Retrieve a single booking by its Maat UUID.

```php
Waffarha::bookings()->get(string $uuid): Booking
```

- **HTTP:** `GET {base_url}/bookings/{uuid}`
- **Returns:** [`Booking`](data-objects.md#booking).
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$booking = Waffarha::bookings()->get('b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d');

echo $booking->status;
echo $booking->checkIn, ' → ', $booking->checkOut;
echo $booking->guest?->name;
```

## Response shape

> **Provisional.** The single-booking response shape is not yet confirmed. The
> [`Booking`](data-objects.md#booking) DTO reads each field from every observed
> candidate key (e.g. `uuid`/`id`, `property_uuid`/`property_id`,
> `guests_count`/`number_of_guests`) and keeps the full payload in
> `$booking->attributes`. See [webhooks](webhooks.md) for the closest documented
> per-booking payload, and run `composer test:live` to capture a real response.

See the full field table in [data objects](data-objects.md#booking).
