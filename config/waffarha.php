<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Waffarha API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL where the Waffarha API endpoints are hosted.
    |
    */
    'base_url' => env('WAFFARHA_API_BASE_URL', 'https://api.waffarha.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Waffarha API Authentication Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials required to authenticate against Waffarha's third-party APIs.
    | Configure these variables inside your host .env file.
    |
    */
    'client_id' => env('WAFFARHA_CLIENT_ID'),

    'client_secret' => env('WAFFARHA_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The amount of time in seconds that we wait for the Waffarha API response
    | before throwing a connection exception.
    |
    */
    'timeout' => (int) env('WAFFARHA_API_TIMEOUT', 30),
];
