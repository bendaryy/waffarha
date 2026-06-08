<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Maat API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL where the Maat API endpoints are hosted. This must include
    | any path prefix the API lives under, because the SDK appends endpoint
    | paths directly (e.g. "{base_url}/units", "{base_url}/oauth/token").
    |
    | Per the Maat docs the Waffarha integration is served under "/waffarha",
    | so set MAAT_URL to e.g. "https://your-maat-host.example.com/waffarha".
    |
    */
    'base_url' => env('MAAT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Maat API Authentication Credentials
    |--------------------------------------------------------------------------
    |
    | OAuth client credentials issued by Maat. Configure these variables inside
    | your host .env file.
    |
    */
    'client_id' => env('MAAT_CLIENT_ID'),

    'client_secret' => env('MAAT_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The amount of time in seconds that we wait for the Maat API response
    | before throwing a connection exception.
    |
    */
    'timeout' => (int) env('MAAT_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Connection Timeout
    |--------------------------------------------------------------------------
    |
    | Seconds to wait while establishing the TCP/TLS connection before failing.
    |
    */
    'connect_timeout' => (int) env('MAAT_API_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Transient Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Total number of attempts for transient connection failures (timeouts,
    | DNS/connection errors). HTTP status errors are NOT retried here.
    |
    */
    'retries' => (int) env('MAAT_API_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | Token Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store used to persist OAuth access/refresh tokens. Null uses the
    | application's default cache store. Use a shared store (redis, database) in
    | multi-server deployments so tokens are reused across instances.
    |
    */
    'cache_store' => env('MAAT_API_CACHE_STORE'),
];
