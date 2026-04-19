<?php
// =============================================================
// src/Middleware/RedisRateLimiter.php - Redis Sliding Window Rate Limiter
// =============================================================
// Distributed-safe rate limiting using Redis sorted sets.
// Falls back to file-based limiter if Redis is unavailable.
// =============================================================

namespace App\Middleware;

use App\Core\Redis;
use App\Core\Logger;

class RedisRateLimiter
{
    /**
     * Check rate limit using Redis sliding window algorithm.
     * Uses sorted sets with timestamps as scores for precise sliding window.
     *
     * @param string $key Unique key (e.g., "checkin:192.168.1.1")
     * @param int $maxRequests Maximum allowed requests in window
     * @param int $windowSeconds Window duration in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function check(string $key, int $maxRequests = 60, int $windowSeconds = 60): array
    {
        $redis = Redis::getInstance();
        if ($redis === null) {
            return $this->fileFallback($key, $maxRequests, $windowSeconds);
        }

        try {
            return $this->slidingWindowCheck($redis, $key, $maxRequests, $windowSeconds);
        } catch (\RedisException $e) {
            Logger::warning('Redis rate limiter failed, using file fallback', [
                'error' => $e->getMessage(),
                'key' => $key,
            ]);
            return $this->fileFallback($key, $maxRequests, $windowSeconds);
        }
    }

    /**
     * Rate limit by IP + endpoint
     */
    public function checkByIP(string $endpoint, int $maxRequests = 60, int $windowSeconds = 60): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = "rl:{$endpoint}:{$ip}";
        return $this->check($key, $maxRequests, $windowSeconds);
    }

    /**
     * Rate limit by employee token + endpoint
     */
    public function checkByToken(string $token, string $endpoint, int $maxRequests = 30, int $windowSeconds = 60): array
    {
        $key = "rl:{$endpoint}:tok:" . hash('xxh3', $token);
        return $this->check($key, $maxRequests, $windowSeconds);
    }

    /**
     * Send 429 response and exit
     */
    public function denyResponse(int $retryAfter = 60): never
    {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header("Retry-After: {$retryAfter}");
        echo json_encode([
            'success' => false,
            'message' => 'تم تجاوز الحد المسموح من الطلبات. حاول مرة أخرى بعد ' . $retryAfter . ' ثانية.',
            'retry_after' => $retryAfter,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redis sliding window implementation using ZSET
     */
    private function slidingWindowCheck(\Redis $redis, string $key, int $maxRequests, int $windowSeconds): array
    {
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;

        // Use pipeline for atomicity
        $redis->multi(\Redis::PIPELINE);

        // Remove expired entries
        $redis->zRemRangeByScore($key, '-inf', (string)$windowStart);

        // Count current entries in window
        $redis->zCard($key);

        // Add current request
        $redis->zAdd($key, $now, $now . ':' . random_int(0, 99999));

        // Set TTL to auto-cleanup
        $redis->expire($key, $windowSeconds + 1);

        $results = $redis->exec();

        $currentCount = (int)($results[1] ?? 0);

        if ($currentCount >= $maxRequests) {
            // Remove the entry we just added (over limit)
            $redis->zRemRangeByRank($key, -1, -1);

            // Calculate retry_after: time until oldest entry expires
            $oldest = $redis->zRange($key, 0, 0, true);
            $retryAfter = 1;
            if (!empty($oldest)) {
                $oldestScore = reset($oldest);
                $retryAfter = max(1, (int)ceil(($oldestScore + $windowSeconds) - $now));
            }

            Logger::api('Rate limit exceeded', [
                'key' => $key,
                'count' => $currentCount,
                'limit' => $maxRequests,
            ]);

            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => $retryAfter,
            ];
        }

        return [
            'allowed' => true,
            'remaining' => max(0, $maxRequests - $currentCount - 1),
            'retry_after' => 0,
        ];
    }

    /**
     * File-based fallback when Redis is unavailable
     */
    private function fileFallback(string $key, int $maxRequests, int $windowSeconds): array
    {
        $dir = sys_get_temp_dir() . '/sarh_rate_limit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $safeKey = preg_replace('/[^a-zA-Z0-9._-]/', '_', $key);
        $file = $dir . '/' . $safeKey . '.json';
        $now = time();

        $data = ['requests' => [], 'blocked_until' => 0];
        $fp = @fopen($file, 'c+');
        if ($fp) {
            flock($fp, LOCK_EX);
            $content = stream_get_contents($fp);
            if ($content) {
                $data = json_decode($content, true) ?: $data;
            }
        }

        if (($data['blocked_until'] ?? 0) > $now) {
            $retryAfter = $data['blocked_until'] - $now;
            if ($fp) { flock($fp, LOCK_UN); fclose($fp); }
            return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retryAfter];
        }

        // Clean old entries
        $data['requests'] = array_values(array_filter(
            $data['requests'] ?? [],
            fn($ts) => ($now - $ts) < $windowSeconds
        ));

        if (count($data['requests']) >= $maxRequests) {
            $data['blocked_until'] = $now + $windowSeconds;
            if ($fp) {
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($data));
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            return ['allowed' => false, 'remaining' => 0, 'retry_after' => $windowSeconds];
        }

        $data['requests'][] = $now;
        $data['blocked_until'] = 0;

        if ($fp) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        return [
            'allowed' => true,
            'remaining' => max(0, $maxRequests - count($data['requests'])),
            'retry_after' => 0,
        ];
    }
}
