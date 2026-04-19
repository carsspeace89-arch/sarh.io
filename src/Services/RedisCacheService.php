<?php
// =============================================================
// src/Services/RedisCacheService.php - Redis Cache Layer
// =============================================================
// Production cache with Redis primary and file fallback.
// Caches settings, branch configs, frequent queries.
// =============================================================

namespace App\Services;

use App\Core\Redis;

class RedisCacheService
{
    private ?CacheService $fileCache;
    private static ?self $instance = null;

    public function __construct(?CacheService $fileCache = null)
    {
        $this->fileCache = $fileCache ?? new CacheService();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get value from cache (Redis first, then file)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $value = $redis->get("cache:{$key}");
                if ($value !== false) {
                    return $value;
                }
            } catch (\RedisException $e) {
                // fall through to file cache
            }
        }

        return $this->fileCache->get($key, $default);
    }

    /**
     * Set value in cache (both Redis and file)
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $redis = Redis::getInstance();
        $redisOk = false;
        if ($redis !== null) {
            try {
                $redisOk = (bool)$redis->setex("cache:{$key}", $ttl, $value);
            } catch (\RedisException $e) {
                // continue to file cache
            }
        }

        $fileOk = $this->fileCache->set($key, $value, $ttl);
        return $redisOk || $fileOk;
    }

    /**
     * Get or compute and cache
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Remove from cache
     */
    public function forget(string $key): bool
    {
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $redis->del("cache:{$key}");
            } catch (\RedisException $e) {
                // continue
            }
        }

        return $this->fileCache->forget($key);
    }

    /**
     * Remove by prefix
     */
    public function forgetByPrefix(string $prefix): int
    {
        $count = 0;
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $fullPrefix = $redis->getOption(\Redis::OPT_PREFIX) . "cache:{$prefix}";
                $keys = $redis->keys("{$fullPrefix}*");
                if (!empty($keys)) {
                    // Strip prefix for del command since prefix is auto-added
                    $count = $redis->del($keys);
                }
            } catch (\RedisException $e) {
                // continue
            }
        }

        $count += $this->fileCache->forgetByPrefix($prefix);
        return $count;
    }

    /**
     * Flush all cache
     */
    public function flush(): int
    {
        $count = 0;
        $redis = Redis::getInstance();
        if ($redis !== null) {
            try {
                $fullPrefix = $redis->getOption(\Redis::OPT_PREFIX) . 'cache:';
                $keys = $redis->keys("{$fullPrefix}*");
                if (!empty($keys)) {
                    $count = $redis->del($keys);
                }
            } catch (\RedisException $e) {
                // continue
            }
        }

        $count += $this->fileCache->flush();
        return $count;
    }

    // ==========================================
    // Domain-specific cache helpers
    // ==========================================

    /**
     * Cache system settings (hot path)
     */
    public function getSettings(): array
    {
        return $this->remember('system_settings', 300, function () {
            $rows = \App\Core\Database::getInstance()
                ->query("SELECT setting_key, setting_value FROM settings")
                ->fetchAll(\PDO::FETCH_KEY_PAIR);
            return $rows ?: [];
        });
    }

    /**
     * Invalidate settings cache
     */
    public function invalidateSettings(): void
    {
        $this->forget('system_settings');
    }

    /**
     * Cache branch config
     */
    public function getBranchConfig(int $branchId): ?array
    {
        return $this->remember("branch_config:{$branchId}", 600, function () use ($branchId) {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM branches WHERE id = ? AND is_active = 1");
            $stmt->execute([$branchId]);
            return $stmt->fetch() ?: null;
        });
    }

    /**
     * Cache branch shifts
     */
    public function getBranchShifts(int $branchId): array
    {
        return $this->remember("branch_shifts:{$branchId}", 600, function () use ($branchId) {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM branch_shifts WHERE branch_id = ? AND is_active = 1 ORDER BY shift_number");
            $stmt->execute([$branchId]);
            return $stmt->fetchAll() ?: [];
        });
    }

    /**
     * Invalidate branch cache
     */
    public function invalidateBranch(int $branchId): void
    {
        $this->forget("branch_config:{$branchId}");
        $this->forget("branch_shifts:{$branchId}");
    }
}
