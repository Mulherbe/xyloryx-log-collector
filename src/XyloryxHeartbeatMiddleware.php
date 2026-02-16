<?php

namespace Xyloryx\LogCollector;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class XyloryxHeartbeatMiddleware
{
    /**
     * In-memory counter — per worker process, no external dependency needed.
     */
    private static int $counter = 0;
    private static int $threshold = 100;

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

        if (self::$counter >= self::$threshold) {
            $toSend = self::$counter;
            self::$counter = 0;

            try {
                Http::timeout(2)
                    ->withHeaders([
                        'X-API-KEY' => $apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->post($endpoint, ['count' => $toSend]);
            } catch (\Exception $e) {
                // Fail silently — never break the application for monitoring
            }
        }
    }
}
