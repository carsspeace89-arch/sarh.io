<?php
// =============================================================
// api/v1/index.php - API v1 Entry Point (Router)
// =============================================================
// All /api/v1/* requests are routed through here.
// Requires .htaccess RewriteRule for clean URLs.
// =============================================================

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Load Composer autoloader (Monolog, Predis)
$composerAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use App\Core\Router;
use App\Core\Logger;

// Initialize structured logging
Logger::init();

// CORS headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [SITE_URL, rtrim(SITE_URL, '/')];
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: ' . SITE_URL);
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Add request ID header
header('X-Request-ID: ' . Logger::getRequestId());

// ── Build Router ──
$router = new Router();

// ── Health ──
$router->get('/health', [\App\Controllers\Api\V1\HealthController::class, 'index']);

// ── Auth routes ──
$router->group(['prefix' => '/auth'], function (Router $r) {
    $r->post('/pin', [\App\Controllers\Api\V1\AuthController::class, 'authenticateByPin']);
    $r->post('/device', [\App\Controllers\Api\V1\AuthController::class, 'authenticateByDevice']);
    $r->post('/login', [\App\Controllers\Api\V1\AuthController::class, 'login']);
    $r->post('/logout', [\App\Controllers\Api\V1\AuthController::class, 'logout']);
});

// ── Attendance routes ──
$router->group(['prefix' => '/attendance'], function (Router $r) {
    $r->post('/check-in', [\App\Controllers\Api\V1\AttendanceController::class, 'checkIn']);
    $r->post('/check-out', [\App\Controllers\Api\V1\AttendanceController::class, 'checkOut']);
    $r->get('/today/{token}', [\App\Controllers\Api\V1\AttendanceController::class, 'todayStats']);
});

// ── Employee routes ──
$router->group(['prefix' => '/employee'], function (Router $r) {
    $r->get('/{token}', [\App\Controllers\Api\V1\EmployeeController::class, 'getByToken']);
    $r->post('/register-device', [\App\Controllers\Api\V1\EmployeeController::class, 'registerDevice']);
});

// ── Dispatch ──
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Strip /api/v1 prefix and query string
$uri = strtok($requestUri, '?');
$basePath = '/api/v1';
if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
$uri = $uri ?: '/';

try {
    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    Logger::error('API v1 unhandled exception', [
        'exception' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'خطأ داخلي في الخادم',
        'request_id' => Logger::getRequestId(),
    ], JSON_UNESCAPED_UNICODE);
}
