<?php

namespace Xyloryx\LogCollector;

use Illuminate\Support\Facades\Http;
use Throwable;

class XyloryxLogHandler
{
    /**
     * Report an exception to Xyloryx Log.
     */
    public function report(Throwable $exception): void
    {
        // Skip if not enabled
        if (!config('xyloryx-log.enabled', true)) {
            return;
        }

        $endpoint = config('xyloryx-log.endpoint');
        $apiKey = config('xyloryx-log.api_key');

        // Skip if configuration is missing
        if (empty($endpoint) || empty($apiKey)) {
            return;
        }

        try {
            $payload = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'stacktrace' => $exception->getTraceAsString(),
                'context' => $this->getContext($exception),
            ];

            Http::timeout(2)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, $payload);

        } catch (\Exception $e) {
            // Fail silently - don't break the application if monitoring is down
            // Optionally log to local error log
            // error_log('Xyloryx Log: Failed to report error - ' . $e->getMessage());
        }
    }

    /**
     * Get contextual information about the error.
     */
    protected function getContext(Throwable $exception): array
    {
        $context = [
            'environment' => config('app.env'),
            'php_version' => PHP_VERSION,
        ];

        // Add request information if available
        if (app()->bound('request')) {
            $request = request();
            $context['url'] = $request->fullUrl();
            $context['method'] = $request->method();
            $context['ip'] = $request->ip();
            
            // Add user information if authenticated
            if (function_exists('auth') && auth()->check()) {
                $context['user_id'] = auth()->id();
            }
        }

        return $context;
    }
}
