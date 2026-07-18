# `bookings()->list()` — list bookings

List provider bookings on Maat.

```php
Waffarha::bookings()->list(array $query = []): BookingCollection
```

- **HTTP:** `GET {base_url}/bookings`
- **Returns:** [`BookingCollection`](data-objects.md#bookingcollection) — an
  iterable, countable collection of [`Booking`](data-objects.md#booking) objects.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

## Parameters

`$query` is sent as the query string. Observed filters:

| Param | Type | Description |
|-------|------|-------------|
| `status` | string | Filter by booking status — exact match. See [booking-statuses.md](booking-statuses.md) (`Confirmed`, `Check_in`, `Completed`, `Cancelled`, …). |
| `check_in_from` | string | Earliest check-in date (`Y-m-d`). |
| `check_in_to` | string | Latest check-in date (`Y-m-d`). |
| `page` / `per_page` | int | Pagination. |

Unknown parameters are passed through as-is.

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$bookings = Waffarha::bookings()->list([
    'status' => 'Confirmed',
    'check_in_from' => '2026-08-01',
    'check_in_to' => '2026-08-31',
]);

echo count($bookings);          // bookings on this page
echo $bookings->meta?->total;   // total across all pages

foreach ($bookings as $booking) {
    echo $booking->uuid, ' ', $booking->status, ' — ', $booking->guest?->name, PHP_EOL;
}
```

## Response shape

The list envelope below is **confirmed** against the live API (`bookings`
wrapper + `pagination`, enveloped like the units endpoint). The **per-booking
fields** are still provisional — the live account had zero bookings, so a single
row could not be sampled. See [webhooks](webhooks.md) for the best current
evidence of the per-booking shape, and re-run `composer test:live` against an
account with bookings to confirm the row keys.

```json
{
    "ResponseCode": "200",
    "Result": "true",
    "ResponseMsg": "Provider bookings retrieved successfully.",
    "bookings": [
        {
            "uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
            "provider_booking_id": "WAF-123456",
            "status": "Confirmed",
            "check_in": "2026-08-12",
            "check_out": "2026-08-15",
            "guests_count": 2,
            "total_amount": "4500.00",
            "currency": "EGP",
            "guest": { "name": "Ahmed Mohamed", "email": "ahmed@example.com" }
        }
    ],
    "pagination": {
        "current_page": 1, "last_page": 1, "per_page": 5,
        "total": 1, "next_page_url": null, "prev_page_url": null
    }
}
```

> The row object above is illustrative (provisional); only the envelope,
> `bookings` wrapper, and `pagination` block are confirmed.

The `bookings` array maps to `Booking` objects and `pagination` to a
`PaginationMeta`. Each `Booking` keeps the full payload, so non-promoted fields
remain reachable via `$booking->get('key')`. See the full field tables in
[data objects](data-objects.md#booking).
