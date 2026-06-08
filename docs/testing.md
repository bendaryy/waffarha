# Testing

The package ships two test suites:

| Suite | Command | Network | CI |
|-------|---------|---------|----|
| **Fake** | `composer test` | None — `Http::fake()` | ✅ runs |
| **Live** | `composer test:live` | Hits the real Maat API | ❌ never |

Plus quality tooling:

```bash
composer analyse    # PHPStan (level max, via Larastan)
composer format     # Laravel Pint (composer lint to check only)
```

## Fake suite

Fully mocked with `Http::fake()` and Orchestra Testbench — no credentials or
network needed. This is what CI runs.

```bash
composer test
```

## Live (integration) suite

`tests/Integration/` exercises the real Maat API. It **self-skips** unless
credentials are present, so it's safe to run any time. Provide them as shell
environment variables or via a gitignored `tests/.env.live` file (copy
`tests/.env.live.example`):

```dotenv
WAFFARHA_LIVE_BASE_URL=https://your-maat-host.example.com/waffarha
WAFFARHA_LIVE_CLIENT_ID=your-client-id
WAFFARHA_LIVE_CLIENT_SECRET=your-client-secret
```

Then:

```bash
composer test:live
```

Or inline:

```bash
WAFFARHA_LIVE_BASE_URL=... WAFFARHA_LIVE_CLIENT_ID=... WAFFARHA_LIVE_CLIENT_SECRET=... composer test:live
```

The live tests obtain a real token, list units, and fetch one unit by UUID,
asserting the SDK plumbing rather than specific records. CI only ever runs the
Fake suite and never sees live credentials.
