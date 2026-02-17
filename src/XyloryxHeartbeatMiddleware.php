<?php

namespace Xyloryx\LogCollector;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XyloryxHeartbeatMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Send a heartbeat after each request to track total request count.
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

        try {
            // Send 1 request count for every request - simple and reliable
            $this->sendWithCurl($endpoint, $apiKey, 1);
        } catch (\Throwable $e) {
            // Fail silently — NEVER break the application for monitoring
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
