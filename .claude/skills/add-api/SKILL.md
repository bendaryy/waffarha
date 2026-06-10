---
name: add-api
description: Add a new Maat API resource (endpoint group) to the Waffarha SDK, following the package's resource + typed-DTO conventions. Use when exposing a new endpoint or API group (e.g. bookings, reviews, favourites) — it walks through the resource class, DTOs, client accessor, facade, container binding, tests, and docs.
---

# Add a new API to the Waffarha SDK

This package wraps the Maat API. Endpoints are grouped into **resource classes**
behind accessors on `WaffarhaClient`, return **typed readonly DTOs**, and all
wire concerns (auth, retries, logging, errors) live in one `Transport`. Follow
the existing `units` implementation as the canonical example.

## Architecture (read these first)

- `src/Http/Transport.php` — the only thing that talks HTTP. Resources call
  `$this->transport->send($method, $endpoint, $data = [], $query = [])`, which
  returns the decoded JSON body as `array<string, mixed>` (or throws
  `WaffarhaRequestException`). Auth/token/retry/401-refresh are automatic.
- `src/Resources/Resource.php` — abstract base; holds `protected readonly Transport $transport`.
- `src/Resources/Units.php` — example resource (`list()`, `get()`).
- `src/Data/*` — example DTOs (`Unit`, `UnitCollection`, `UnitDetail`, `PropertyDetails`, …).
- `src/WaffarhaClient.php` — memoized accessors (`units()`) + raw `request()`.
- `src/Facades/Waffarha.php` — `@method` lines for IDE/static analysis.
- `src/WaffarhaServiceProvider.php` — container bindings.

## Steps

For a new API called `<Name>` (singular PascalCase, e.g. `Bookings`), with
accessor `<name>()` (e.g. `bookings()`):

### 1. DTOs — `src/Data/`
One `final readonly` class per response object. Conventions (copy from
`src/Data/Unit.php`, `PropertyDetails.php`, `UnitCollection.php`):

- `declare(strict_types=1);`, namespace `Maat\Waffarha\Data`.
- Promote the useful fields as typed, **nullable** constructor-promoted properties.
- Add a static `fromArray(array $data): self` factory. Guard every read with
  `is_scalar()` before casting; never assume a key exists.
- **Monetary/count fields come back as numeric strings** (e.g. `"1000"`) — keep
  them as `?string`. Only use `?int`/`?bool` where the API genuinely sends them.
- Map nested objects/lists to their own DTOs (see `UnitDetail::mapList()`).
- Keep the full payload in `public array $attributes` and expose
  `get(string $key, mixed $default = null)` so non-promoted fields aren't lost.
- Mirror the API's key names in `@phpstan-type` docblocks, including any typos
  (the real API misspells `propetydetails` / `longtitude` — map them but expose
  correctly-named properties).

Collection DTOs implement `Countable, IteratorAggregate` (see `UnitCollection`).

### 2. Resource — `src/Resources/<Name>.php`
```php
<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\SomeDto;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

final class <Name> extends Resource
{
    /**
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): SomeCollection
    {
        return SomeCollection::fromArray(
            $this->transport->send('GET', '<endpoint>', query: $query)
        );
    }

    /** @throws WaffarhaRequestException */
    public function get(string $id): SomeDto
    {
        return SomeDto::fromArray(
            $this->transport->send('GET', "<endpoint>/{$id}")
        );
    }
}
```
Rules:
- Query-string params go in `query:`; a JSON body (write verbs) goes in the
  third `$data` arg. GET/HEAD ignore `$data`.
- The endpoint is appended to `base_url` — no leading slash, no host, no
  `/waffarha` prefix.
- **Confirm the real path and response shape** against the API before mapping
  (the units list is `units` but the detail is the singular `unit/{uuid}`, and
  list vs detail are different shapes). Run the live test or inspect a real
  response; do not guess field names or wrapper keys.

### 3. Accessor — `src/WaffarhaClient.php`
```php
private ?<Name> $<name> = null;

public function <name>(): <Name>
{
    return $this-><name> ??= new <Name>($this->transport);
}
```

### 4. Facade — `src/Facades/Waffarha.php`
Add `@method static <Name> <name>()` and `use Maat\Waffarha\Resources\<Name>;`.

### 5. Container binding — `src/WaffarhaServiceProvider.php`
In `register()`, after the `Units` binding:
```php
$this->app->bind(<Name>::class, fn ($app) => new <Name>($app->make(Transport::class)));
```

### 6. Tests
- **Fake** (`tests/`, extends `Tests\TestCase`): use `Http::fake()` with the real
  response envelope. Cover the happy path (typed DTO mapping), a 401
  refresh-and-retry if relevant, and an error → `WaffarhaRequestException`. Mirror
  `tests/WaffarhaClientTest.php`. Fake URLs look like
  `maat.test/waffarha/<endpoint>*`. Always also fake the
  `maat.test/waffarha/oauth/token` endpoint (see the `fakeToken()` helper).
- **Live** (`tests/Integration/`, extends `IntegrationTestCase`, `#[Group('live')]`):
  a tolerant smoke test asserting plumbing, not specific records. Mirror
  `tests/Integration/LiveUnitsTest.php`. It self-skips without credentials.
- Add a memoization assertion for the new accessor (see
  `test_units_accessor_returns_a_memoized_resource`).

### 7. Docs
- New `docs/<call>.md` per API call — signature, HTTP path, params, example, raw
  response shape, response→DTO mapping table. Mirror `docs/get-units.md` /
  `docs/get-unit.md`.
- Add the DTO field tables to `docs/data-objects.md`.
- Add links in `docs/README.md` (index) and the README "Documentation" table.

## Verify

```bash
composer analyse   # PHPStan level max — must stay green
composer test      # mocked suite
composer lint      # Pint (composer format to fix)
# optional, needs live creds:
composer test:live
```

## Checklist
- [ ] DTO(s) in `src/Data/` with `fromArray`, scalar guards, raw `attributes`
- [ ] Resource in `src/Resources/` extending `Resource`
- [ ] Memoized accessor on `WaffarhaClient`
- [ ] `@method` on the facade
- [ ] Container binding in the service provider
- [ ] Fake + live tests, accessor memoization test
- [ ] Per-call doc + data-objects entry + index/README links
- [ ] `composer analyse`, `composer test`, `composer lint` green
