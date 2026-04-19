<?php
/**
 * ================================================================
 * cron/auto-notifications.php - إشعارات تلقائية للتأخير والغياب
 * ================================================================
 * يفحص التأخير والغياب يومياً وينشئ إشعارات تلقائية
 * 
 * التشغيل: مرة يومياً بعد ساعة العمل (مثلاً 10:00 صباحاً)
 * مثال: 0 10 * * * /usr/bin/php /path/to/cron/auto-notifications.php
 * ================================================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// حماية من الطلبات العشوائية
$cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '';
if (php_sapi_name() !== 'cli') {
    if (empty($cronSecret) || !isset($_GET['secret']) || !hash_equals($cronSecret, $_GET['secret'])) {
        http_response_code(403);
        exit('Access denied');
    }
}

$today = date('Y-m-d');
$count = 0;

// ================================================================
// 1. إشعارات المتأخرين
// ================================================================
try {
    $lateStmt = db()->prepare("
        SELECT a.employee_id, e.name, e.branch_id, a.late_minutes, a.timestamp,
               b.name AS branch_name
        FROM attendances a
        JOIN employees e ON a.employee_id = e.id
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE a.attendance_date = ? AND a.type = 'in' AND a.late_minutes > 0
          AND e.is_active = 1 AND e.deleted_at IS NULL
        ORDER BY a.late_minutes DESC
    ");
    $lateStmt->execute([$today]);

    // جلب ورديات الفروع لتحديد الوردية
    $notifBranchShifts = [];
    $nBsStmt = db()->query("SELECT branch_id, shift_number, shift_start, shift_end FROM branch_shifts WHERE is_active = 1 ORDER BY branch_id, shift_number");
    foreach ($nBsStmt->fetchAll() as $s) {
        $notifBranchShifts[$s['branch_id']][] = $s;
    }

    foreach ($lateStmt->fetchAll() as $l) {
        $check = db()->prepare("SELECT id FROM notifications WHERE category = 'late' AND link = ? AND DATE(created_at) = ?");
        $check->execute(['employee-profile.php?id=' . $l['employee_id'], $today]);
        if (!$check->fetch()) {
            $nShifts = $notifBranchShifts[$l['branch_id']] ?? [];
            $nShiftNum = !empty($nShifts) ? assignTimeToShift(date('H:i', strtotime($l['timestamp'])), $nShifts) : 1;
            $shiftLabel = ' (الوردية ' . $nShiftNum . ')';
            db()->prepare("INSERT INTO notifications (title, message, type, category, link) VALUES (?, ?, 'warning', 'late', ?)")->execute([
                'تأخير: ' . $l['name'],
                'تأخر ' . $l['name'] . ' بمقدار ' . $l['late_minutes'] . ' دقيقة في فرع ' . ($l['branch_name'] ?? 'غير محدد') . $shiftLabel,
                'employee-profile.php?id=' . $l['employee_id']
            ]);
            $count++;
        }
    }
} catch (PDOException $e) {
    error_log("Auto-notifications late error: " . $e->getMessage());
}

// ================================================================
// 2. إشعارات الغائبين (بعد ساعة من بدء العمل)
// ================================================================
try {
    $absentStmt = db()->prepare("
        SELECT e.id, e.name, b.name AS branch_name
        FROM employees e
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE e.is_active = 1 AND e.deleted_at IS NULL
          AND e.id NOT IN (
              SELECT DISTINCT employee_id FROM attendances WHERE attendance_date = ?
          )
          AND e.id NOT IN (
              SELECT employee_id FROM leaves WHERE status = 'approved' AND ? BETWEEN start_date AND end_date
          )
    ");
    $absentStmt->execute([$today, $today]);

    $absentList = $absentStmt->fetchAll();
    if (count($absentList) > 0) {
        $check = db()->prepare("SELECT id FROM notifications WHERE category = 'absence' AND DATE(created_at) = ? LIMIT 1");
        $check->execute([$today]);
        if (!$check->fetch()) {
            $names = array_slice(array_column($absentList, 'name'), 0, 5);
            $extra = count($absentList) > 5 ? ' و' . (count($absentList) - 5) . ' آخرين' : '';
            db()->prepare("INSERT INTO notifications (title, message, type, category, link) VALUES (?, ?, 'danger', 'absence', ?)")->execute([
                count($absentList) . ' موظف غائب اليوم',
                'الغائبون: ' . implode('، ', $names) . $extra,
                'report-absence.php?date_from=' . $today . '&date_to=' . $today
            ]);
            $count++;
        }
    }
} catch (PDOException $e) {
    error_log("Auto-notifications absence error: " . $e->getMessage());
}

// ================================================================
// 3. تنبيه وثائق منتهية الصلاحية قريباً
// ================================================================
try {
    $docStmt = db()->query("
        SELECT g.employee_id, e.name, g.group_name, g.expiry_date,
               DATEDIFF(g.expiry_date, CURDATE()) AS days_left
        FROM emp_document_groups g
        JOIN employees e ON g.employee_id = e.id
        WHERE e.is_active = 1 AND e.deleted_at IS NULL
          AND g.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");

    foreach ($docStmt->fetchAll() as $doc) {
        $check = db()->prepare("SELECT id FROM notifications WHERE category = 'documents' AND link = ? AND DATE(created_at) = ?");
        $check->execute(['employee-profile.php?id=' . $doc['employee_id'], $today]);
        if (!$check->fetch()) {
            db()->prepare("INSERT INTO notifications (title, message, type, category, link) VALUES (?, ?, 'danger', 'documents', ?)")->execute([
                'وثيقة تنتهي قريباً: ' . $doc['name'],
                $doc['group_name'] . ' تنتهي خلال ' . $doc['days_left'] . ' يوم',
                'employee-profile.php?id=' . $doc['employee_id']
            ]);
            $count++;
        }
    }
} catch (PDOException $e) {
    error_log("Auto-notifications docs error: " . $e->getMessage());
}

$msg = date('Y-m-d H:i:s') . " - Auto notifications: {$count} new\n";
if (php_sapi_name() === 'cli') {
    echo $msg;
} else {
    echo json_encode(['success' => true, 'count' => $count]);
}
