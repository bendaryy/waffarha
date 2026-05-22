# Waffarha Laravel Package

A Laravel package that provides a lightweight HTTP client and facade for integrating with the [Waffarha](https://waffarha.com) third-party API.

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
WAFFARHA_API_BASE_URL=https://api.waffarha.com/v1
WAFFARHA_CLIENT_ID=your-client-id
WAFFARHA_CLIENT_SECRET=your-client-secret
WAFFARHA_API_TIMEOUT=30
```

### Configuration Options

| Key | Env Variable | Default | Description |
|-----|--------------|---------|-------------|
| `base_url` | `WAFFARHA_API_BASE_URL` | `https://api.waffarha.com/v1` | Base URL for all API requests. |
| `client_id` | `WAFFARHA_CLIENT_ID` | `null` | Waffarha API client identifier. |
| `client_secret` | `WAFFARHA_CLIENT_SECRET` | `null` | Waffarha API client secret. |
| `timeout` | `WAFFARHA_API_TIMEOUT` | `30` | HTTP request timeout (seconds). |

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

| Method | Description |
|--------|-------------|
| `getUnits(array $queryParameters = [])` | Fetch a paginated list of syndicated units. |
| `getUnit(string $uuid)` | Retrieve a specific unit by UUID. |
| `request(string $method, string $endpoint, array $data = [])` | Send a raw HTTP request to any endpoint. |

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
