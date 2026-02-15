# Xyloryx Log Collector

Laravel package for sending errors to [Xyloryx Log](https://log.xiloryx.fr) — real-time error monitoring for Laravel applications.

## Installation

Install the package via Composer:

```bash
composer require xyloryx/log-collector
```

## Configuration

### 1. Environment Variables

Add the following variables to your `.env` file:

```env
XYLORYX_LOG_ENABLED=true
XYLORYX_LOG_API_KEY=your_project_api_key_here
```

You can find your API key in your project settings on [log.xiloryx.fr](https://log.xiloryx.fr).

### 2. Integrate with Exception Handler

In your `bootstrap/app.php` (Laravel 11+):

```php
->withExceptions(function (Exceptions $exceptions) {
    if (config('xyloryx-log.enabled', false)) {
        $exceptions->report(function (Throwable $e) {
            app(\Xyloryx\LogCollector\XyloryxLogHandler::class)->report($e);
        });
    }
})
```

For Laravel 10, in your `app/Exceptions/Handler.php`:

```php
use Xyloryx\LogCollector\XyloryxLogHandler;

public function register(): void
{
    $this->reportable(function (Throwable $e) {
        app(XyloryxLogHandler::class)->report($e);
    });
}
```

### 3. (Optional) Publish Configuration

```bash
php artisan vendor:publish --tag=xyloryx-log-config
```

## Usage

Once configured, the package automatically captures and sends all exceptions to your Xyloryx Log dashboard at [log.xiloryx.fr](https://log.xiloryx.fr).

### Testing

Trigger a test exception to verify the integration:

```php
Route::get('/test-error', function () {
    throw new \Exception('Test error for Xyloryx Log');
});
```

Visit `/test-error` in your browser, and the error should appear on your dashboard.

## Features

- **Automatic Error Capture**: Captures all unhandled exceptions
- **Context Information**: Includes request URL, method, IP, user ID, and environment
- **Silent Failure**: Won't break your application if the monitoring platform is unreachable
- **Timeout Protection**: 2-second timeout prevents slow responses from affecting your app
- **Configurable**: Enable/disable monitoring via environment variables

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Guzzle HTTP client (included with Laravel)

## License

MIT

## Support

For support, please contact: contact@xyloryx.com
