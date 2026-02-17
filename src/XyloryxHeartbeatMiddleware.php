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
     * Send a heartbeat after batching requests to track total request count.
     * Uses a file-based counter with flock() for synchronization across workers.
     * Batches of 50 requests before sending to avoid spamming the API.
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

        try {
            $this->incrementAndMaybeSend($endpoint, $apiKey);
        } catch (\Throwable $e) {
            // Fail silently — NEVER break the application for monitoring
        }
    }

    /**
     * Increment counter in package file and send batch when threshold is reached.
     * Uses file locking to work reliably across multiple PHP-FPM workers.
     */
    protected function incrementAndMaybeSend(string $endpoint, string $apiKey): void
    {
        // Counter file in the package directory (vendor/xyloryx/log-collector/)
        $counterFile = __DIR__ . '/../.heartbeat_count';
        $threshold = 50; // Send every 50 requests

        // Open file for reading and writing, create if doesn't exist
        $fp = @fopen($counterFile, 'c+');

        if ($fp === false) {
            // If we can't open the file, fall back to sending every request
            $this->sendWithCurl($endpoint, $apiKey, 1);
            return;
        }

        // Lock file exclusively for atomic read-modify-write
        if (flock($fp, LOCK_EX)) {
            $currentCount = (int) stream_get_contents($fp);
            $newCount = $currentCount + 1;

            if ($newCount >= $threshold) {
                // Reached threshold: send and reset
                $this->sendWithCurl($endpoint, $apiKey, $newCount);
                $newCount = 0;
            }

            // Write new count back to file
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, (string) $newCount);

            flock($fp, LOCK_UN);
        }

        fclose($fp);
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
