<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/generate-vapid.php — توليد مفاتيح VAPID وحفظها في الإعدادات
// يمكن استدعاؤها مرة واحدة فقط من لوحة الأدمن
// =============================================================
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// يجب أن يكون أدمن مسجل الدخول
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// التحقق من CSRF
$input = json_decode(file_get_contents('php://input'), true);
if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
    exit;
}

// التحقق: هل المفاتيح موجودة بالفعل؟
$existingPublic = getSystemSetting('vapid_public_key', '');
$existingPrivate = getSystemSetting('vapid_private_key', '');

$force = (bool)($input['force'] ?? false);
if (!empty($existingPublic) && !empty($existingPrivate) && !$force) {
    echo json_encode([
        'success' => true,
        'message' => 'مفاتيح VAPID موجودة بالفعل',
        'public_key' => $existingPublic,
        'already_exists' => true
    ]);
    exit;
}

try {
    // توليد زوج مفاتيح ECDSA P-256
    $key = openssl_pkey_new([
        'curve_name'       => 'prime256v1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ]);

    if (!$key) {
        throw new Exception('فشل في توليد المفتاح: ' . openssl_error_string());
    }

    $details = openssl_pkey_get_details($key);
    if (!$details || !isset($details['ec'])) {
        throw new Exception('فشل في استخراج تفاصيل المفتاح');
    }

    // المفتاح العام: 65 bytes (0x04 + x(32) + y(32)) → base64url
    $publicKeyRaw = chr(4)
        . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)
        . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
    $publicKeyB64 = base64UrlEncode($publicKeyRaw);

    // المفتاح الخاص: 32 bytes → base64url
    $privateKeyRaw = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);
    $privateKeyB64 = base64UrlEncode($privateKeyRaw);

    // حفظ في الإعدادات
    setSystemSetting('vapid_public_key', $publicKeyB64);
    setSystemSetting('vapid_private_key', $privateKeyB64);

    auditLog('vapid_generate', 'تم توليد مفاتيح VAPID جديدة');

    echo json_encode([
        'success'    => true,
        'message'    => 'تم توليد مفاتيح VAPID بنجاح',
        'public_key' => $publicKeyB64,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage(),
    ]);
}
