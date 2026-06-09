# Waffarha Laravel Package

A Laravel package that provides a lightweight HTTP client and facade for integrating with the Maat API from any external application (e.g. Waffarha).

## Requirements

- PHP `^8.2`
- Laravel `10.x` / `11.x` / `12.x` / `13.x`

## Installation

Install via Composer:

```bash
composer require maat/waffarha
```

The package will be auto-discovered. Both the service provider and the `Waffarha` facade are registered automatically via Laravel package discovery.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=waffarha-config
```

This creates `config/waffarha.php`. Add the following variables to your `.env`:

```dotenv
MAAT_URL=https://your-maat-host.example.com
MAAT_CLIENT_ID=your-client-id
MAAT_CLIENT_SECRET=your-client-secret
MAAT_API_TIMEOUT=30
```

### Configuration Options

| Key             | Env Variable         | Default | Description                             |
| --------------- | -------------------- | ------- | --------------------------------------- |
| `base_url`      | `MAAT_URL`           | `null`  | Base URL of the Maat API host.          |
| `client_id`     | `MAAT_CLIENT_ID`     | `null`  | OAuth client identifier issued by Maat. |
| `client_secret` | `MAAT_CLIENT_SECRET` | `null`  | OAuth client secret issued by Maat.     |
| `timeout`       | `MAAT_API_TIMEOUT`   | `30`    | HTTP request timeout (seconds).         |

## Authentication

Maat exposes a Laravel Passport `client_credentials` endpoint for the Waffarha integration. Before calling any protected endpoint, obtain an access token from:

```http
POST {MAAT_URL}/waffarha/oauth/token
Content-Type: application/json
Accept: application/json

{
    "grant_type": "client_credentials",
    "client_id": "{MAAT_CLIENT_ID}",
    "client_secret": "{MAAT_CLIENT_SECRET}",
    "scope": "*"
}
```

### Successful Response

The Maat OAuth server uses a customised `client_credentials` grant that also issues a refresh token (default refresh TTL: **1 month**):

```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "def50200a8c4b2e7..."
}
```

### Using the Access Token

Send the returned `access_token` as a `Bearer` token on the `Authorization` header of every subsequent request:

```http
GET {MAAT_URL}/waffarha/units
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
Accept: application/json
```

### Refreshing the Access Token

When the access token expires, exchange the saved `refresh_token` for a new pair without re-sending the client secret elsewhere. Hit the same `/oauth/token` endpoint with `grant_type=refresh_token`:

```http
POST {MAAT_URL}/waffarha/oauth/token
Content-Type: application/json
Accept: application/json

{
    "grant_type": "refresh_token",
    "refresh_token": "def50200a8c4b2e7...",
    "client_id": "{MAAT_CLIENT_ID}",
    "client_secret": "{MAAT_CLIENT_SECRET}",
    "scope": "*"
}
```

The response shape is identical to the initial token request — a new `access_token` **and** a fresh `refresh_token` (the old refresh token is invalidated).

### Examples with cURL

Obtain initial token:

```bash
curl -X POST "$MAAT_URL/waffarha/oauth/token" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "'"$MAAT_CLIENT_ID"'",
    "client_secret": "'"$MAAT_CLIENT_SECRET"'",
    "scope": "*"
  }'
```

Refresh an existing token:

```bash
curl -X POST "$MAAT_URL/waffarha/oauth/token" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "refresh_token",
    "refresh_token": "'"$MAAT_REFRESH_TOKEN"'",
    "client_id": "'"$MAAT_CLIENT_ID"'",
    "client_secret": "'"$MAAT_CLIENT_SECRET"'",
    "scope": "*"
  }'
```

## Usage

### Using the Facade

```php
use Maat\Waffarha\Facades\Waffarha;

$units = Waffarha::getUnits(['page' => 1, 'per_page' => 20]);

$unit = Waffarha::getUnit('unit-uuid-here');
```

### Using Dependency Injection

```php
use Maat\Waffarha\WaffarhaClient;

class SyncUnitsJob
{
    public function __construct(protected WaffarhaClient $waffarha) {}

    public function handle(): void
    {
        $units = $this->waffarha->getUnits();

        // ...
    }
}
```

### Using the Service Container

```php
$client = app('waffarha');
$units = $client->getUnits();
```

### Custom Requests

For endpoints not covered by helper methods, use the generic `request()` method:

```php
use Maat\Waffarha\Facades\Waffarha;

$response = Waffarha::request('GET', 'units', [
    'page' => 1,
    'per_page' => 20,
]);
```

## Available Methods

| Method                                                        | Description                                                                              |
| ------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `getUnits(array $queryParameters = [])`                       | Fetch a paginated list of syndicated units.                                              |
| `getUnit(string $uuid)`                                       | Retrieve a specific unit by UUID.                                                        |
| `listBookings(array $queryParameters = [])`                   | List Waffarha bookings (filter by `status`, `check_in_from`, `check_in_to`).             |
| `createBooking(array $payload)`                               | Create a Waffarha booking on a Maat unit.                                                |
| `getBooking(string $uuid)`                                    | Retrieve a previously created Waffarha booking by Maat UUID.                             |
| `updateBooking(string $uuid, array $payload)`                 | Update a Waffarha booking (status, dates, guests, total, guest details).                 |
| `cancelBooking(string $uuid, ?string $reason = null)`         | Cancel a Waffarha booking.                                                               |
| `request(string $method, string $endpoint, array $data = [])` | Send a raw HTTP request to any endpoint.                                                 |

## Bookings

Waffarha pushes reservations against Maat units through the booking endpoints. Identify the source with `'provider' => 'waffarha'` in every create call.

### Create a booking

```php
use Maat\Waffarha\Facades\Waffarha;

$response = Waffarha::createBooking([
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
```

### Update / cancel

```php
Waffarha::updateBooking($uuid, [
    'status' => 'CheckIn',
    'notes' => 'Guest already checked in.',
]);

Waffarha::cancelBooking($uuid, 'Guest no-show');
```

### Webhooks (outbound — Maat → Waffarha)

If `webhook_url` (and optional `webhook_secret`) is configured on the Waffarha provider row, Maat will queue a `ProviderBookingWebhookJob` whenever a Waffarha booking is created, updated, or cancelled. The payload is sent as JSON with the headers:

| Header             | Description                                                                     |
| ------------------ | ------------------------------------------------------------------------------- |
| `x-webhook-secret` | The provider's stored shared secret. Verify before processing.                  |
| `x-webhook-event`  | One of `reservation.confirmed`, `reservation.updated`, `reservation.cancelled`. |

Payload shape:

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

## Error Handling

All API failures throw an `Exception` with a descriptive message and are logged automatically via Laravel's logger:

```php
use Maat\Waffarha\Facades\Waffarha;

try {
    $units = Waffarha::getUnits();
} catch (\Exception $e) {
    report($e);
}
```

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
