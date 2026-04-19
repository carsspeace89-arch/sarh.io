<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/announcements.php - API لجلب الإعلانات النشطة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

setupApiHeaders();

// التحقق من الـ Origin
if (!validateApiOrigin()) {
    http_response_code(403);
    apiError('Invalid origin', [], 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Method not allowed', [], 405);
}

try {
    // معاملات الطلب
    $tickerOnly = isset($_GET['ticker_only']) && $_GET['ticker_only'] === '1';
    $branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
    
    // بناء الاستعلام
    $sql = "SELECT id, title, content, type, priority, icon, color, created_at 
            FROM announcements 
            WHERE is_active = 1";
    
    if ($tickerOnly) {
        $sql .= " AND show_in_ticker = 1";
    }
    
    // تصفية حسب الجمهور المستهدف
    $sql .= " AND (target_audience = 'all' OR target_audience = 'employees'";
    
    if ($branchId) {
        $sql .= " OR (target_audience = 'specific_branch' AND branch_id = :branch_id)";
    }
    
    $sql .= ")";
    
    // ترتيب حسب الأولوية والتاريخ
    $sql .= " ORDER BY 
              FIELD(priority, 'urgent', 'high', 'normal', 'low'),
              created_at DESC";
    
    $stmt = db()->prepare($sql);
    
    if ($branchId) {
        $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تنسيق البيانات
    foreach ($announcements as &$announcement) {
        // إضافة معلومات إضافية
        $announcement['priority_label'] = [
            'low' => 'منخفض',
            'normal' => 'عادي',
            'high' => 'مرتفع',
            'urgent' => 'عاجل'
        ][$announcement['priority']] ?? 'عادي';
        
        $announcement['type_label'] = [
            'news' => 'خبر',
            'announcement' => 'إعلان',
            'circular' => 'تعميم',
            'alert' => 'تنبيه',
            'event' => 'حدث'
        ][$announcement['type']] ?? 'إعلان';
    }
    
    apiSuccess([
        'announcements' => $announcements,
        'count' => count($announcements)
    ]);
    
} catch (Exception $e) {
    error_log("Announcements API Error: " . $e->getMessage());
    apiError('Failed to fetch announcements', [], 500);
}
