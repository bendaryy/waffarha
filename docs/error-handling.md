# Error handling

All failures throw a typed exception extending
`Maat\Waffarha\Exceptions\WaffarhaException`. HTTP and connection failures are
also logged automatically (with sensitive data redacted and bodies truncated).

| Exception | When |
|-----------|------|
| `WaffarhaConfigurationException` | Required config (base URL / credentials) is missing. Thrown when the client is resolved. |
| `WaffarhaAuthenticationException` | An access token could not be obtained or refreshed. |
| `WaffarhaRequestException` | The API returned an error status, or the request could not be completed (connection error/timeout). Carries `->status` (int) and `->body` (?string). |

```php
use Maat\Waffarha\Facades\Waffarha;
use Maat\Waffarha\Exceptions\WaffarhaException;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

try {
    $units = Waffarha::units()->list();
} catch (WaffarhaRequestException $e) {
    // HTTP/transport failure — inspect the response
    report($e);
    logger()->warning('Waffarha request failed', [
        'status' => $e->status,
        'body' => $e->body,
    ]);
} catch (WaffarhaException $e) {
    // Any other SDK failure (config, auth)
    report($e);
}
```

Catch `WaffarhaException` to handle any SDK failure generically, or a specific
subclass to react to a particular failure mode.
