<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/employee-notifications.php — إشعارات فورية للموظف (polling)
// يرجع: رسائل الوارد + تنبيه اقتراب الوردية + حالة الحضور
// =============================================================

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Alt-Svc: clear');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    echo json_encode(['success' => false]);
    exit;
}

$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    echo json_encode(['success' => false]);
    exit;
}

$empId    = (int)$employee['id'];
$branchId = !empty($employee['branch_id']) ? (int)$employee['branch_id'] : null;
$result   = ['success' => true, 'inbox_count' => 0, 'alerts' => []];

// ── 1. عدد رسائل الوارد غير المقروءة ──
try {
    $stmt = db()->prepare("SELECT COUNT(*) FROM employee_inbox WHERE employee_id = ? AND is_read = 0");
    $stmt->execute([$empId]);
    $result['inbox_count'] = (int)$stmt->fetchColumn();
} catch (Exception $e) { /* silent */ }

// ── 2. آخر رسائل الوارد (أحدث 3 غير مقروءة) ──
try {
    $stmt = db()->prepare("
        SELECT id, msg_type, title, body, created_at 
        FROM employee_inbox 
        WHERE employee_id = ? AND is_read = 0 
        ORDER BY created_at DESC LIMIT 3
    ");
    $stmt->execute([$empId]);
    $result['inbox_recent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $result['inbox_recent'] = [];
}

// ── 3. تحقق من حالة الحضور اليوم ──
$hasCheckedIn = false;
try {
    $stmt = db()->prepare("SELECT type FROM attendances WHERE employee_id = ? AND attendance_date = CURDATE() ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute([$empId]);
    $lastRecord = $stmt->fetch();
    $hasCheckedIn = (bool)$lastRecord;
    $result['attendance_status'] = $lastRecord ? $lastRecord['type'] : 'none';
} catch (Exception $e) {
    $result['attendance_status'] = 'unknown';
}

// ── 4. تنبيه اقتراب الوردية (إذا لم يسجل الحضور) ──
if (!$hasCheckedIn && $branchId) {
    try {
        $schedule = getBranchSchedule($branchId);
        $shifts   = $schedule['shifts'] ?? [];
        $now      = new DateTime('now', new DateTimeZone('Asia/Riyadh'));
        $nowMin   = (int)$now->format('H') * 60 + (int)$now->format('i');

        foreach ($shifts as $shift) {
            $shiftStartMin = timeToMinutes($shift['shift_start']);
            // تنبيه قبل 30 دقيقة من بداية الوردية
            $alertStartMin = ($shiftStartMin - 30 + 1440) % 1440;
            // نافذة التنبيه: 30 دقيقة قبل الوردية حتى 60 دقيقة بعد بدايتها
            $alertEndMin   = ($shiftStartMin + 60) % 1440;

            $inWindow = false;
            if ($alertEndMin < $alertStartMin) {
                // يعبر منتصف الليل
                $inWindow = ($nowMin >= $alertStartMin || $nowMin <= $alertEndMin);
            } else {
                $inWindow = ($nowMin >= $alertStartMin && $nowMin <= $alertEndMin);
            }

            if ($inWindow) {
                $minutesUntil = ($shiftStartMin - $nowMin + 1440) % 1440;
                if ($minutesUntil > 720) $minutesUntil -= 1440; // normalize

                if ($minutesUntil > 0) {
                    $result['alerts'][] = [
                        'type'    => 'shift_approaching',
                        'icon'    => '⏰',
                        'title'   => 'الوردية تبدأ قريباً',
                        'message' => 'تبدأ ورديتك بعد ' . $minutesUntil . ' دقيقة. لا تنسَ تسجيل الحضور!',
                        'shift'   => (int)$shift['shift_number'],
                        'urgent'  => ($minutesUntil <= 10),
                    ];
                } else {
                    $lateMin = abs($minutesUntil);
                    $result['alerts'][] = [
                        'type'    => 'shift_late',
                        'icon'    => '🚨',
                        'title'   => 'تأخرت عن الوردية!',
                        'message' => 'بدأت ورديتك منذ ' . $lateMin . ' دقيقة ولم تسجل الحضور بعد.',
                        'shift'   => (int)$shift['shift_number'],
                        'urgent'  => true,
                    ];
                }
                break; // show only one shift alert
            }
        }
    } catch (Exception $e) { /* silent */ }
}

// ── 5. إعلانات جديدة (آخر ساعة) ──
try {
    $stmt = db()->prepare("
        SELECT id, title, content, icon, priority 
        FROM announcements 
        WHERE is_active = 1 
          AND (target_branches IS NULL OR target_branches = '[]' OR target_branches LIKE ?)
          AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY created_at DESC LIMIT 3
    ");
    $branchPattern = $branchId ? '%"' . (int)$branchId . '"%' : '%';
    $stmt->execute([$branchPattern]);
    $result['new_announcements'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $result['new_announcements'] = [];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
