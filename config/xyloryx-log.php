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
    | When enabled, sends a heartbeat on EVERY request to track total request
    | count per project. Runs in terminate phase (non-blocking).
    |
    | Simple, reliable, works perfectly with multiple PHP-FPM workers.
    |
    */

    'heartbeat_enabled' => env('XYLORYX_LOG_HEARTBEAT', false),

    'heartbeat_endpoint' => 'https://log.xiloryx.fr/api/heartbeat',


];
