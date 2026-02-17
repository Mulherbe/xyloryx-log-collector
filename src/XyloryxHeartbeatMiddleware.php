<?php

namespace Xyloryx\LogCollector;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XyloryxHeartbeatMiddleware
{
    /**
     * In-memory counter — per worker process, no external dependency needed.
     * Note: With multiple PHP-FPM workers, each has its own counter.
     * A lower threshold (10-20) is recommended for production.
     */
    private static int $counter = 0;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Increment the in-memory counter after each request.
     * When it hits the threshold, flush the batch to the server and reset.
     * This runs in the terminate phase so it does NOT block the response.
     * Uses native cURL to be completely independent from Laravel's cache/redis configuration.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (!config('xyloryx-log.heartbeat_enabled', false)) {
            return;
        }

        $apiKey = config('xyloryx-log.api_key');
        $endpoint = config('xyloryx-log.heartbeat_endpoint');

        if (empty($apiKey) || empty($endpoint)) {
            return;
        }

        self::$counter++;

        // Configurable threshold (default 10 for multi-worker environments)
        $threshold = config('xyloryx-log.heartbeat_threshold', 10);

        if (self::$counter >= $threshold) {
            $toSend = self::$counter;
            self::$counter = 0;

            try {
                // Use native cURL instead of Laravel Http to avoid cache/redis dependencies
                $this->sendWithCurl($endpoint, $apiKey, $toSend);
            } catch (\Throwable $e) {
                // Fail silently — NEVER break the application for monitoring
            }
        }
    }

    /**
     * Send heartbeat data using native cURL (no Laravel dependencies).
     */
    protected function sendWithCurl(string $endpoint, string $apiKey, int $count): void
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
            CURLOPT_POSTFIELDS => json_encode(['count' => $count]),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
