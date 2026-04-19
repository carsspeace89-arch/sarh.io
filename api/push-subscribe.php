<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/push-subscribe.php — تسجيل/إلغاء اشتراك Push Notifications
// =============================================================
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = trim($input['token'] ?? '');
$action = $input['action'] ?? 'subscribe';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token required']);
    exit;
}

$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$empId = (int) $employee['id'];

try {
    if ($action === 'subscribe') {
        $subscription = $input['subscription'] ?? [];
        $endpoint = $subscription['endpoint'] ?? '';
        $p256dh = $subscription['keys']['p256dh'] ?? '';
        $auth = $subscription['keys']['auth'] ?? '';

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            echo json_encode(['success' => false, 'message' => 'Invalid subscription data']);
            exit;
        }

        // Validate endpoint URL
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid endpoint URL']);
            exit;
        }

        // Upsert subscription
        $stmt = db()->prepare("
            INSERT INTO push_subscriptions (employee_id, endpoint, p256dh, auth_key)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                employee_id = VALUES(employee_id),
                p256dh = VALUES(p256dh),
                auth_key = VALUES(auth_key),
                updated_at = NOW()
        ");
        $stmt->execute([$empId, $endpoint, $p256dh, $auth]);

        echo json_encode(['success' => true, 'message' => 'تم تفعيل الإشعارات']);
    } elseif ($action === 'unsubscribe') {
        $endpoint = $input['endpoint'] ?? '';
        if (!empty($endpoint)) {
            $stmt = db()->prepare("DELETE FROM push_subscriptions WHERE employee_id = ? AND endpoint = ?");
            $stmt->execute([$empId, $endpoint]);
        }
        echo json_encode(['success' => true, 'message' => 'تم إلغاء الإشعارات']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
}
