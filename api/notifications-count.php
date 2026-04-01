<?php
// =============================================================
// api/notifications-count.php - عدد الإشعارات غير المقروءة (AJAX)
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isAdminLoggedIn()) {
    jsonResponse(['count' => 0]);
}

try {
    $count = (int)db()->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
    jsonResponse(['count' => $count]);
} catch (PDOException $e) {
    jsonResponse(['count' => 0]);
}
