<?php
// =============================================================
// src/Controllers/Api/V1/HealthController.php - System Health & Monitoring
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Redis;
use App\Core\Logger;
use App\Queue\QueueManager;

class HealthController extends Controller
{
    /**
     * GET /api/v1/health
     */
    public function index(): void
    {
        $checks = [];
        $status = 'healthy';

        // PHP
        $checks['php'] = ['status' => 'ok', 'version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION];

        // Database
        try {
            $db = Database::getInstance();
            $start = microtime(true);
            $db->query("SELECT 1");
            $latency = round((microtime(true) - $start) * 1000, 2);
            $checks['database'] = ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => 'connection failed'];
            $status = 'unhealthy';
        }

        // Redis
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $start = microtime(true);
                $redis->ping();
                $latency = round((microtime(true) - $start) * 1000, 2);
                $checks['redis'] = ['status' => 'ok', 'latency_ms' => $latency];
            } catch (\Throwable $e) {
                $checks['redis'] = ['status' => 'degraded', 'message' => 'ping failed'];
                $status = $status === 'healthy' ? 'degraded' : $status;
            }
        } else {
            $checks['redis'] = ['status' => 'unavailable', 'message' => 'extension not loaded or connection failed'];
            $status = $status === 'healthy' ? 'degraded' : $status;
        }

        // Queue
        try {
            $queueStats = QueueManager::getInstance()->stats();
            $checks['queue'] = [
                'status' => 'ok',
                'pending' => $queueStats['pending'] ?? 0,
                'failed' => $queueStats['failed'] ?? 0,
            ];
        } catch (\Throwable $e) {
            $checks['queue'] = ['status' => 'unknown'];
        }

        // Disk
        $storagePath = dirname(__DIR__, 3) . '/storage';
        $checks['disk'] = [
            'status' => is_writable($storagePath) ? 'ok' : 'error',
            'free_mb' => round(disk_free_space($storagePath ?: '/') / 1048576),
        ];
        if ($checks['disk']['status'] === 'error') {
            $status = 'unhealthy';
        }

        // Slow query log count (last hour)
        try {
            $logFile = dirname(__DIR__, 3) . '/storage/logs/app-' . date('Y-m-d') . '.log';
            $slowQueries = 0;
            if (file_exists($logFile)) {
                $oneHourAgo = time() - 3600;
                $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach (array_slice($lines, -200) as $line) {
                    if (str_contains($line, 'slow_query')) {
                        $slowQueries++;
                    }
                }
            }
            $checks['slow_queries_1h'] = $slowQueries;
        } catch (\Throwable $e) {
            // ignore
        }

        $checks['server_time'] = date('Y-m-d H:i:s');
        $checks['timezone'] = date_default_timezone_get();

        $this->json([
            'status' => $status,
            'checks' => $checks,
            'version' => 'v1.0.0',
            'request_id' => Logger::getRequestId(),
        ], $status === 'healthy' ? 200 : 503);
    }
}
