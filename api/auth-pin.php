<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/auth-pin.php — مصادقة الموظف عبر PIN
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

// Rate Limiting: Redis-first, file-based fallback (stricter for auth)
if (class_exists('\App\Middleware\RedisRateLimiter')) {
    $rl = new \App\Middleware\RedisRateLimiter();
    $check = $rl->checkByIP('auth_pin', 10, 60);
    if (!$check['allowed']) { $rl->denyResponse($check['retry_after']); }
} elseif (isRateLimited(10, 60, 'auth_pin')) {
    rateLimitResponse();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('طريقة طلب غير مسموحة', 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    apiError('بيانات غير صالحة', 400);
}

$pin = trim($body['pin'] ?? '');
$fingerprint = trim($body['fingerprint'] ?? '');

if (empty($pin) || !preg_match('/^\d{4}$/', $pin)) {
    apiError('أدخل رقم PIN صحيح من 4 أرقام', 400);
}

$employee = getEmployeeByPin($pin);
if (!$employee) {
    apiError('رقم PIN غير صحيح أو الموظف غير مفعّل', 403);
}

// ── إذا تم إرسال بصمة الجهاز: تحقق من الربط ──
if (!empty($fingerprint) && strlen($fingerprint) >= 32) {
    // هل هذا الجهاز مربوط بموظف آخر (ربط صارم)؟
    $boundStmt = db()->prepare("
        SELECT id, name, unique_token, pin_changed_at, created_at
        FROM employees 
        WHERE device_fingerprint = ? 
          AND device_bind_mode = 1 
          AND id != ? 
          AND is_active = 1 
          AND deleted_at IS NULL 
        LIMIT 1
    ");
    $boundStmt->execute([$fingerprint, $employee['id']]);
    $boundEmployee = $boundStmt->fetch();

    if ($boundEmployee) {
        // الجهاز مربوط بموظف آخر ← أعد توجيهه للموظف الصحيح
        apiSuccess([
            'success'        => true,
            'redirected'     => true,
            'token'          => $boundEmployee['unique_token'],
            'employee_name'  => $boundEmployee['name'],
            'employee_id'    => $boundEmployee['id'],
            'pin_changed_at' => $boundEmployee['pin_changed_at'] ?? $boundEmployee['created_at'],
            'message'        => 'هذا الجهاز مربوط بحساب ' . $boundEmployee['name'],
        ]);
    }
}

apiSuccess([
    'success'        => true,
    'token'          => $employee['unique_token'],
    'employee_name'  => $employee['name'],
    'employee_id'    => $employee['id'],
    'pin_changed_at' => $employee['pin_changed_at'] ?? $employee['created_at'],
]);
