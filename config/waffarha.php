<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maat API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL where the Maat API endpoints are hosted.
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
];
