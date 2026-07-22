# `bookings()->bookDetails()` — guest receipt (JSON)

Fetch the guest-facing receipt for a booking: a `bookdetails` object
(day breakdown + financial summary). Always in EGP. No public HTML URL.

Separate from [`bookings()->preview()`](booking-preview.md), which returns
a booking-shaped quote (not this receipt).

```php
Waffarha::bookings()->bookDetails(string $bookingUuid): GuestBookDetails
```

- **HTTP:** `POST {base_url}/book_details`
- **Body:** `{ "booking_uuid": "<maat-booking-uuid>" }`
- **Returns:** [`GuestBookDetails`](data-objects.md#guestbookdetails)
- **Throws:** `WaffarhaRequestException` on 404 / 4xx / 5xx

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$receipt = Waffarha::bookings()->bookDetails('9b3a1c6e-4d2f-4d1e-8a5b-2c8d8e9f0a1b');

echo $receipt->total;          // guest total in EGP
echo $receipt->access;         // access fee
echo $receipt->taxFromHost;    // host property tax
echo $receipt->get('guest_name');
```

## Response

```json
{
  "bookdetails": {
    "currency": "EGP",
    "uuid": "9b3a1c6e-4d2f-4d1e-8a5b-2c8d8e9f0a1b",
    "title": "Catalina Updated",
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "total_day": 3,
    "guest_name": "Ahmed Mohamed",
    "subtotal": 4500.00,
    "long_stay_discount": 0,
    "long_stay_applied": false,
    "cleaning_fee": 250.00,
    "access": 100.00,
    "service_fee": 50.00,
    "tax": 7.00,
    "host_tax_rate": 14.00,
    "tax_from_host": 630.00,
    "total": 5537.00,
    "day_breakdown": [ /* … */ ],
    "financial_summary": {
      "currency": "EGP",
      "subtotal": 4500.00,
      "cleaning_fee": 250.00,
      "access": 100.00,
      "service_fee_amount": 50.00,
      "tax_amount": 7.00,
      "host_tax_rate": 14.00,
      "tax_from_host": 630.00,
      "total_amount": 5537.00
    }
  },
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "Book Property Details Founded!"
}
```

> Commission / `net_amount` are **not** included — this is the guest receipt,
> not the host payout view.
