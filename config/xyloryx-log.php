<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Xyloryx Log Monitoring Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether errors should be sent to Xyloryx Log.
    | You can disable it in development or for debugging purposes.
    |
    */

    'enabled' => env('XYLORYX_LOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Xyloryx Log Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint URL of the Xyloryx Log monitoring platform.
    | This is pre-configured to point to https://log.xiloryx.fr.
    |
    */

    'endpoint' => 'https://log.xiloryx.fr/api/errors',

    /*
    |--------------------------------------------------------------------------
    | Xyloryx Log API Key
    |--------------------------------------------------------------------------
    |
    | Your project's unique API key from Xyloryx Log.
    | You can find this in your project settings.
    |
    */

    'api_key' => env('XYLORYX_LOG_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Heartbeat (Project Health)
    |--------------------------------------------------------------------------
    |
    | Tracks total request count per project. Batches 50 requests before
    | sending to avoid spamming the API. Uses a file-based counter in the
    | package directory (no DB, no cache dependencies).
    |
    | Works reliably across multiple PHP-FPM workers using file locking.
    | Runs in terminate phase (non-blocking).
    |
    */

    'heartbeat_enabled' => env('XYLORYX_LOG_HEARTBEAT', false),

    'heartbeat_endpoint' => 'https://log.xiloryx.fr/api/heartbeat',


];
