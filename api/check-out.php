<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/check-out.php - API تسجيل الانصراف
// =============================================================
// Backward-compatible wrapper. New clients should use /api/v1/attendance/check-out
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate_limiter.php';

setupApiHeaders(['POST', 'OPTIONS']);

// Rate Limiting: Redis-first, file-based fallback
if (class_exists('\App\Middleware\RedisRateLimiter')) {
    $rl = new \App\Middleware\RedisRateLimiter();
    $check = $rl->checkByIP('checkout', 30, 60);
    if (!$check['allowed']) { $rl->denyResponse($check['retry_after']); }
} elseif (isRateLimited(30, 60, 'checkout')) {
    rateLimitResponse();
}
// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!validateApiOrigin()) {
    apiError('Origin غير مسموح', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiError('طريقة طلب غير مسموحة', 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    apiError('بيانات غير صالحة', 400);
}

$token    = trim($body['token']    ?? '');
$lat      = (float) ($body['latitude']  ?? 0);
$lon      = (float) ($body['longitude'] ?? 0);
$accuracy = (float) ($body['accuracy']  ?? 0);

if (empty($token)) {
    apiError('الرمز المميز مطلوب', 400);
}
if ($lat === 0.0 && $lon === 0.0) {
    apiError('بيانات الموقع غير صالحة', 400);
}
// GPS coordinate range validation
if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    apiError('إحداثيات الموقع خارج النطاق الصالح', 400);
}

$employee = getEmployeeByToken($token);
if (!$employee) {
    apiError('رمز غير صالح أو الموظف غير مفعّل', 403);
}

// التحقق من أن الموظف سجّل دخولاً اليوم أولاً
$stmt = db()->prepare("
    SELECT id FROM attendances
    WHERE employee_id = ? AND type = 'in' AND attendance_date = CURDATE()
    LIMIT 1
");
$stmt->execute([$employee['id']]);
if (!$stmt->fetch()) {
    apiError('لم يتم تسجيل الدخول اليوم. سجّل دخولاً أولاً.', 400);
}

// الانصراف متاح في أي وقت بعد تسجيل الحضور

// التحقق من النطاق الجغرافي (باستخدام فرع الموظف إن وجد)
$geoCheck = isWithinGeofence($lat, $lon, $employee['branch_id'] ?? null);
if (!$geoCheck['allowed']) {
    apiError($geoCheck['message'], 400, ['distance' => $geoCheck['distance']]);
}

// تسجيل الانصراف
$result = recordAttendance($employee['id'], 'out', $lat, $lon, $accuracy);

// Structured logging
if (class_exists('\App\Core\Logger')) {
    \App\Core\Logger::api('check-out', [
        'employee_id' => $employee['id'],
        'success' => $result['success'],
        'distance' => $geoCheck['distance'] ?? 0,
    ]);
}

apiSuccess(array_merge($result, [
    'employee_name' => $employee['name'],
    'timestamp'     => date('Y-m-d H:i:s'),
    'distance'      => $geoCheck['distance'] ?? 0
]));
