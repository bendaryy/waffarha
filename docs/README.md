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
- [Custom requests](custom-requests.md) — the generic `request()` escape hatch.

### Reference

- [Data objects](data-objects.md) — field reference for every returned DTO.
- [Error handling](error-handling.md) — exception types and handling.
- [Testing](testing.md) — running the mocked and live suites.
