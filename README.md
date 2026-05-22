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

| Key | Env Variable | Default | Description |
|-----|--------------|---------|-------------|
| `base_url` | `MAAT_URL` | `null` | Base URL of the Maat API host. |
| `client_id` | `MAAT_CLIENT_ID` | `null` | OAuth client identifier issued by Maat. |
| `client_secret` | `MAAT_CLIENT_SECRET` | `null` | OAuth client secret issued by Maat. |
| `timeout` | `MAAT_API_TIMEOUT` | `30` | HTTP request timeout (seconds). |

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
