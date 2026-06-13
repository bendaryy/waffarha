# Waffarha SDK documentation

Detailed reference for the `maat/waffarha` package. For installation and a quick
start, see the [project README](../README.md).

## Contents

- [Configuration](configuration.md) — all config keys and environment variables.
- [Authentication](authentication.md) — automatic token handling and the token
  endpoint reference.

### API calls

- [`units()->list()`](get-units.md) — list units (returns `UnitCollection`).
- [`units()->get()`](get-unit.md) — unit details (returns `UnitDetail`).
- [`units()->calendar()`](unit-calendar.md) — per-day pricing + availability (returns `UnitCalendar`).
- [`units()->checkAvailability()`](check-availability.md) — confirm a date range + price breakdown (returns `AvailabilityCheck`).
- [`bookings()->list()`](list-bookings.md) — list bookings (returns `BookingCollection`).
- [`bookings()->get()`](get-booking.md) — booking details (returns `Booking`).
- [`bookings()->create()`](create-booking.md) — create a booking (returns `Booking`).
- [`payouts()->list()`](payouts.md#list) — list per-booking payouts (returns `PayoutCollection`).
- [`payouts()->get()`](payouts.md#get) — payout details by UUID (returns `Payout`).
- [`payouts()->submitProof()`](payouts.md#submitproof) — upload the bank-transfer receipt (returns `Payout`).
- [Custom requests](custom-requests.md) — the generic `request()` escape hatch.

### Reference

- [Data objects](data-objects.md) — field reference for every returned DTO.
- [Webhooks](webhooks.md) — outbound booking webhooks (Maat → Waffarha).
- [Error handling](error-handling.md) — exception types and handling.
- [Testing](testing.md) — running the mocked and live suites.
