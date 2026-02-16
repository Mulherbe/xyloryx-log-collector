# Xyloryx Log Collector

Laravel package for sending errors to [Xyloryx Log](https://log.xiloryx.fr) — real-time error monitoring for Laravel applications.

## Installation

```bash
composer require xyloryx/log-collector
```

That's it. The package auto-registers itself via Laravel's package discovery.

## Configuration

### 1. Add your API key

Add the following to your `.env` file:

```env
XYLORYX_LOG_API_KEY=your_project_api_key_here
```

You can find your API key in your project settings on [log.xiloryx.fr](https://log.xiloryx.fr).

### 2. (Optional) Publish the config file

```bash
php artisan vendor:publish --tag=xyloryx-log-config
```

This gives you access to the full config at `config/xyloryx-log.php`.

## Available environment variables

| Variable | Default | Description |
|---|---|---|
| `XYLORYX_LOG_API_KEY` | `null` | **Required.** Your project API key |
| `XYLORYX_LOG_ENABLED` | `true` | Enable/disable error reporting |
| `XYLORYX_LOG_HEARTBEAT` | `false` | Enable request counting (premium) |

## Features

### Error reporting

All unhandled exceptions are automatically captured and sent to your dashboard — no code changes required. The package hooks into Laravel's exception handler on boot.

Each error includes:
- Exception message, file, and line
- Full stack trace
- Request URL, method, and IP
- Authenticated user ID (if available)
- PHP version and environment

### Project Health (premium)

When `XYLORYX_LOG_HEARTBEAT=true`, the package tracks your total request count using a local cache buffer. Requests are batched and sent to Xyloryx Log every 100 hits — **zero performance impact** on your app.

This powers the **Project Health** dashboard: total requests, total errors, and error rate over 30 days.

## Silent by design

The package will **never** throw an exception or slow down your application:
- All HTTP calls to Xyloryx Log have a 2-second timeout
- Every failure is caught and silently ignored
- Heartbeat runs in the `terminate()` phase, after the response is sent

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11, or 12
- Guzzle HTTP client (included with Laravel)

## Support

For support: contact@xyloryx.com
