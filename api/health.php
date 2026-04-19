<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/health.php - فحص صحة النظام (Enhanced v5.0)
// =============================================================
// Includes: PHP, DB, disk, Redis, queue stats, error rates
// =============================================================

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$checks = [
    'php'      => true,
    'database' => false,
    'disk'     => false,
    'redis'    => false,
];

$status = 'healthy';
$details = [];

// فحص PHP — لا نكشف إصدار PHP للحماية من الاستهداف
$details['php'] = 'ok';

// فحص قاعدة البيانات
try {
    require_once __DIR__ . '/../includes/db.php';
    $pdo = db();
    $pdo->query("SELECT 1");
    $checks['database'] = true;
    $details['database'] = 'connected';
} catch (Exception $e) {
    $checks['database'] = false;
    $details['database'] = 'connection failed';
    $status = 'unhealthy';
}

// فحص الكتابة على القرص
$tmpFile = sys_get_temp_dir() . '/attendance_health_' . time();
if (@file_put_contents($tmpFile, 'ok') !== false) {
    $checks['disk'] = true;
    $details['disk'] = 'writable';
    @unlink($tmpFile);
} else {
    $checks['disk'] = false;
    $details['disk'] = 'not writable';
    $status = 'degraded';
}

// فحص Redis
if (class_exists('\App\Core\Redis')) {
    try {
        $redis = \App\Core\Redis::getInstance();
        if ($redis !== null) {
            $redis->ping();
            $checks['redis'] = true;
            $details['redis'] = 'connected';
        } else {
            $details['redis'] = 'unavailable';
            $status = ($status === 'healthy') ? 'degraded' : $status;
        }
    } catch (Exception $e) {
        $details['redis'] = 'connection failed';
        $status = ($status === 'healthy') ? 'degraded' : $status;
    }
} else {
    $details['redis'] = 'not configured';
}

// Queue stats
$queueStats = null;
if (class_exists('\App\Queue\QueueManager')) {
    try {
        $queueStats = \App\Queue\QueueManager::getInstance()->stats();
    } catch (Exception $e) {
        $queueStats = ['error' => 'unavailable'];
    }
}

// وقت الخادم
$details['server_time'] = date('Y-m-d H:i:s');
$details['timezone']    = date_default_timezone_get();

// Log storage size
$logsDir = __DIR__ . '/../storage/logs';
$logsSize = 0;
if (is_dir($logsDir)) {
    foreach (glob($logsDir . '/*.log') as $logFile) {
        $logsSize += filesize($logFile);
    }
}
$details['log_storage_mb'] = round($logsSize / 1048576, 2);

$httpCode = $status === 'healthy' ? 200 : 503;

$response = [
    'status'  => $status,
    'checks'  => $checks,
    'details' => $details,
    'uptime'  => time() - (int)(@filemtime(__DIR__ . '/../install.lock') ?: time()),
];

if ($queueStats !== null) {
    $response['queue'] = $queueStats;
}

http_response_code($httpCode);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
