#!/usr/bin/env php
<?php
// =============================================================
// worker.php - Background Job Queue Worker
// =============================================================
// Processes queued jobs (auto-checkout, notifications, reports).
// Run: php worker.php [--sleep=3] [--max-jobs=1000] [--timeout=3600]
// =============================================================

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Ensure Composer autoload
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use App\Queue\QueueManager;
use App\Queue\Job;
use App\Core\Logger;
use App\Core\Redis;

// Parse CLI arguments
$options = getopt('', ['sleep::', 'max-jobs::', 'timeout::', 'queue::', 'job-timeout::']);
$sleepSec = (int)($options['sleep'] ?? 3);
$maxJobs = (int)($options['max-jobs'] ?? 1000);
$timeout = (int)($options['timeout'] ?? 3600);
$jobTimeout = (int)($options['job-timeout'] ?? 120);
$startedAt = time();

Logger::init();
Logger::queue('Worker started', [
    'pid' => getmypid(),
    'sleep' => $sleepSec,
    'max_jobs' => $maxJobs,
    'timeout' => $timeout,
]);

echo "[" . date('Y-m-d H:i:s') . "] Worker started (PID: " . getmypid() . ")\n";
echo "  Sleep: {$sleepSec}s | Max jobs: {$maxJobs} | Timeout: {$timeout}s\n";

$queue = QueueManager::getInstance();
$processedCount = 0;

// Signal handling for graceful shutdown
$shouldRun = true;
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function () use (&$shouldRun) {
        echo "\n[" . date('Y-m-d H:i:s') . "] Received SIGTERM, shutting down gracefully...\n";
        $shouldRun = false;
    });
    pcntl_signal(SIGINT, function () use (&$shouldRun) {
        echo "\n[" . date('Y-m-d H:i:s') . "] Received SIGINT, shutting down gracefully...\n";
        $shouldRun = false;
    });
}

while ($shouldRun) {
    if (function_exists('pcntl_signal_dispatch')) {
        pcntl_signal_dispatch();
    }

    // Check timeout
    if ((time() - $startedAt) >= $timeout) {
        echo "[" . date('Y-m-d H:i:s') . "] Timeout reached ({$timeout}s), shutting down\n";
        break;
    }

    // Check max jobs
    if ($processedCount >= $maxJobs) {
        echo "[" . date('Y-m-d H:i:s') . "] Max jobs reached ({$maxJobs}), shutting down\n";
        break;
    }

    // Try to get a job
    $payload = $queue->pop();
    if ($payload === null) {
        sleep($sleepSec);
        continue;
    }

    $jobId = $payload['id'] ?? 'unknown';
    $jobClass = $payload['class'] ?? 'unknown';

    echo "[" . date('Y-m-d H:i:s') . "] Processing job {$jobId} ({$jobClass})\n";

    try {
        // Per-job timeout using pcntl_alarm if available
        $jobTimedOut = false;
        if (function_exists('pcntl_alarm') && function_exists('pcntl_signal')) {
            pcntl_signal(SIGALRM, function () use (&$jobTimedOut, $jobId) {
                $jobTimedOut = true;
                throw new \RuntimeException("Job {$jobId} exceeded timeout limit");
            });
            pcntl_alarm($jobTimeout);
        }

        $job = Job::deserialize($payload['data']);
        $job->handle();

        // Clear alarm
        if (function_exists('pcntl_alarm')) {
            pcntl_alarm(0);
        }

        $queue->complete($jobId);
        $processedCount++;

        echo "[" . date('Y-m-d H:i:s') . "] ✅ Completed job {$jobId}\n";

        Logger::queue('Job completed', [
            'job_id' => $jobId,
            'class' => $jobClass,
            'processed_count' => $processedCount,
        ]);
    } catch (\Throwable $e) {
        echo "[" . date('Y-m-d H:i:s') . "] ❌ Failed job {$jobId}: " . $e->getMessage() . "\n";

        try {
            $job->failed($e);
        } catch (\Throwable $e2) {
            // ignore failure handler errors
        }

        $queue->fail($jobId, $payload, $e);
    }
}

Logger::queue('Worker shutdown', [
    'pid' => getmypid(),
    'processed' => $processedCount,
    'runtime' => time() - $startedAt,
]);

echo "[" . date('Y-m-d H:i:s') . "] Worker shutdown. Processed: {$processedCount} jobs\n";
