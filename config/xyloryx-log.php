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
    | When enabled, the collector batches request counts and sends them to
    | the heartbeat endpoint. The threshold defines how many requests each
    | PHP-FPM worker must handle before sending a batch.
    |
    | Default: 10 (recommended for multi-worker production environments)
    | Note: With 4 workers, you'll send ~1 batch per 10 actual requests.
    |
    */

    'heartbeat_enabled' => env('XYLORYX_LOG_HEARTBEAT', false),

    'heartbeat_endpoint' => 'https://log.xiloryx.fr/api/heartbeat',

    'heartbeat_threshold' => env('XYLORYX_LOG_HEARTBEAT_THRESHOLD', 10),


];
