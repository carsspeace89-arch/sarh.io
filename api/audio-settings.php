<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/audio-settings.php - API لجلب إعدادات الملفات الصوتية المفعّلة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

setupApiHeaders();

if (!validateApiOrigin()) {
    http_response_code(403);
    apiError('Invalid origin', [], 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $stmt = db()->query("SELECT category, filename, play_mode, volume FROM audio_library WHERE is_active = 1");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $result[$row['category']] = [
            'url'       => SITE_URL . '/assets/audio/' . $row['filename'],
            'play_mode' => $row['play_mode'],
            'volume'    => (float) $row['volume'],
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'audio' => $result], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Audio settings API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'خطأ في الخادم']);
}
