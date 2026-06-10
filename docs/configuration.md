# Configuration

Publish the config file with:

```bash
php artisan vendor:publish --tag=waffarha-config
```

This creates `config/waffarha.php`, read from these environment variables:

```dotenv
MAAT_URL=https://your-maat-host.example.com/waffarha
MAAT_CLIENT_ID=your-client-id
MAAT_CLIENT_SECRET=your-client-secret
MAAT_API_TIMEOUT=30
```

## Options

| Key | Env Variable | Default | Description |
|-----|--------------|---------|-------------|
| `base_url` | `MAAT_URL` | `null` | Base URL of the Maat API, **including** the `/waffarha` path prefix. |
| `client_id` | `MAAT_CLIENT_ID` | `null` | OAuth client identifier issued by Maat. |
| `client_secret` | `MAAT_CLIENT_SECRET` | `null` | OAuth client secret issued by Maat. |
| `timeout` | `MAAT_API_TIMEOUT` | `30` | HTTP response timeout (seconds). |
| `connect_timeout` | `MAAT_API_CONNECT_TIMEOUT` | `10` | Connection timeout (seconds). |
| `retries` | `MAAT_API_RETRIES` | `2` | Total attempts for transient connection failures (status errors are not retried). |
| `cache_store` | `MAAT_API_CACHE_STORE` | `null` (default store) | Cache store used to persist OAuth tokens. |

## Notes

- **Base URL prefix.** The SDK appends endpoint paths directly to `base_url`, so
  it calls `{base_url}/units`, `{base_url}/unit/{uuid}` and `{base_url}/oauth/token`.
  `MAAT_URL` must therefore include the `/waffarha` prefix.
- **Token cache store.** Tokens are cached per client id. In a multi-server
  deployment, point `cache_store` at a shared store (redis/database) so a token
  fetched on one node is reused by the others.
- **Missing config fails fast.** A missing `base_url`, `client_id` or
  `client_secret` throws a `WaffarhaConfigurationException` when the client is
  resolved — not obscurely at request time. See [error handling](error-handling.md).
