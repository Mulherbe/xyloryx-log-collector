<?php

namespace Xiloryx\LogCollector;

use Throwable;

class XiloryxLogHandler
{
    /**
     * Report an exception to Xiloryx Log.
     * Uses native cURL to be completely independent from Laravel's cache/redis configuration.
     */
    public function report(Throwable $exception): void
    {
        // Skip if not enabled
        if (!config('xiloryx-log.enabled', true)) {
            return;
        }

        $endpoint = config('xiloryx-log.endpoint');
        $apiKey = config('xiloryx-log.api_key');

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

            // Use native cURL instead of Laravel Http to avoid cache/redis dependencies
            $this->sendWithCurl($endpoint, $apiKey, $payload);

        } catch (\Throwable $e) {
            // Fail silently - NEVER break the application if monitoring is down
            // Optionally log to local error log in dev mode
            if (config('app.debug', false)) {
                error_log('Xiloryx Log: Failed to report error - ' . $e->getMessage());
            }
        }
    }

    /**
     * Send error data using native cURL (no Laravel dependencies).
     */
    protected function sendWithCurl(string $endpoint, string $apiKey, array $payload): void
    {
        $ch = curl_init($endpoint);

        if ($ch === false) {
            return; // cURL init failed, fail silently
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        curl_exec($ch);
        curl_close($ch);
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
