# Xyloryx Log Collector

Laravel package for sending errors to your self-hosted Xyloryx Log monitoring system.

## Installation

Install the package via Composer:

```bash
composer require xyloryx/log-collector
```

## Configuration

### 1. Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=xyloryx-log-config
```

### 2. Environment Variables

Add the following variables to your `.env` file:

```env
XYLORYX_LOG_ENABLED=true
XYLORYX_LOG_ENDPOINT=http://your-xyloryx-log-server.com/api/errors
XYLORYX_LOG_API_KEY=your_project_api_key_here
```

**Important:** Replace the endpoint URL with your Xyloryx Log server URL and the API key with your project's unique key from the Xyloryx Log dashboard.

### 3. Integrate with Exception Handler

In your `app/Exceptions/Handler.php`, add the error reporting to the `register` method:

```php
use Xyloryx\LogCollector\XyloryxLogHandler;

public function register(): void
{
    $this->reportable(function (Throwable $e) {
        app(XyloryxLogHandler::class)->report($e);
    });
}
```

## Usage

Once configured, the package will automatically capture and send all exceptions to your Xyloryx Log server.

### Testing

To test the integration, you can trigger a test exception:

```php
Route::get('/test-error', function () {
    throw new \Exception('Test error for Xyloryx Log');
});
```

Visit `/test-error` in your browser, and the error should appear in your Xyloryx Log dashboard.

## Features

- **Automatic Error Capture**: Captures all unhandled exceptions
- **Context Information**: Includes request URL, method, IP, user ID, and environment
- **Silent Failure**: Won't break your application if the monitoring server is down
- **Timeout Protection**: 2-second timeout prevents slow responses from affecting your app
- **Configurable**: Enable/disable monitoring via environment variables

## Configuration Options

### Enable/Disable Monitoring

```env
XYLORYX_LOG_ENABLED=false
```

### Custom Endpoint

If your Xyloryx Log server is hosted at a different URL:

```env
XYLORYX_LOG_ENDPOINT=https://monitoring.yourdomain.com/api/errors
```

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Guzzle HTTP client (included with Laravel)

## License

Proprietary - Licensed for use with Xyloryx Log monitoring system only.

## Support

For support, please contact: contact@xyloryx.com

---

**Note:** This package requires an active Xyloryx Log server installation. Visit the Xyloryx Log documentation for setup instructions.
# xyloryx-log-collector
