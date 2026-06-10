# Custom requests

For endpoints not covered by a helper method, use the generic `request()` method.
It returns the decoded JSON body as an `array<string, mixed>`.

```php
public function request(
    string $method,
    string $endpoint,
    array $data = [],   // JSON body — used for write verbs (POST/PUT/PATCH)
    array $query = [],  // query-string params — correctly encoded for every verb
): array
```

- Put **query-string** parameters in the fourth argument (`$query`). They are URL
  encoded correctly for every verb (including GET).
- Put a **JSON body** in the third argument (`$data`). It is sent for write verbs
  and ignored for `GET`/`HEAD`.
- The endpoint is appended to the configured `base_url`; do not include a leading
  host or the `/waffarha` prefix.

```php
use Maat\Waffarha\Facades\Waffarha;

// GET with query parameters
$response = Waffarha::request('GET', 'units', query: [
    'page' => 1,
    'per_page' => 20,
]);

// POST with a JSON body
$response = Waffarha::request('POST', 'some-endpoint', [
    'field' => 'value',
]);
```

Authentication, retries, logging and error translation behave exactly as they do
for the helper methods. See [error handling](error-handling.md).
