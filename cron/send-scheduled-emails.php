<?php
// ================================================================
// cron/send-scheduled-emails.php - إرسال المراسلات المجدولة
// ================================================================
// يفحص المراسلات المجدولة ويرسلها باستخدام SMTP
// 
// التشغيل من CLI:
//   every-15-min: /usr/bin/php /home/u307296675/domains/sarh.io/public_html/cron/send-scheduled-emails.php
// 
// التشغيل من المتصفح (مع جلسة أدمن):
//   https://sarh.io/cron/send-scheduled-emails.php?send_id=1
// ================================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mail.php';
require_once __DIR__ . '/../includes/email-templates.php';

// حماية من الطلبات العشوائية
$cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '';
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    $hasSecret = !empty($cronSecret) && isset($_GET['secret']) && hash_equals($cronSecret, $_GET['secret']);
    $hasSession = false;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $hasSession = !empty($_SESSION['admin_id']);
    
    if (!$hasSecret && !$hasSession) {
        http_response_code(403);
        exit(json_encode(['error' => 'Access denied']));
    }
    
    header('Content-Type: application/json; charset=utf-8');
}

$now = new DateTime();
$currentHour = (int)$now->format('H');
$currentMinute = (int)$now->format('i');
$currentDayOfWeek = (int)$now->format('w');
$currentDayOfMonth = (int)$now->format('d');
$today = $now->format('Y-m-d');

$log = [];
$log[] = "=== بدء فحص المراسلات المجدولة ===";
$log[] = "الوقت الحالي: " . $now->format('Y-m-d H:i:s');

// === إرسال يدوي لمراسلة محددة ===
if (isset($_GET['send_id'])) {
    $sendId = (int)$_GET['send_id'];
    $stmt = db()->prepare("SELECT * FROM scheduled_emails WHERE id = ?");
    $stmt->execute([$sendId]);
    $schedule = $stmt->fetch();
    
    if (!$schedule) {
        $result = ['success' => false, 'message' => 'المراسلة غير موجودة'];
    } else {
        $result = sendScheduledReport($schedule, $today);
        if ($result['success']) {
            db()->prepare("UPDATE scheduled_emails SET last_sent_at = NOW() WHERE id = ?")
                ->execute([$sendId]);
            logSend($sendId, $schedule['recipients'], $result['subject'], 'sent');
        } else {
            logSend($sendId, $schedule['recipients'], $result['subject'] ?? 'Unknown', 'failed', $result['error']);
        }
    }
    
    if (!$isCli) {
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        echo ($result['success'] ? '✓' : '✗') . ' ' . ($result['message'] ?? $result['error'] ?? '') . "\n";
    }
    exit;
}

// === إرسال اختبار ===
if (isset($_GET['test_email'])) {
    $testTo = filter_var($_GET['test_email'], FILTER_VALIDATE_EMAIL);
    if (!$testTo) {
        echo json_encode(['success' => false, 'message' => 'إيميل غير صالح'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $testType = $_GET['test_type'] ?? 'summary';
    $testFreq = $_GET['frequency'] ?? 'daily';
    $range = getDateRangeForFrequency($testFreq, $today);
    $testData = generateReport($testType, '{}', $range['start'], $range['end']);
    $testTitle = 'رسالة اختبارية - ' . getReportTypeLabel($testType);
    
    $htmlBody = buildReportEmail($testType, $testData ?? [], $testTitle, $range['periodLabel']);
    $result = sendEmail([$testTo], $testTitle . ' - ' . $range['label'], $htmlBody);
    
    echo json_encode([
        'success' => $result['success'],
        'message' => $result['success'] ? 'تم إرسال رسالة الاختبار بنجاح إلى ' . $testTo : 'فشل الإرسال: ' . $result['error']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// === اختبار اتصال SMTP ===
if (isset($_GET['test_smtp'])) {
    $result = testSmtpConnection();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// === معاينة قالب ===
if (isset($_GET['preview'])) {
    header('Content-Type: text/html; charset=utf-8');
    $previewType = $_GET['preview'];
    $previewFreq = $_GET['frequency'] ?? 'daily';
    $range = getDateRangeForFrequency($previewFreq, $today);
    $previewData = generateReport($previewType, '{}', $range['start'], $range['end']);
    $previewTitle = 'معاينة - ' . getReportTypeLabel($previewType);
    echo buildReportEmail($previewType, $previewData ?? [], $previewTitle, $range['periodLabel']);
    exit;
}

// === المعالجة العادية - Cron ===
try {
    $stmt = db()->prepare("SELECT * FROM scheduled_emails WHERE is_active = 1 ORDER BY send_time ASC");
    $stmt->execute();
    $schedules = $stmt->fetchAll();
    
    $log[] = "تم العثور على " . count($schedules) . " مراسلة نشطة";
    $sent = 0;
    $failed = 0;

    foreach ($schedules as $schedule) {
        $shouldSend = false;
        $sendTime = new DateTime($schedule['send_time']);
        $sendHour = (int)$sendTime->format('H');
        $sendMinute = (int)$sendTime->format('i');

        $timeDiff = abs(($currentHour * 60 + $currentMinute) - ($sendHour * 60 + $sendMinute));
        if ($timeDiff > 15) continue;

        $alreadySent = !empty($schedule['last_sent_at']) && date('Y-m-d', strtotime($schedule['last_sent_at'])) === $today;
        
        switch ($schedule['frequency']) {
            case 'daily':
                $shouldSend = !$alreadySent;
                break;
            case 'weekly':
                $shouldSend = ($currentDayOfWeek == $schedule['day_of_week']) && !$alreadySent;
                break;
            case 'monthly':
                $shouldSend = ($currentDayOfMonth == $schedule['day_of_month']) && !$alreadySent;
                break;
        }

        if (!$shouldSend) continue;

        $log[] = "";
        $log[] = "--- إرسال: {$schedule['title']} ---";
        
        try {
            $result = sendScheduledReport($schedule, $today);
            
            if ($result['success']) {
                db()->prepare("UPDATE scheduled_emails SET last_sent_at = NOW() WHERE id = ?")
                    ->execute([$schedule['id']]);
                logSend($schedule['id'], $schedule['recipients'], $result['subject'], 'sent');
                $log[] = "✓ تم الإرسال بنجاح إلى: {$schedule['recipients']}";
                $sent++;
            } else {
                logSend($schedule['id'], $schedule['recipients'], $result['subject'] ?? '', 'failed', $result['error']);
                $log[] = "✗ فشل الإرسال: " . $result['error'];
                $failed++;
            }
        } catch (Exception $ex) {
            $log[] = "✗ خطأ: " . $ex->getMessage();
            error_log("Scheduled email error (ID {$schedule['id']}): " . $ex->getMessage());
            $failed++;
        }
    }

    $log[] = "";
    $log[] = "=== انتهى | أُرسل: {$sent} | فشل: {$failed} ===";

} catch (PDOException $e) {
    $log[] = "خطأ في قاعدة البيانات: " . $e->getMessage();
    error_log("Scheduled emails DB error: " . $e->getMessage());
}

if (!$isCli) {
    echo json_encode([
        'success' => true,
        'sent' => $sent ?? 0,
        'failed' => $failed ?? 0,
        'log' => $log
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo implode("\n", $log) . "\n";
}

// ============================================================
// الدوال
// ============================================================

function sendScheduledReport(array $schedule, string $today): array {
    try {
        $reportType = $schedule['report_type'];
        $frequency = $schedule['frequency'] ?? 'daily';
        $recipients = array_filter(array_map('trim', explode(',', $schedule['recipients'])));
        
        if (empty($recipients)) {
            return ['success' => false, 'error' => 'لا يوجد مستلمون', 'subject' => ''];
        }
        
        // تحديد فترة التقرير حسب التكرار
        $range = getDateRangeForFrequency($frequency, $today);
        
        $reportData = generateReport($reportType, $schedule['filters'] ?? '{}', $range['start'], $range['end']);
        $subject = $schedule['title'] . ' - ' . $range['label'];
        $htmlBody = buildReportEmail($reportType, $reportData ?? [], $schedule['title'], $range['periodLabel']);
        
        $result = sendEmail($recipients, $subject, $htmlBody);
        
        return [
            'success' => $result['success'],
            'subject' => $subject,
            'error' => $result['error'] ?? null,
            'message' => $result['success'] ? 'تم الإرسال بنجاح' : 'فشل الإرسال'
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'subject' => ''];
    }
}

/**
 * حساب فترة التقرير حسب تكرار الإرسال
 */
function getDateRangeForFrequency(string $frequency, string $today): array {
    $dt = new DateTime($today);
    switch ($frequency) {
        case 'weekly':
            $end = $dt->format('Y-m-d');
            $start = (clone $dt)->modify('-6 days')->format('Y-m-d');
            $label = $start . ' — ' . $end;
            $periodLabel = "أسبوعي: من {$start} إلى {$end}";
            break;
        case 'monthly':
            $start = $dt->format('Y-m-01');
            $end = $dt->format('Y-m-t');
            $month = $dt->format('Y-m');
            $label = $month;
            $periodLabel = "شهري: " . $month;
            break;
        default: // daily
            $start = $today;
            $end = $today;
            $label = $today;
            $periodLabel = "يومي: " . $today;
            break;
    }
    return ['start' => $start, 'end' => $end, 'label' => $label, 'periodLabel' => $periodLabel];
}

function buildReportEmail(string $type, $data, string $title, string $periodLabel = ''): string {
    $d = is_array($data) ? $data : [];
    switch ($type) {
        case 'daily':    return buildDailyReport($d, $title, $periodLabel);
        case 'late':     return buildLateReport($d, $title, $periodLabel);
        case 'absent':   return buildAbsentReport($d, $title, $periodLabel);
        case 'overtime': return buildOvertimeReport($d, $title, $periodLabel);
        case 'monthly':  return buildMonthlyReport($d, $title, $periodLabel);
        case 'summary':  return buildSummaryReport($d, $title, $periodLabel);
        case 'payroll':  return buildPayrollReport($d, $title, $periodLabel);
        default:         return getBaseTemplate($title, 'تقرير', '<p>نوع التقرير غير معروف</p>', 'default', $periodLabel);
    }
}

function generateReport(string $type, string $filtersJson, string $startDate, string $endDate = '') {
    if (empty($endDate)) $endDate = $startDate;
    switch ($type) {
        case 'daily':    return getDailyAttendanceReport($startDate, $endDate);
        case 'late':     return getLateEmployeesReport($startDate, $endDate);
        case 'absent':   return getAbsentEmployeesReport($startDate, $endDate);
        case 'overtime': return getOvertimeReport($startDate, $endDate);
        case 'monthly':  return getMonthlyReport($startDate, $endDate);
        case 'summary':  return getSummaryReport($startDate, $endDate);
        case 'payroll':  return getPayrollReport($startDate, $endDate);
        default:         return null;
    }
}

function getDailyAttendanceReport(string $startDate, string $endDate): array {
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number, b.name AS branch_name,
            a.type, a.timestamp, a.late_minutes, a.attendance_date
        FROM attendances a
        JOIN employees e ON a.employee_id = e.id
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE a.attendance_date BETWEEN ? AND ? AND a.type IN ('in','out')
        ORDER BY a.attendance_date ASC, a.timestamp ASC LIMIT 500
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

function getLateEmployeesReport(string $startDate, string $endDate): array {
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number, b.name AS branch_name,
            a.timestamp, a.late_minutes, a.attendance_date
        FROM attendances a
        JOIN employees e ON a.employee_id = e.id
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE a.attendance_date BETWEEN ? AND ? AND a.type = 'in' AND a.late_minutes > 0
        ORDER BY a.late_minutes DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

function getAbsentEmployeesReport(string $startDate, string $endDate): array {
    // الغائبون = من لم يسجل أي حضور خلال الفترة كاملة
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number, b.name AS branch_name
        FROM employees e
        LEFT JOIN branches b ON e.branch_id = b.id
        WHERE e.is_active = 1 AND e.deleted_at IS NULL
          AND e.id NOT IN (SELECT DISTINCT employee_id FROM attendances WHERE attendance_date BETWEEN ? AND ?)
        ORDER BY e.name
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

function getOvertimeReport(string $startDate, string $endDate): array {
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number, b.name AS branch_name,
            SUM(TIMESTAMPDIFF(MINUTE, os.timestamp, COALESCE(oe.timestamp, NOW()))) AS overtime_minutes
        FROM attendances os
        JOIN employees e ON os.employee_id = e.id
        LEFT JOIN branches b ON e.branch_id = b.id
        LEFT JOIN attendances oe ON oe.employee_id = os.employee_id 
            AND oe.attendance_date = os.attendance_date AND oe.type = 'overtime-end'
        WHERE os.attendance_date BETWEEN ? AND ? AND os.type = 'overtime-start'
        GROUP BY e.id, e.name, b.name
        HAVING overtime_minutes > 0
        ORDER BY overtime_minutes DESC
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

function getMonthlyReport(string $startDate, string $endDate): array {
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number,
            COUNT(DISTINCT CASE WHEN a.type = 'in' THEN a.attendance_date END) AS days_attended,
            SUM(CASE WHEN a.type = 'in' AND a.late_minutes > 0 THEN 1 ELSE 0 END) AS late_count,
            SUM(CASE WHEN a.type = 'in' THEN COALESCE(a.late_minutes, 0) ELSE 0 END) AS total_late_minutes
        FROM employees e
        LEFT JOIN attendances a ON e.id = a.employee_id AND a.attendance_date BETWEEN ? AND ?
        WHERE e.is_active = 1 AND e.deleted_at IS NULL
        GROUP BY e.id, e.name
        ORDER BY e.name LIMIT 200
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll();
}

function getSummaryReport(string $startDate, string $endDate): array {
    $total = (int)db()->query("SELECT COUNT(*) FROM employees WHERE is_active = 1 AND deleted_at IS NULL")->fetchColumn();
    $stmt = db()->prepare("SELECT COUNT(DISTINCT employee_id) FROM attendances WHERE attendance_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $present = (int)$stmt->fetchColumn();
    $stmt = db()->prepare("SELECT COUNT(DISTINCT employee_id) FROM attendances WHERE attendance_date BETWEEN ? AND ? AND type = 'in' AND late_minutes > 0");
    $stmt->execute([$startDate, $endDate]);
    $late = (int)$stmt->fetchColumn();
    return ['total_employees' => $total, 'present' => $present, 'absent' => $total - $present, 'late' => $late];
}

function getPayrollReport(string $startDate, string $endDate): array {
    $month = date('Y-m', strtotime($startDate));
    $workingDays = getWorkingDaysInMonth($month);
    $stmt = db()->prepare("
        SELECT e.name, e.id AS employee_number,
            COUNT(DISTINCT CASE WHEN a.type = 'in' THEN a.attendance_date END) AS days_attended,
            SUM(CASE WHEN a.type = 'in' THEN COALESCE(a.late_minutes, 0) ELSE 0 END) AS total_late_minutes,
            0 AS total_overtime
        FROM employees e
        LEFT JOIN attendances a ON e.id = a.employee_id AND a.attendance_date BETWEEN ? AND ?
        WHERE e.is_active = 1 AND e.deleted_at IS NULL
        GROUP BY e.id, e.name
        ORDER BY e.name LIMIT 200
    ");
    $stmt->execute([$startDate, $endDate]);
    $results = $stmt->fetchAll();
    foreach ($results as &$row) {
        $row['absent_days'] = max(0, $workingDays - (int)$row['days_attended']);
    }
    return $results;
}

function getWorkingDaysInMonth(string $month): int {
    $start = new DateTime($month . '-01');
    $end = (clone $start)->modify('last day of this month');
    $days = 0;
    while ($start <= $end) {
        $dow = (int)$start->format('w');
        if ($dow !== 5 && $dow !== 6) $days++;
        $start->modify('+1 day');
    }
    return $days;
}

function logSend(int $scheduleId, string $recipients, string $subject, string $status, ?string $error = null): void {
    try {
        db()->prepare("INSERT INTO email_send_log (schedule_id, recipients, subject, status, error_message, sent_at) VALUES (?, ?, ?, ?, ?, NOW())")
            ->execute([$scheduleId, $recipients, $subject, $status, $error]);
    } catch (PDOException $e) {
        error_log("Failed to log email send: " . $e->getMessage());
    }
}

function getReportTypeLabel(string $type): string {
    $types = [
        'daily' => 'تقرير الحضور اليومي', 'late' => 'تقرير المتأخرين',
        'absent' => 'تقرير الغائبين', 'overtime' => 'تقرير العمل الإضافي',
        'monthly' => 'التقرير الشهري', 'summary' => 'ملخص الحضور', 'payroll' => 'تقرير الرواتب',
    ];
    return $types[$type] ?? $type;
}
