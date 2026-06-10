# `bookings()->update()` / `cancel()` — manage a booking

Update or cancel an existing booking by its Maat UUID.

## Update

```php
Waffarha::bookings()->update(string $uuid, array $payload): Booking
```

- **HTTP:** `PUT {base_url}/bookings/{uuid}`
- **Returns:** [`Booking`](data-objects.md#booking) — the updated booking.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

The payload accepts any mutable subset of the booking (status, dates, guests
count, total, notes, guest details).

```php
use Maat\Waffarha\Facades\Waffarha;

Waffarha::bookings()->update($uuid, [
    'status' => 'CheckIn',
    'notes' => 'Guest already checked in.',
]);
```

## Cancel

```php
Waffarha::bookings()->cancel(string $uuid, ?string $reason = null): Booking
```

- **HTTP:** `DELETE {base_url}/bookings/{uuid}`
- **Returns:** [`Booking`](data-objects.md#booking). When the API returns an
  empty body, the DTO's fields are simply `null`.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

When a `$reason` is given it is sent in the request body as `{"reason": "..."}`.

```php
use Maat\Waffarha\Facades\Waffarha;

Waffarha::bookings()->cancel($uuid, 'Guest no-show');
```

> **Provisional response mapping.** The update/cancel response shapes are not yet
> confirmed; the returned [`Booking`](data-objects.md#booking) keeps the full
> payload in `$booking->attributes`. See [data objects](data-objects.md#booking).
