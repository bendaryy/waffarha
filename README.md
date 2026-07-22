# Waffarha Laravel Package

A Laravel package providing a typed HTTP client and facade for integrating with
the Maat API from an external application (e.g. Waffarha). It handles OAuth token
management automatically and returns typed DTOs instead of raw arrays.

## Requirements

- PHP `^8.2`
- Laravel `11.x` / `12.x` / `13.x`

## Installation

```bash
composer require maat/waffarha
```

The service provider and `Waffarha` facade are auto-discovered.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=waffarha-config
```

Set these in your `.env`:

```dotenv
MAAT_URL=https://your-maat-host.example.com/waffarha
MAAT_CLIENT_ID=your-client-id
MAAT_CLIENT_SECRET=your-client-secret
```

> `MAAT_URL` must include the API path prefix (e.g. `/waffarha`) — the SDK
> appends endpoint paths directly.

See [docs/configuration.md](docs/configuration.md) for all options. Authentication
(token fetch, caching, refresh) is fully automatic — see
[docs/authentication.md](docs/authentication.md).

## Quick start

```php
use Maat\Waffarha\Facades\Waffarha;

// List units (returns a typed UnitCollection of Unit objects)
$units = Waffarha::units()->list(['page' => 1, 'per_page' => 20]);

foreach ($units as $unit) {
    echo $unit->uuid, ' ', $unit->title, ' (', $unit->city, ')', PHP_EOL;
}

$total = $units->meta?->total;

// Fetch one unit's full details (returns a typed UnitDetail)
$detail = Waffarha::units()->get($unit->uuid);
echo $detail->property->title, ' — ', $detail->property->currency;
```

You can also resolve the client via dependency injection
(`WaffarhaClient $waffarha`) or the container (`app('waffarha')`).

## Documentation

| Doc | Contents |
|-----|----------|
| [Configuration](docs/configuration.md) | All config keys and env variables |
| [Authentication](docs/authentication.md) | How tokens are obtained/cached/refreshed; token endpoint reference |
| [`POST /oauth/token`](docs/authentication.md#token-endpoint) | Get / refresh an access token (`client_credentials` + `refresh_token` grants). The SDK calls this for you — direct use is for non-PHP partners |
| [`units()->list()`](docs/get-units.md) | List units — params, response, return type |
| [`units()->get()`](docs/get-unit.md) | Unit details — response shape, full field reference |
| [`units()->calendar()`](docs/unit-calendar.md) | Per-day pricing + availability calendar (180-day window, hard cap) |
| [`units()->checkAvailability()`](docs/check-availability.md) | Confirm a date range + price breakdown before creating a booking |
| [`cityFolders()->list()` / `units()`](docs/city-folders.md) | Browse city folders + search/filter Waffarha units inside a folder (always EGP) |
| [`bookings()->list()`](docs/list-bookings.md) | List bookings — filters, response, return type |
| [`bookings()->get()`](docs/get-booking.md) | Booking details by UUID |
| [`bookings()->preview()`](docs/booking-preview.md) | Booking-shaped quote before create (`POST /bookings/preview`) — always EGP |
| [`bookings()->create()`](docs/create-booking.md) | Create a booking — payload reference |
| [`bookings()->bookDetails()`](docs/book-details.md) | Guest receipt JSON (`POST /book_details`) — always EGP |
| [`payouts()->list()`](docs/payouts.md#list) | List per-booking payouts (returns `PayoutCollection`) |
| [`payouts()->get()`](docs/payouts.md#get) | Payout details by UUID (returns `Payout`) |
| [`payouts()->submitProof()`](docs/payouts.md#submitproof) | Upload the bank-transfer receipt for an open payout |
| [`whatsapp()->get()`](docs/whatsapp.md) | Maat support WhatsApp |
| [Financial fields](docs/financials.md) | All money fields (check / booking / receipt), formulas, discounts |
| [Booking statuses](docs/booking-statuses.md) | All `status` values and Waffarha lifecycle |
| [Webhooks](docs/webhooks.md) | Outbound booking webhooks (Maat → Waffarha) |
| [Custom requests](docs/custom-requests.md) | The generic `request()` escape hatch |
| [Data objects](docs/data-objects.md) | Field reference for every returned DTO |
| [Error handling](docs/error-handling.md) | Exception types and handling |
| [Testing](docs/testing.md) | Running the mocked and live test suites |

## Development

```bash
composer install
composer test       # mocked suite (no network) — run by CI
composer analyse    # PHPStan (level max)
composer format     # Laravel Pint
```

See [docs/testing.md](docs/testing.md) for the live integration suite.
