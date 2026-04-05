<?php
/**
 * ================================================================
 * cron/auto-checkout.php - Auto Check-out at shift end
 * ================================================================
 * يسجل انصراف تلقائي عند انتهاء كل وردية لمن لم يسجل
 * يدعم الورديات المتعددة: يربط كل تسجيل حضور بانصراف خاص به
 * 
 * التشغيل: كل دقيقة عبر cron
 * مثال: * * * * * /usr/bin/php /path/to/cron/auto-checkout.php
 * ================================================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// لو غير CLI، منع التشغيل إلا برمز سري من متغير البيئة
$cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '';
if (php_sapi_name() !== 'cli') {
    if (empty($cronSecret) || !isset($_GET['secret']) || !hash_equals($cronSecret, $_GET['secret'])) {
        http_response_code(403);
        exit('Access denied');
    }
}

$now = new DateTime();
$today = $now->format('Y-m-d');

try {
    // ================================================================
    // جلب تسجيلات الحضور التي ليس لها انصراف مقابل
    // الشرط: لا يوجد تسجيل انصراف بعد وقت الحضور لنفس الموظف ونفس التاريخ
    // ================================================================
    $stmt = db()->prepare("
        SELECT
            ci.id AS checkin_id,
            ci.employee_id,
            ci.timestamp AS checkin_time,
            ci.attendance_date,
            ci.latitude,
            ci.longitude,
            e.name,
            e.branch_id
        FROM attendances ci
        INNER JOIN employees e ON ci.employee_id = e.id
        WHERE ci.type = 'in'
          AND ci.attendance_date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
          AND e.is_active = 1
          AND e.deleted_at IS NULL
          AND NOT EXISTS (
              SELECT 1 FROM attendances co
              WHERE co.employee_id = ci.employee_id
                AND co.type = 'out'
                AND co.attendance_date = ci.attendance_date
                AND co.timestamp > ci.timestamp
          )
        ORDER BY ci.employee_id, ci.timestamp
    ");
    $stmt->execute();
    $checkins = $stmt->fetchAll();

    $autoCheckouts = 0;
    $skipped = 0;

    foreach ($checkins as $ci) {
        try {
            // جلب ورديات الفرع
            $shiftStmt = db()->prepare("SELECT shift_number, shift_start, shift_end FROM branch_shifts WHERE branch_id = ? AND is_active = 1 ORDER BY shift_number");
            $shiftStmt->execute([$ci['branch_id']]);
            $shifts = $shiftStmt->fetchAll();

            if (empty($shifts)) {
                $shifts = [['shift_number' => 1, 'shift_start' => getSystemSetting('work_start_time', '08:00'), 'shift_end' => getSystemSetting('work_end_time', '16:00')]];
            }

            // تحديد وردية هذا التسجيل
            $checkinTime = date('H:i', strtotime($ci['checkin_time']));
            $shiftNum = assignTimeToShift($checkinTime, $shifts);

            // جلب بيانات الوردية المطابقة
            $matchedShift = null;
            foreach ($shifts as $s) {
                if ((int)$s['shift_number'] === $shiftNum) { $matchedShift = $s; break; }
            }
            if (!$matchedShift) $matchedShift = $shifts[0];

            $shiftEnd = $matchedShift['shift_end'];

            // بناء وقت الانصراف المتوقع
            $expectedCheckout = new DateTime($ci['attendance_date'] . ' ' . $shiftEnd);
            $checkInDT = new DateTime($ci['checkin_time']);

            // إذا وقت الانصراف قبل وقت الدخول → يعني تجاوز منتصف الليل
            if ($expectedCheckout <= $checkInDT) {
                $expectedCheckout->modify('+1 day');
            }

            // هل انتهت الوردية؟
            if ($now >= $expectedCheckout) {
                // استخدام وقت نهاية الوردية كوقت الانصراف (وليس NOW)
                $checkoutTimestamp = $expectedCheckout->format('Y-m-d H:i:s');

                $insertStmt = db()->prepare("
                    INSERT INTO attendances (
                        employee_id, type, timestamp, attendance_date,
                        latitude, longitude, location_accuracy,
                        ip_address, user_agent, notes
                    ) VALUES (
                        :emp_id, 'out', :ts, :att_date,
                        :lat, :lon, 0,
                        'AUTO', 'AUTO-CHECKOUT-CRON', :notes
                    )
                ");
                $insertStmt->execute([
                    'emp_id'   => $ci['employee_id'],
                    'ts'       => $checkoutTimestamp,
                    'att_date' => $ci['attendance_date'],
                    'lat'      => $ci['latitude'],
                    'lon'      => $ci['longitude'],
                    'notes'    => "انصراف تلقائي - وردية {$shiftNum}"
                ]);
                $autoCheckouts++;
                echo "[{$now->format('Y-m-d H:i:s')}] ✅ AUTO-CHECKOUT Shift {$shiftNum}: {$ci['name']} (checkin #{$ci['checkin_id']})\n";
            } else {
                $skipped++;
            }
        } catch (Exception $e) {
            echo "[{$now->format('Y-m-d H:i:s')}] ❌ Error for {$ci['name']}: " . $e->getMessage() . "\n";
        }
    }

    echo "[{$now->format('Y-m-d H:i:s')}] 📊 Done: {$autoCheckouts} auto-checkouts, {$skipped} pending\n";

} catch (Exception $e) {
    echo "[{$now->format('Y-m-d H:i:s')}] ❌ Error: " . $e->getMessage() . "\n";
}

echo "[{$now->format('Y-m-d H:i:s')}] ✅ Cron job completed\n";
