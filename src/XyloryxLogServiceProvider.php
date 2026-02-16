<?php

namespace Xyloryx\LogCollector;

use Illuminate\Contracts\Debug\ExceptionHandler;
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
        $this->registerHeartbeatMiddleware();
        $this->registerExceptionReporting();
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
                'version' => '1.2.0',
                'enabled' => config('xyloryx-log.enabled', false),
                'heartbeat' => config('xyloryx-log.heartbeat_enabled', false),
            ]);
        });
    }

    /**
     * Register the heartbeat middleware for request counting.
     * Uses the terminate phase so it doesn't block the response.
     * Registered on both web and api groups to cover all traffic.
     */
    protected function registerHeartbeatMiddleware(): void
    {
        if (config('xyloryx-log.heartbeat_enabled', false)) {
            /** @var \Illuminate\Routing\Router $router */
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('web', XyloryxHeartbeatMiddleware::class);
            $router->pushMiddlewareToGroup('api', XyloryxHeartbeatMiddleware::class);
        }
    }

    /**
     * Auto-wire exception reporting into Laravel's exception handler.
     * Works on Laravel 10, 11 and 12 — zero config required from the client.
     */
    protected function registerExceptionReporting(): void
    {
        if (!config('xyloryx-log.enabled', true)) {
            return;
        }

        $handler = $this->app->make(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable(function (\Throwable $e) {
                $this->app->make(XyloryxLogHandler::class)->report($e);
            });
        }
    }
}
