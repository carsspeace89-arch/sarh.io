<?php
// =============================================================
// includes/auth.php - مصادقة المديرين
// =============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * التحقق من تسجيل دخول المدير، وإعادة توجيهه إذا لم يكن مسجلاً
 */
function requireAdminLogin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * محاولة تسجيل دخول مدير
 */
function adminLogin(string $username, string $password): array {
    $stmt = db()->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();

    // دائماً نستدعي password_verify لمنع Timing Attacks (حتى لو المستخدم غير موجود)
    $dummyHash = '$2y$10$dummyhashtopreventtimingattacksXXXXXXXXXXXXXXXXXXXXXXX';
    $hashToCheck = $admin ? $admin['password_hash'] : $dummyHash;
    $passwordValid = password_verify($password, $hashToCheck);

    if (!$admin || !$passwordValid) {
        return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة'];
    }

    // تجديد معرف الجلسة لأمان أفضل
    session_regenerate_id(true);

    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name']     = $admin['full_name'];

    // تذكرني — تمديد الجلسة لـ 30 يوم
    if (!empty($_POST['remember_me'])) {
        $ttl = 2592000; // 30 يوم
        setcookie('remember_admin', '1', time() + $ttl, '/', '', true, true);
        ini_set('session.cookie_lifetime', $ttl);
        ini_set('session.gc_maxlifetime', $ttl);
        session_set_cookie_params($ttl);
    }

    // تحديث آخر تسجيل دخول
    db()->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

    return ['success' => true, 'message' => 'تم تسجيل الدخول بنجاح'];
}

/**
 * تسجيل خروج المدير
 */
function adminLogout(): void {
    $_SESSION = [];
    // مسح كوكي تذكرني
    setcookie('remember_admin', '', time() - 3600, '/', '', true, true);
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

/**
 * هل المدير مسجل دخوله؟
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

// =============================================================
// Auto-checkout trigger (piggyback) — يعمل مرة كل دقيقة
// عند فتح أي صفحة إدارة، يتحقق ويسجل انصراف تلقائي
// =============================================================
function triggerAutoCheckout(): void {
    $lockFile = sys_get_temp_dir() . '/sarh_auto_checkout.lock';

    // تحقق من آخر تشغيل (مرة كل 60 ثانية كحد أدنى)
    if (file_exists($lockFile)) {
        $lastRun = (int) file_get_contents($lockFile);
        if (time() - $lastRun < 60) {
            return; // لم يحن الوقت بعد
        }
    }

    // تحديث وقت آخر تشغيل فوراً (lock)
    file_put_contents($lockFile, (string) time());

    try {
        $now = new DateTime();
        $currentTime = $now->format('H:i:s');

        // جلب الموظفين بدون تسجيل انصراف اليوم أو أمس
        $stmt = db()->prepare("
            SELECT DISTINCT
                e.id AS employee_id,
                e.branch_id,
                ci.timestamp AS checkin_time,
                ci.attendance_date,
                ci.latitude,
                ci.longitude
            FROM employees e
            INNER JOIN attendances ci ON e.id = ci.employee_id
            WHERE ci.type = 'in'
              AND ci.attendance_date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND e.is_active = 1
              AND e.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM attendances co
                  WHERE co.employee_id = e.id
                    AND co.type = 'out'
                    AND co.attendance_date = ci.attendance_date
              )
        ");
        $stmt->execute();
        $employees = $stmt->fetchAll();

        if (empty($employees)) {
            return;
        }

        foreach ($employees as $emp) {
            $schedule = getBranchSchedule($emp['branch_id']);
            $shifts   = $schedule['shifts'] ?? [];

            $checkinMin = timeToMinutes(date('H:i', strtotime($emp['checkin_time'])));
            $coEnd = $schedule['work_end_time'];

            foreach ($shifts as $shift) {
                $shiftStart  = timeToMinutes($shift['shift_start']);
                $shiftEnd    = timeToMinutes($shift['shift_end']);
                $earlyWindow = ($shiftStart - 90 + 1440) % 1440;

                if ($shiftEnd < $earlyWindow) {
                    if ($checkinMin >= $earlyWindow || $checkinMin <= $shiftEnd) {
                        $coEnd = $shift['shift_end'];
                        break;
                    }
                } else {
                    if ($checkinMin >= $earlyWindow && $checkinMin <= $shiftEnd) {
                        $coEnd = $shift['shift_end'];
                        break;
                    }
                }
            }

            $expectedCheckout = new DateTime($emp['attendance_date'] . ' ' . $coEnd);
            $checkInDT = new DateTime($emp['checkin_time']);

            if ($expectedCheckout <= $checkInDT) {
                $expectedCheckout->modify('+1 day');
            }

            if ($now >= $expectedCheckout) {
                $insertStmt = db()->prepare("
                    INSERT INTO attendances (
                        employee_id, type, timestamp, attendance_date,
                        latitude, longitude, location_accuracy,
                        ip_address, user_agent, notes
                    ) VALUES (
                        :emp_id, 'out', NOW(), :att_date,
                        :lat, :lon, 0,
                        'AUTO', 'AUTO-CHECKOUT', 'انصراف تلقائي عند انتهاء الوردية'
                    )
                ");
                $insertStmt->execute([
                    'emp_id'   => $emp['employee_id'],
                    'att_date' => $emp['attendance_date'],
                    'lat'      => $emp['latitude'],
                    'lon'      => $emp['longitude']
                ]);
            }
        }
    } catch (Exception $e) {
        // صامت — لا نعطل صفحة الإدارة
    }
}

// تشغيل تلقائي عند تحميل الصفحة (فقط للمديرين المسجلين)
if (isAdminLoggedIn()) {
    triggerAutoCheckout();
}
