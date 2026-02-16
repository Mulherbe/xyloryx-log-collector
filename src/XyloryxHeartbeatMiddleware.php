<?php

namespace Xyloryx\LogCollector;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
     * Send a heartbeat after the response has been sent to the client.
     * This runs in the terminate phase so it does NOT block the response.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Skip if heartbeat is disabled
        if (!config('xyloryx-log.heartbeat_enabled', false)) {
            return;
        }

        $apiKey = config('xyloryx-log.api_key');
        $endpoint = config('xyloryx-log.heartbeat_endpoint');

        if (empty($apiKey) || empty($endpoint)) {
            return;
        }

        try {
            Http::timeout(1)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint);
        } catch (\Exception $e) {
            // Fail silently — never break the application for monitoring
        }
    }
}
