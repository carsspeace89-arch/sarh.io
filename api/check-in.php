<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/check-in.php - API تسجيل الدخول
// =============================================================
// Backward-compatible wrapper. New clients should use /api/v1/attendance/check-in
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rate_limiter.php';

setupApiHeaders(['POST', 'OPTIONS']);

// Rate Limiting: Redis-first, file-based fallback
if (class_exists('\App\Middleware\RedisRateLimiter')) {
    $rl = new \App\Middleware\RedisRateLimiter();
    $check = $rl->checkByIP('checkin', 30, 60);
    if (!$check['allowed']) { $rl->denyResponse($check['retry_after']); }
} elseif (isRateLimited(30, 60, 'checkin')) {
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

// قراءة جسم الطلب JSON
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    apiError('بيانات غير صالحة', 400);
}

$token    = trim($body['token']    ?? '');
$lat      = (float) ($body['latitude']  ?? 0);
$lon      = (float) ($body['longitude'] ?? 0);
$accuracy = (float) ($body['accuracy']  ?? 0);

// التحقق من البيانات
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

// التحقق من صحة الـ token
$employee = getEmployeeByToken($token);
if (!$employee) {
    apiError('رمز غير صالح أو الموظف غير مفعّل', 403);
}

// التحقق من أن التسجيل ضمن نافذة الوردية (قبل بدء الوردية بساعة حتى نهايتها)
$schedule       = getBranchSchedule($employee['branch_id'] ?? null);
$workStart      = $schedule['work_start_time'];
$workEnd        = $schedule['work_end_time'];
$nowTime        = date('H:i');

// بداية النافذة = shift_start - 90 دقيقة (ساعة ونصف)
$earlyStart = date('H:i', strtotime($workStart) - 5400);

// التحقق: من earlyStart إلى workEnd
if ($workEnd < $earlyStart) {
    // يعبر منتصف الليل
    $outsideWindow = ($nowTime < $earlyStart && $nowTime > $workEnd);
} else {
    $outsideWindow = ($nowTime < $earlyStart || $nowTime > $workEnd);
}

if ($outsideWindow) {
    apiError("تسجيل الحضور متاح من {$earlyStart} إلى {$workEnd}. الوقت الحالي: {$nowTime}", 200);
}

// التحقق من النطاق الجغرافي (باستخدام فرع الموظف إن وجد)
// تخطي التحقق إذا كان الموظف معفى من شرط الموقع
if (!empty($employee['bypass_geofence'])) {
    $geoCheck = ['allowed' => true, 'distance' => 0, 'radius' => 0, 'message' => 'معفى من شرط الموقع'];
} else {
    $geoCheck = isWithinGeofence($lat, $lon, $employee['branch_id'] ?? null);
    if (!$geoCheck['allowed']) {
        apiError(
            "⛔ لا يمكن تسجيل الحضور من خارج نطاق العمل.\n\n📍 المسافة الحالية: {$geoCheck['distance']} متر\n📏 الحد المسموح: {$geoCheck['radius']} متر\n\nيرجى التوجه إلى مقر العمل والمحاولة مجدداً.",
            200,
            [
                'distance' => $geoCheck['distance'],
                'radius' => $geoCheck['radius'],
            ]
        );
    }
}

// تسجيل الدخول
$result = recordAttendance($employee['id'], 'in', $lat, $lon, $accuracy);

// Structured logging
if (class_exists('\App\Core\Logger')) {
    \App\Core\Logger::api('check-in', [
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
