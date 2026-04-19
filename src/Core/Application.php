<?php
// =============================================================
// src/Core/Application.php - نواة التطبيق
// =============================================================

namespace App\Core;

class Application
{
    private static ?Application $instance = null;
    private Container $container;
    private Router $router;
    private string $basePath;

    public function __construct(string $basePath)
    {
        self::$instance = $this;
        $this->basePath = rtrim($basePath, '/');
        $this->container = Container::getInstance();
        $this->router = new Router();

        $this->registerCoreServices();
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * تسجيل الخدمات الأساسية
     */
    private function registerCoreServices(): void
    {
        $this->container->instance('app', $this);
        $this->container->instance('router', $this->router);

        // Database
        $this->container->singleton('db', function () {
            return Database::getInstance();
        });

        // Cache (file-based legacy)
        $this->container->singleton(\App\Services\CacheService::class, function () {
            return new \App\Services\CacheService($this->basePath . '/storage/cache');
        });

        // Redis Cache (primary)
        $this->container->singleton(\App\Services\RedisCacheService::class, function () {
            return \App\Services\RedisCacheService::getInstance();
        });

        // Auth
        $this->container->singleton(\App\Services\AuthService::class, function () {
            return new \App\Services\AuthService();
        });

        // Shift Service
        $this->container->singleton(\App\Services\ShiftService::class, function () {
            return new \App\Services\ShiftService(
                $this->container->make(\App\Services\RedisCacheService::class)
            );
        });

        // Geofence (hardened with risk scoring)
        $this->container->singleton(\App\Services\GeofenceService::class, function () {
            return new \App\Services\GeofenceService(
                $this->container->make(\App\Services\RedisCacheService::class)
            );
        });

        // Attendance
        $this->container->singleton(\App\Services\AttendanceService::class, function () {
            return new \App\Services\AttendanceService(
                $this->container->make(\App\Services\ShiftService::class),
                $this->container->make(\App\Services\GeofenceService::class)
            );
        });

        // WhatsApp
        $this->container->singleton(\App\Services\WhatsAppService::class, function () {
            return \App\Services\WhatsAppService::getInstance();
        });

        // Export
        $this->container->singleton(\App\Services\ExportService::class, function () {
            return new \App\Services\ExportService();
        });

        // Redis Rate Limiter
        $this->container->singleton(\App\Middleware\RedisRateLimiter::class, function () {
            return new \App\Middleware\RedisRateLimiter();
        });

        // Legacy Rate Limiter (backward compatibility)
        $this->container->singleton(\App\Middleware\RateLimiter::class, function () {
            return new \App\Middleware\RateLimiter();
        });

        // Session Timeout
        $this->container->singleton(\App\Middleware\SessionTimeout::class, function () {
            return new \App\Middleware\SessionTimeout(30);
        });

        // Queue Manager
        $this->container->singleton(\App\Queue\QueueManager::class, function () {
            return \App\Queue\QueueManager::getInstance();
        });
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * حل خدمة من الحاوية
     */
    public function make(string $abstract): mixed
    {
        return $this->container->make($abstract);
    }

    /**
     * تشغيل التطبيق
     */
    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // حساب URI النسبي
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        // إزالة base path
        $basePath = rtrim($scriptName, '/');
        if ($basePath && str_starts_with($requestUri, $basePath)) {
            $uri = substr($requestUri, strlen($basePath));
        } else {
            $uri = $requestUri;
        }

        $uri = strtok($uri, '?') ?: '/';

        $this->router->dispatch($method, $uri);
    }
}
