<?php
// =============================================================
// config/bootstrap.php - تهيئة التطبيق (v5.0 Refactored)
// =============================================================
// يُحمَّل بالإضافة إلى includes/config.php للتوافق العكسي
// =============================================================

// ================== Composer Autoloader ==================
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// ================== التحميل التلقائي PSR-4 ==================
$autoloaderFile = dirname(__DIR__) . '/src/Core/Autoloader.php';
if (!file_exists($autoloaderFile)) {
    return; // Skip v5 bootstrap if src files not deployed
}
require_once $autoloaderFile;

use App\Core\Autoloader;
use App\Core\ErrorHandler;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\Redis;

Autoloader::register();
Autoloader::addNamespace('App', dirname(__DIR__) . '/src');

// ================== Global Error Handler ==================
ErrorHandler::register();

// ================== Structured Logging ==================
Logger::init(dirname(__DIR__) . '/storage/logs');

// ================== Redis Configuration ==================
Redis::configure([
    'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port'     => (int)($_ENV['REDIS_PORT'] ?? 6379),
    'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    'database' => (int)($_ENV['REDIS_DB'] ?? 0),
    'prefix'   => 'sarh:',
]);

// ================== نظام الترجمة ==================
$locale = 'ar';
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['locale'])) {
    $locale = $_SESSION['locale'];
} elseif (!empty($_COOKIE['locale'])) {
    $locale = $_COOKIE['locale'];
}
Lang::init(dirname(__DIR__) . '/lang', $locale);

// ================== التوافق العكسي ==================
// الدوال القديمة لا تزال متاحة عبر includes/functions.php
// النماذج والخدمات الجديدة متاحة عبر App\* namespace
