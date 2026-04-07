<?php
// =============================================================
// api/inbox-count.php — عدد رسائل صندوق الوارد غير المقروءة للموظف
// =============================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    echo json_encode(['count' => 0]);
    exit;
}

$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $stmt = db()->prepare("SELECT COUNT(*) FROM employee_inbox WHERE employee_id = ? AND is_read = 0");
    $stmt->execute([(int)$employee['id']]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['count' => $count, 'success' => true]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
