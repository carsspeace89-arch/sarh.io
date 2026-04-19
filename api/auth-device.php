<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/auth-device.php — مصادقة الموظف عبر بصمة الجهاز
// إذا كان الجهاز مربوطاً بموظف → يُرجع بيانات الموظف مباشرة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate_limiter.php';

setupApiHeaders(['POST', 'OPTIONS']);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!validateApiOrigin()) {
    apiError('Origin غير مسموح', 403);
}

// Rate Limiting: Redis-first, file-based fallback
if (class_exists('\App\Middleware\RedisRateLimiter')) {
    $rl = new \App\Middleware\RedisRateLimiter();
    $check = $rl->checkByIP('auth_device', 20, 60);
    if (!$check['allowed']) { $rl->denyResponse($check['retry_after']); }
} elseif (isRateLimited(20, 60, 'auth_device')) {
    rateLimitResponse();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('طريقة طلب غير مسموحة', 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    apiError('بيانات غير صالحة', 400);
}

$fingerprint = trim($body['fingerprint'] ?? '');

if (empty($fingerprint) || strlen($fingerprint) < 32) {
    apiError('بصمة الجهاز غير صالحة', 400);
}

// البحث عن موظف مربوط بهذا الجهاز (ربط صارم أو مراقبة)
$stmt = db()->prepare("
    SELECT id, name, unique_token, pin_changed_at, created_at
    FROM employees 
    WHERE device_fingerprint = ? 
      AND device_bind_mode IN (1, 2) 
      AND is_active = 1 
      AND deleted_at IS NULL 
    LIMIT 1
");
$stmt->execute([$fingerprint]);
$employee = $stmt->fetch();

if (!$employee) {
    apiError('الجهاز غير مربوط بأي موظف', 404);
}

apiSuccess([
    'success'        => true,
    'bound'          => true,
    'token'          => $employee['unique_token'],
    'employee_name'  => $employee['name'],
    'employee_id'    => $employee['id'],
    'pin_changed_at' => $employee['pin_changed_at'] ?? $employee['created_at'],
]);
