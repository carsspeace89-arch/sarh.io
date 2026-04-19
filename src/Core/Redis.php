<?php
// =============================================================
// src/Core/Redis.php - Redis Connection Manager
// =============================================================
// Provides centralized Redis connection with fallback handling
// =============================================================

namespace App\Core;

class Redis
{
    private static ?\Redis $instance = null;
    private static bool $available = false;
    private static array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => null,
        'database' => 0,
        'prefix' => 'sarh:',
        'timeout' => 2.0,
        'read_timeout' => 2.0,
    ];

    /**
     * Configure Redis connection parameters
     */
    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
        self::$instance = null;
        self::$available = false;
    }

    /**
     * Get Redis instance (singleton)
     */
    public static function getInstance(): ?\Redis
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (!extension_loaded('redis')) {
            self::$available = false;
            return null;
        }

        try {
            $redis = new \Redis();
            $connected = $redis->connect(
                self::$config['host'],
                self::$config['port'],
                self::$config['timeout'],
                null,
                0,
                self::$config['read_timeout']
            );

            if (!$connected) {
                self::$available = false;
                return null;
            }

            if (!empty(self::$config['password'])) {
                $redis->auth(self::$config['password']);
            }

            if (self::$config['database'] > 0) {
                $redis->select(self::$config['database']);
            }

            $redis->setOption(\Redis::OPT_PREFIX, self::$config['prefix']);
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

            self::$instance = $redis;
            self::$available = true;
            return self::$instance;
        } catch (\RedisException $e) {
            self::$available = false;
            // Log Redis connection failures
            if (class_exists('\App\Core\Logger')) {
                try {
                    \App\Core\Logger::error('Redis connection failed', [
                        'error' => $e->getMessage(),
                        'host' => self::$config['host'],
                        'port' => self::$config['port'],
                    ]);
                } catch (\Throwable $logError) {
                    // Fallback to error_log if Logger fails
                    error_log("Redis connection failed: {$e->getMessage()}");
                }
            }
            return null;
        }
    }

    /**
     * Check if Redis is available
     */
    public static function isAvailable(): bool
    {
        if (self::$instance === null) {
            self::getInstance();
        }
        return self::$available;
    }

    /**
     * Reset the connection (for testing)
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            try {
                self::$instance->close();
            } catch (\RedisException $e) {
                // ignore
            }
        }
        self::$instance = null;
        self::$available = false;
    }
}
