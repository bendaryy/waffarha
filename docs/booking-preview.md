# `bookings()->preview()` — booking-shaped quote before create

Preview a booking quote **before** calling [`create()`](create-booking.md).
Returns the same `booking` envelope as store/show (always EGP) — **not** the
receipt [`bookDetails()`](book-details.md) `bookdetails` shape.

```php
Waffarha::bookings()->preview(array $payload): Booking
```

- **HTTP:** `POST {base_url}/bookings/preview`
- **Returns:** [`Booking`](data-objects.md#booking) (same DTO as create/get)

## Payload

Same fields as create, minus `total_amount` (Maat computes it):

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `property_uuid` | uuid | yes | Maat property UUID |
| `check_in` / `check_out` | `Y-m-d` | yes | Check-out after check-in |
| `guests_count` | int | yes | ≥ 1 |
| `guest.name` | string | yes | Guest display name |
| `guest.email` / `phone` / … | string | no | Optional guest fields |
| `discount_in_percentage` | number | no | 0–100 Waffarha discount |
| `provider_booking_id` | string | no | Echoed on the preview |
| `notes` | string | no | |

## Example

```php
$preview = Waffarha::bookings()->preview([
    'property_uuid' => 'b6d0b8d2-…',
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'guests_count' => 2,
    'guest' => ['name' => 'Ahmed Mohamed'],
    'discount_in_percentage' => 10,
]);

echo $preview->totalAmount;                 // guest total in EGP
echo $preview->financial?->access;          // access fee
echo $preview->financial?->taxFromHost;     // host property tax
echo $preview->guest?->name;

// Night breakdown is on the raw payload (preview-only extra):
foreach ($preview->get('breakdown', []) as $night) {
    // …
}

// Then create with the same dates + discount and total_amount = preview total
Waffarha::bookings()->create([
    'provider_booking_id' => 'WAF-123',
    'property_uuid' => 'b6d0b8d2-…',
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'guests_count' => 2,
    'total_amount' => (float) $preview->totalAmount,
    'guest' => ['name' => 'Ahmed Mohamed'],
    'discount_in_percentage' => 10,
]);
```

## Response envelope

Same as create/show:

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "Booking preview computed successfully.",
  "booking": {
    "id": null,
    "provider": "Waffarha",
    "status": null,
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "total_days": 3,
    "guests_count": 2,
    "total_amount": 5537,
    "currency": "EGP",
    "financial": {
      "currency": "EGP",
      "subtotal": 4500,
      "cleaning_fee": 250,
      "access": 100,
      "service_fee": 50,
      "tax_rate": 14,
      "tax": 7,
      "host_tax_rate": 14,
      "tax_from_host": 630,
      "total": 5537
    },
    "property": { "uuid": "…", "title": "…", "city": "…" },
    "guest": { "name": "Ahmed Mohamed" },
    "breakdown": [ { "date": "2026-08-12", "price": 1500 } ],
    "special_rates_applied": []
  }
}
```

For the guest **receipt** after booking (`bookdetails`), use
[`bookDetails()`](book-details.md).

Money fields reference: [financials.md](financials.md). Status on preview is
always `null` (no booking yet) — see [booking-statuses.md](booking-statuses.md).
