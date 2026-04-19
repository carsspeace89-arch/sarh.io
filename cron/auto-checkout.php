<?php
/**
 * ================================================================
 * cron/auto-checkout.php - Shift-Aware Auto Check-out (v5.0 Queue-Aware)
 * ================================================================
 * Dispatches auto-checkout jobs to the queue when Redis is available.
 * Falls back to direct DB operations for reliability.
 * 
 * المنطق:
 *   IF no manual checkout at shift_end + grace_minutes → auto_checkout
 *   status = 'auto_checkout', shift_id مرتبط، closed_by_checkin_id مرتبط
 * 
 * الحماية من التكرار:
 *   - NOT EXISTS مع co.timestamp > ci.timestamp (لكل check-in)
 *   - Lock file يمنع التشغيل المتزامن
 * 
 * التشغيل: كل دقيقة عبر cron
 * ================================================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// ── Access Control ──
$cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '';
if (php_sapi_name() !== 'cli') {
    $provided = $_SERVER['HTTP_X_CRON_SECRET']
        ?? ($_POST['cron_secret'] ?? ($_GET['secret'] ?? ''));
    if (empty($cronSecret) || !hash_equals($cronSecret, $provided)) {
        http_response_code(403);
        exit('Access denied');
    }
}

// ── Structured Logging ──
$useLogger = class_exists('\App\Core\Logger');
$log = function(string $msg, array $ctx = []) use ($useLogger) {
    if ($useLogger) {
        \App\Core\Logger::queue($msg, $ctx);
    }
    // Always echo for cron output
    echo "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
};

// ── Lock File: Prevent concurrent executions ──
$lockFile = sys_get_temp_dir() . '/sarh_cron_checkout.lock';
$lockFp = fopen($lockFile, 'c');
if (!flock($lockFp, LOCK_EX | LOCK_NB)) {
    $log("Another instance is running. Exiting.");
    fclose($lockFp);
    exit(0);
}
ftruncate($lockFp, 0);
fwrite($lockFp, (string)getmypid());

// ── Check if queue dispatch is available ──
$useQueue = class_exists('\App\Queue\QueueManager')
    && class_exists('\App\Queue\Jobs\AutoCheckoutJob')
    && \App\Core\Redis::isAvailable();

$now = new DateTime();
$graceMinutes = (int) getSystemSetting('auto_checkout_grace_minutes', '15');

try {
    $stmt = db()->prepare("
        SELECT
            ci.id        AS checkin_id,
            ci.employee_id,
            ci.timestamp AS checkin_time,
            ci.attendance_date,
            ci.latitude,
            ci.longitude,
            ci.shift_id  AS existing_shift_id,
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
    $queued = 0;
    $skipped = 0;

    foreach ($checkins as $ci) {
        try {
            // ── جلب ورديات الفرع ──
            $shiftStmt = db()->prepare("
                SELECT id, shift_number, shift_start, shift_end
                FROM branch_shifts
                WHERE branch_id = ? AND is_active = 1
                ORDER BY shift_number
            ");
            $shiftStmt->execute([$ci['branch_id']]);
            $shifts = $shiftStmt->fetchAll();

            if (empty($shifts)) {
                $shifts = [[
                    'id' => null,
                    'shift_number' => 1,
                    'shift_start'  => getSystemSetting('work_start_time', '08:00'),
                    'shift_end'    => getSystemSetting('work_end_time', '16:00')
                ]];
            }

            // ── تحديد وردية هذا التسجيل ──
            $checkinTime = date('H:i', strtotime($ci['checkin_time']));
            $shiftNum = assignTimeToShift($checkinTime, $shifts);

            $matchedShift = null;
            foreach ($shifts as $s) {
                if ((int)$s['shift_number'] === $shiftNum) { $matchedShift = $s; break; }
            }
            if (!$matchedShift) $matchedShift = $shifts[0];

            $shiftId = $ci['existing_shift_id'] ?? ($matchedShift['id'] ?? null);

            // ── بناء وقت الانصراف: shift_end + grace ──
            $expectedCheckout = new DateTime($ci['attendance_date'] . ' ' . $matchedShift['shift_end']);
            $checkInDT = new DateTime($ci['checkin_time']);

            if ($expectedCheckout <= $checkInDT) {
                $expectedCheckout->modify('+1 day');
            }

            $triggerTime = clone $expectedCheckout;
            $triggerTime->modify("+{$graceMinutes} minutes");

            // ── هل حان وقت الإغلاق التلقائي؟ ──
            if ($now >= $triggerTime) {
                $checkoutTimestamp = $expectedCheckout->format('Y-m-d H:i:s');

                // ── Try queue dispatch first ──
                if ($useQueue) {
                    $job = new \App\Queue\Jobs\AutoCheckoutJob([
                        'checkin_id'    => $ci['checkin_id'],
                        'employee_id'   => $ci['employee_id'],
                        'checkout_time' => $checkoutTimestamp,
                        'attendance_date' => $ci['attendance_date'],
                        'latitude'      => $ci['latitude'],
                        'longitude'     => $ci['longitude'],
                        'shift_id'      => $shiftId,
                        'shift_number'  => $shiftNum,
                        'grace_minutes' => $graceMinutes,
                    ]);
                    \App\Queue\QueueManager::getInstance()->dispatch($job);
                    $queued++;
                    $log("⏳ QUEUED auto-checkout: {$ci['name']} (checkin #{$ci['checkin_id']}) shift {$shiftNum}");
                    continue;
                }

                // ── Direct DB fallback (with transaction) ──
                db()->beginTransaction();
                try {
                    $guardStmt = db()->prepare("
                        SELECT id FROM attendances
                        WHERE employee_id = ? AND type = 'out'
                          AND attendance_date = ? AND timestamp > ?
                        LIMIT 1
                    ");
                    $guardStmt->execute([$ci['employee_id'], $ci['attendance_date'], $ci['checkin_time']]);
                    if ($guardStmt->fetch()) {
                        db()->rollBack();
                        $skipped++;
                        continue;
                    }

                    $insertStmt = db()->prepare("
                        INSERT INTO attendances (
                            employee_id, type, timestamp, attendance_date,
                            latitude, longitude, location_accuracy,
                            ip_address, user_agent, notes,
                            status, shift_id, closed_by_checkin_id
                        ) VALUES (
                            :emp_id, 'out', :ts, :att_date,
                            :lat, :lon, 0,
                            'AUTO', 'AUTO-CHECKOUT-CRON', :notes,
                            'auto_checkout', :shift_id, :ci_id
                        )
                    ");
                    $insertStmt->execute([
                        'emp_id'   => $ci['employee_id'],
                        'ts'       => $checkoutTimestamp,
                        'att_date' => $ci['attendance_date'],
                        'lat'      => $ci['latitude'],
                        'lon'      => $ci['longitude'],
                        'notes'    => "انصراف تلقائي - وردية {$shiftNum} (بعد {$graceMinutes} دقيقة سماح)",
                        'shift_id' => $shiftId,
                        'ci_id'    => $ci['checkin_id']
                    ]);
                    db()->commit();
                    $autoCheckouts++;
                } catch (\Throwable $txErr) {
                    db()->rollBack();
                    throw $txErr;
                }
                $log("✅ AUTO-CHECKOUT Shift {$shiftNum}: {$ci['name']} (checkin #{$ci['checkin_id']})");
            } else {
                $skipped++;
            }
        } catch (Exception $e) {
            $log("❌ Error for {$ci['name']}: " . $e->getMessage(), [
                'employee_id' => $ci['employee_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    $log("📊 Done: {$autoCheckouts} direct, {$queued} queued, {$skipped} pending (grace={$graceMinutes}min)");

} catch (Exception $e) {
    $log("❌ Fatal error: " . $e->getMessage());
}

// ── Release lock ──
flock($lockFp, LOCK_UN);
fclose($lockFp);

$log("✅ Cron job completed");
