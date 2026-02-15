<?php

namespace Xyloryx\LogCollector;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class XyloryxLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/xyloryx-log.php',
            'xyloryx-log'
        );

        $this->app->singleton(XyloryxLogHandler::class, function ($app) {
            return new XyloryxLogHandler();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/xyloryx-log.php' => config_path('xyloryx-log.php'),
        ], 'xyloryx-log-config');

        $this->registerRoutes();
    }

    /**
     * Register the ping route for connection testing.
     */
    protected function registerRoutes(): void
    {
        Route::get('_xyloryx-log/ping', function () {
            // Only respond to requests from the Xyloryx Log platform
            $origin = request()->header('X-XYLORYX-ORIGIN');
            if ($origin !== 'https://log.xiloryx.fr') {
                abort(403, 'Unauthorized origin');
            }

            // Verify the API key matches the configured one
            $requestKey = request()->header('X-API-KEY');
            $configuredKey = config('xyloryx-log.api_key');

            if (empty($configuredKey) || $requestKey !== $configuredKey) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API key mismatch',
                ], 401);
            }

            return response()->json([
                'status' => 'ok',
                'package' => 'xyloryx/log-collector',
                'version' => '1.1.0',
                'enabled' => config('xyloryx-log.enabled', false),
            ]);
        });
    }
}
