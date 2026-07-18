# Webhooks (outbound — Maat → Waffarha)

Maat can notify your application when a booking changes. This is **inbound to
your app** (Maat calls you); the SDK itself does not send or receive these
webhooks — this page documents the contract so you can verify and parse them.

If `webhook_url` (and optional `webhook_secret`) is configured on the Waffarha
provider row, Maat queues a `ProviderBookingWebhookJob` whenever a booking is
created, updated, or cancelled. The payload is sent as JSON with these headers:

| Header | Description |
|--------|-------------|
| `x-webhook-secret` | The provider's stored shared secret. **Verify before processing.** |
| `x-webhook-event` | One of `reservation.confirmed`, `reservation.updated`, `reservation.cancelled`. |

`reservation.status` uses the same strings as the REST API — see
[booking-statuses.md](booking-statuses.md). Partner-safe money fields:
[financials.md](financials.md).

## Payload shape

```json
{
  "event": "reservation.confirmed",
  "timestamp": "2026-08-10T19:42:13+02:00",
  "reservation": {
    "id": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d",
    "provider_booking_id": "WAF-123456",
    "property_id": "9b2a...-uuid",
    "property_title": "Beachfront Villa",
    "check_in": "2026-08-12",
    "check_out": "2026-08-15",
    "number_of_guests": 2,
    "total_amount": 4500.0,
    "currency": "EGP",
    "status": "Confirmed",
    "cancellation_reason": null,
    "notes": "Late arrival around 11 PM.",
    "guest": {
      "name": "Ahmed Mohamed",
      "email": "ahmed@example.com",
      "phone": "+201234567890",
      "nationality": "Egyptian",
      "passport_number": "A12345678",
      "date_of_birth": "1990-05-10"
    },
    "created_at": "2026-08-10T19:42:10+02:00",
    "updated_at": "2026-08-10T19:42:10+02:00"
  }
}
```

> **Note on the `Booking` DTO.** The `reservation` object above is currently the
> best-documented per-booking shape, so the provisional
> [`Booking`](data-objects.md#booking) mapping reads both these keys (`id`,
> `property_id`, `number_of_guests`, …) and the REST/create-payload variants
> (`uuid`, `property_uuid`, `guests_count`, …). Once a real REST response is
> captured (`composer test:live`), the mapping will be tightened.
