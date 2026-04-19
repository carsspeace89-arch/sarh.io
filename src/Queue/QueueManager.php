<?php
// =============================================================
// src/Queue/QueueManager.php - Job Queue with Redis + MySQL fallback
// =============================================================
// Dispatches jobs to Redis queue. Falls back to MySQL jobs table.
// Supports delayed jobs, retries, and priority.
// =============================================================

namespace App\Queue;

use App\Core\Redis;
use App\Core\Database;
use App\Core\Logger;

class QueueManager
{
    private const REDIS_QUEUE = 'queue:default';
    private const REDIS_DELAYED = 'queue:delayed';
    private const REDIS_FAILED = 'queue:failed';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Dispatch a job to the queue
     */
    public function dispatch(Job $job, int $delay = 0): string
    {
        $jobId = bin2hex(random_bytes(16));
        $payload = [
            'id' => $jobId,
            'class' => get_class($job),
            'data' => $job->serialize(),
            'attempts' => 0,
            'max_attempts' => $job->getMaxAttempts(),
            'retry_delay' => $job->getRetryDelay(),
            'created_at' => time(),
            'available_at' => time() + $delay,
        ];

        $redis = Redis::getInstance();
        if ($redis !== null) {
            return $this->dispatchToRedis($redis, $payload, $delay);
        }

        return $this->dispatchToDatabase($payload, $delay);
    }

    /**
     * Fetch next available job from queue
     */
    public function pop(): ?array
    {
        $redis = Redis::getInstance();
        if ($redis !== null) {
            return $this->popFromRedis($redis);
        }

        return $this->popFromDatabase();
    }

    /**
     * Mark job as completed
     */
    public function complete(string $jobId): void
    {
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $redis->hDel('queue:processing', $jobId);
            } catch (\RedisException $e) {
                // log and continue
            }
            return;
        }

        $this->completeInDatabase($jobId);
    }

    /**
     * Mark job as failed, retry if attempts remain
     */
    public function fail(string $jobId, array $payload, \Throwable $e): void
    {
        $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;
        $payload['last_error'] = $e->getMessage();
        $payload['failed_at'] = time();

        if ($payload['attempts'] < $payload['max_attempts']) {
            // Retry with delay
            $delay = ($payload['retry_delay'] ?? 60) * $payload['attempts'];
            $payload['available_at'] = time() + $delay;

            Logger::queue('Job retry scheduled', [
                'job_id' => $jobId,
                'class' => $payload['class'],
                'attempt' => $payload['attempts'],
                'delay' => $delay,
            ]);

            $redis = Redis::getInstance();
            if ($redis !== null) {
                $this->dispatchToRedis($redis, $payload, $delay);
            } else {
                $this->retryInDatabase($jobId, $payload, $delay);
            }
            return;
        }

        // Permanently failed
        Logger::error('Job permanently failed', [
            'job_id' => $jobId,
            'class' => $payload['class'],
            'attempts' => $payload['attempts'],
            'error' => $e->getMessage(),
        ]);

        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $redis->hSet(self::REDIS_FAILED, $jobId, json_encode($payload));
                $redis->hDel('queue:processing', $jobId);
            } catch (\RedisException $e2) {
                // ignore
            }
        } else {
            $this->failInDatabase($jobId, $e->getMessage());
        }
    }

    /**
     * Get queue statistics
     */
    public function stats(): array
    {
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                return [
                    'pending' => $redis->lLen(self::REDIS_QUEUE),
                    'delayed' => $redis->zCard(self::REDIS_DELAYED),
                    'processing' => $redis->hLen('queue:processing'),
                    'failed' => $redis->hLen(self::REDIS_FAILED),
                    'driver' => 'redis',
                ];
            } catch (\RedisException $e) {
                // fall through
            }
        }

        return $this->statsFromDatabase();
    }

    // ==========================================
    // Redis implementation
    // ==========================================

    private function dispatchToRedis(\Redis $redis, array $payload, int $delay): string
    {
        $encoded = json_encode($payload);
        if ($delay > 0) {
            $redis->zAdd(self::REDIS_DELAYED, $payload['available_at'], $encoded);
        } else {
            $redis->rPush(self::REDIS_QUEUE, $encoded);
        }

        Logger::queue('Job dispatched (Redis)', [
            'job_id' => $payload['id'],
            'class' => $payload['class'],
            'delay' => $delay,
        ]);

        return $payload['id'];
    }

    private function popFromRedis(\Redis $redis): ?array
    {
        try {
            // Move delayed jobs that are now available
            $now = time();
            $ready = $redis->zRangeByScore(self::REDIS_DELAYED, '-inf', (string)$now);
            foreach ($ready as $item) {
                $redis->zRem(self::REDIS_DELAYED, $item);
                $redis->rPush(self::REDIS_QUEUE, $item);
            }

            // Pop from main queue
            $raw = $redis->lPop(self::REDIS_QUEUE);
            if ($raw === false || $raw === null) {
                return null;
            }

            $payload = json_decode($raw, true);
            if (!$payload) {
                return null;
            }

            // Track as processing
            $redis->hSet('queue:processing', $payload['id'], $raw);

            return $payload;
        } catch (\RedisException $e) {
            Logger::warning('Redis queue pop failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ==========================================
    // MySQL fallback implementation
    // ==========================================

    private function dispatchToDatabase(array $payload, int $delay): string
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO jobs (id, queue, class, payload, attempts, max_attempts, available_at, created_at, status)
            VALUES (?, 'default', ?, ?, 0, ?, FROM_UNIXTIME(?), NOW(), 'pending')
        ");
        $stmt->execute([
            $payload['id'],
            $payload['class'],
            json_encode($payload),
            $payload['max_attempts'],
            $payload['available_at'],
        ]);

        Logger::queue('Job dispatched (MySQL)', [
            'job_id' => $payload['id'],
            'class' => $payload['class'],
        ]);

        return $payload['id'];
    }

    private function popFromDatabase(): ?array
    {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                SELECT * FROM jobs
                WHERE status = 'pending' AND available_at <= NOW()
                ORDER BY created_at ASC
                LIMIT 1
                FOR UPDATE SKIP LOCKED
            ");
            $stmt->execute();
            $row = $stmt->fetch();

            if (!$row) {
                $db->rollBack();
                return null;
            }

            $db->prepare("UPDATE jobs SET status = 'processing', started_at = NOW() WHERE id = ?")
                ->execute([$row['id']]);

            $db->commit();

            $payload = json_decode($row['payload'], true);
            $payload['attempts'] = (int)$row['attempts'];
            return $payload;
        } catch (\Throwable $e) {
            $db->rollBack();
            return null;
        }
    }

    private function completeInDatabase(string $jobId): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE jobs SET status = 'completed', completed_at = NOW() WHERE id = ?")
            ->execute([$jobId]);
    }

    private function retryInDatabase(string $jobId, array $payload, int $delay): void
    {
        $db = Database::getInstance();
        $db->prepare("
            UPDATE jobs SET status = 'pending', attempts = ?, payload = ?,
            available_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
            last_error = ?
            WHERE id = ?
        ")->execute([
            $payload['attempts'],
            json_encode($payload),
            $delay,
            $payload['last_error'] ?? null,
            $jobId,
        ]);
    }

    private function failInDatabase(string $jobId, string $error): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE jobs SET status = 'failed', failed_at = NOW(), last_error = ? WHERE id = ?")
            ->execute([$error, $jobId]);
    }

    private function statsFromDatabase(): array
    {
        $db = Database::getInstance();
        try {
            $row = $db->query("
                SELECT
                    SUM(status = 'pending') AS pending,
                    SUM(status = 'processing') AS processing,
                    SUM(status = 'failed') AS failed,
                    SUM(status = 'completed') AS completed
                FROM jobs
            ")->fetch();

            return [
                'pending' => (int)($row['pending'] ?? 0),
                'delayed' => 0,
                'processing' => (int)($row['processing'] ?? 0),
                'failed' => (int)($row['failed'] ?? 0),
                'driver' => 'mysql',
            ];
        } catch (\Throwable $e) {
            return ['pending' => 0, 'delayed' => 0, 'processing' => 0, 'failed' => 0, 'driver' => 'none'];
        }
    }

    /**
     * Purge failed jobs older than $retentionDays
     * @return int Number of purged jobs
     */
    public function purgeFailedJobs(int $retentionDays = 30): int
    {
        $cutoff = time() - ($retentionDays * 86400);
        $purged = 0;

        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $failed = $redis->hGetAll(self::REDIS_FAILED);
                foreach ($failed as $jobId => $raw) {
                    $data = json_decode($raw, true);
                    if ($data && ($data['failed_at'] ?? 0) < $cutoff) {
                        $redis->hDel(self::REDIS_FAILED, $jobId);
                        $purged++;
                    }
                }
            } catch (\RedisException $e) {
                Logger::warning('Failed to purge Redis failed jobs', ['error' => $e->getMessage()]);
            }
        }

        // Also clean MySQL
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM jobs WHERE status = 'failed' AND failed_at < FROM_UNIXTIME(?)");
            $stmt->execute([$cutoff]);
            $purged += $stmt->rowCount();
        } catch (\Throwable $e) {
            // Table may not exist
        }

        if ($purged > 0) {
            Logger::queue('Purged failed jobs', ['count' => $purged, 'retention_days' => $retentionDays]);
        }

        return $purged;
    }
}
