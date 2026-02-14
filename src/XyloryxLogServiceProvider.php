<?php

namespace Xyloryx\LogCollector;

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
    }
}
