# Authentication

**The SDK manages the OAuth token lifecycle for you.** Once `client_id` and
`client_secret` are configured, every call automatically:

1. obtains a `client_credentials` access token,
2. caches it (in the configured `cache_store`) until shortly before it expires,
3. attaches it as a `Bearer` token, and
4. on a `401`, refreshes the token and retries the request once.

You never need to fetch, store, or attach tokens yourself.

The rest of this page documents the underlying token endpoint for reference
(e.g. if you also integrate from another stack).

## Token endpoint

Maat exposes a Laravel Passport `client_credentials` endpoint. The SDK obtains a
token from:

```http
POST {MAAT_URL}/oauth/token
Content-Type: application/json
Accept: application/json

{
    "grant_type": "client_credentials",
    "client_id": "{MAAT_CLIENT_ID}",
    "client_secret": "{MAAT_CLIENT_SECRET}",
    "scope": "*"
}
```

### Successful response

The grant also issues a refresh token (default refresh TTL: **1 month**):

```json
{
    "token_type": "Bearer",
    "expires_in": 31536000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "refresh_token": "def50200a8c4b2e7..."
}
```

### Refreshing

When the access token expires, the saved `refresh_token` is exchanged for a new
pair at the same endpoint:

```http
POST {MAAT_URL}/oauth/token
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

The response shape is identical — a new `access_token` **and** a fresh
`refresh_token` (the old refresh token is invalidated). If the refresh token is
rejected, the SDK falls back to a fresh `client_credentials` grant.

### cURL examples

```bash
# Obtain initial token
curl -X POST "$MAAT_URL/oauth/token" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"grant_type":"client_credentials","client_id":"'"$MAAT_CLIENT_ID"'","client_secret":"'"$MAAT_CLIENT_SECRET"'","scope":"*"}'

# Refresh
curl -X POST "$MAAT_URL/oauth/token" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"grant_type":"refresh_token","refresh_token":"'"$MAAT_REFRESH_TOKEN"'","client_id":"'"$MAAT_CLIENT_ID"'","client_secret":"'"$MAAT_CLIENT_SECRET"'","scope":"*"}'
```
