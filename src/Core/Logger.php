<?php
// =============================================================
// src/Core/Logger.php - Structured Logging with Monolog
// =============================================================
// Provides centralized, structured logging with request context
// Channels: app, security, api, queue
// =============================================================

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Level;

class Logger
{
    private static array $loggers = [];
    private static string $logDir = '';
    private static string $requestId = '';

    /**
     * Initialize the logging system
     */
    public static function init(?string $logDir = null): void
    {
        self::$logDir = $logDir ?? dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0700, true);
        }
        self::$requestId = substr(bin2hex(random_bytes(8)), 0, 16);
    }

    /**
     * Get a logger channel
     */
    public static function channel(string $channel = 'app'): MonologLogger
    {
        if (isset(self::$loggers[$channel])) {
            return self::$loggers[$channel];
        }

        if (empty(self::$logDir)) {
            self::init();
        }

        $logger = new MonologLogger($channel);

        // Rotating file handler (14 days retention)
        $handler = new RotatingFileHandler(
            self::$logDir . "/{$channel}.log",
            14,
            Level::Debug
        );
        $handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($handler);

        // Error handler - separate file for errors
        if ($channel !== 'error') {
            $errorHandler = new RotatingFileHandler(
                self::$logDir . '/error.log',
                30,
                Level::Error
            );
            $errorHandler->setFormatter(new JsonFormatter());
            $logger->pushHandler($errorHandler);
        }

        // Add context processor
        $logger->pushProcessor(function ($record) {
            $record->extra['request_id'] = self::$requestId;
            $record->extra['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'cli';
            $record->extra['user_id'] = $_SESSION['admin_id'] ?? null;
            $record->extra['uri'] = $_SERVER['REQUEST_URI'] ?? 'cli';
            $record->extra['method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            return $record;
        });

        self::$loggers[$channel] = $logger;
        return $logger;
    }

    /**
     * Get the current request ID
     */
    public static function getRequestId(): string
    {
        if (empty(self::$requestId)) {
            self::$requestId = substr(bin2hex(random_bytes(8)), 0, 16);
        }
        return self::$requestId;
    }

    // Convenience methods
    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::channel($channel)->info($message, $context);
    }

    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::channel($channel)->warning($message, $context);
    }

    public static function error(string $message, array $context = [], string $channel = 'app'): void
    {
        self::channel($channel)->error($message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::channel('security')->warning($message, $context);
    }

    public static function api(string $message, array $context = []): void
    {
        self::channel('api')->info($message, $context);
    }

    public static function queue(string $message, array $context = []): void
    {
        self::channel('queue')->info($message, $context);
    }
}
