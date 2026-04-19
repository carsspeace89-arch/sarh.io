<?php
// =============================================================
// admin/stars.php - نظام النجوم والمكافآت
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'نظام النجوم';
$activePage = 'stars';

// ── إعدادات النجوم ──
$starsPerEarly     = (int)getSystemSetting('stars_per_early_day', '1');
$starsDeductLate   = (int)getSystemSetting('stars_deduct_per_late_day', '1');
$earlyMinMinutes   = (int)getSystemSetting('stars_early_min_minutes', '5');
$lateMinMinutes    = (int)getSystemSetting('stars_late_min_minutes', '5');
$bonusThreshold    = (int)getSystemSetting('stars_bonus_threshold', '50');
$autoEnabled       = (int)getSystemSetting('stars_auto_enabled', '1');

// ── معالجة الطلبات POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    // إضافة/خصم نجوم يدوي
    if ($action === 'add_stars') {
        $empId  = (int)($_POST['employee_id'] ?? 0);
        $stars  = (int)($_POST['stars'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($empId <= 0 || $stars === 0 || $reason === '') {
            echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
            exit;
        }

        // التحقق من وجود الموظف
        $emp = db()->prepare("SELECT id, name, stars_balance FROM employees WHERE id = ? AND is_active = 1 AND deleted_at IS NULL");
        $emp->execute([$empId]);
        $emp = $emp->fetch();
        if (!$emp) {
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            exit;
        }

        try {
            db()->beginTransaction();

            // إدراج السجل
            $stmt = db()->prepare("INSERT INTO employee_stars (employee_id, stars, reason, source, admin_id) VALUES (?, ?, ?, 'manual', ?)");
            $stmt->execute([$empId, $stars, $reason, $_SESSION['admin_id']]);

            // تحديث الرصيد
            $newBalance = $emp['stars_balance'] + $stars;
            db()->prepare("UPDATE employees SET stars_balance = ? WHERE id = ?")->execute([$newBalance, $empId]);

            db()->commit();

            $type = $stars > 0 ? 'إضافة' : 'خصم';
            auditLog('stars_manual', "{$type} " . abs($stars) . " نجمة للموظف {$emp['name']}: {$reason}");

            echo json_encode([
                'success' => true,
                'message' => "تم {$type} " . abs($stars) . " نجمة بنجاح",
                'new_balance' => $newBalance
            ]);
        } catch (Exception $e) {
            db()->rollBack();
            echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
        exit;
    }

    // إعادة تعيين نجوم موظف
    if ($action === 'reset_stars') {
        $empId = (int)($_POST['employee_id'] ?? 0);
        $emp = db()->prepare("SELECT id, name, stars_balance FROM employees WHERE id = ? AND is_active = 1 AND deleted_at IS NULL");
        $emp->execute([$empId]);
        $emp = $emp->fetch();
        if (!$emp) {
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            exit;
        }

        try {
            db()->beginTransaction();

            if ($emp['stars_balance'] != 0) {
                db()->prepare("INSERT INTO employee_stars (employee_id, stars, reason, source, admin_id) VALUES (?, ?, 'إعادة تعيين الرصيد', 'reset', ?)")
                    ->execute([$empId, -$emp['stars_balance'], $_SESSION['admin_id']]);
            }
            db()->prepare("UPDATE employees SET stars_balance = 0 WHERE id = ?")->execute([$empId]);

            db()->commit();
            auditLog('stars_reset', "إعادة تعيين نجوم الموظف {$emp['name']} (كان: {$emp['stars_balance']})");
            echo json_encode(['success' => true, 'message' => 'تم إعادة تعيين النجوم']);
        } catch (Exception $e) {
            db()->rollBack();
            echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
        exit;
    }

    // حساب تلقائي من الحضور
    if ($action === 'auto_calculate') {
        $dateFrom = $_POST['date_from'] ?? date('Y-m-01');
        $dateTo   = $_POST['date_to'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo = date('Y-m-d');

        try {
            // جلب بيانات الحضور مع التبكير/التأخير
            $attStmt = db()->prepare("
                SELECT a.employee_id, a.attendance_date, a.early_minutes, a.late_minutes
                FROM attendances a
                JOIN employees e ON a.employee_id = e.id
                WHERE a.type = 'in'
                  AND a.attendance_date BETWEEN ? AND ?
                  AND e.is_active = 1
                  AND e.deleted_at IS NULL
                ORDER BY a.employee_id, a.attendance_date
            ");
            $attStmt->execute([$dateFrom, $dateTo]);
            $allAtt = $attStmt->fetchAll();

            // تجميع حسب الموظف واليوم (أول تسجيل فقط)
            $empDays = [];
            foreach ($allAtt as $a) {
                $key = $a['employee_id'] . '_' . $a['attendance_date'];
                if (!isset($empDays[$key])) {
                    $empDays[$key] = $a;
                }
            }

            // حساب النجوم
            $addedCount = 0;
            $starsAdded = 0;
            $starsDeducted = 0;

            db()->beginTransaction();

            foreach ($empDays as $day) {
                $eid  = $day['employee_id'];
                $date = $day['attendance_date'];
                $early = (int)$day['early_minutes'];
                $late  = (int)$day['late_minutes'];

                // تحقق: هل تم احتساب هذا اليوم مسبقاً؟
                $exists = db()->prepare("SELECT 1 FROM employee_stars WHERE employee_id = ? AND reference_date = ? AND source IN ('auto_early','auto_late')");
                $exists->execute([$eid, $date]);
                if ($exists->fetch()) continue;

                // نجوم تبكير
                if ($early >= $earlyMinMinutes) {
                    $s = $starsPerEarly;
                    db()->prepare("INSERT INTO employee_stars (employee_id, stars, reason, source, reference_date, admin_id) VALUES (?, ?, ?, 'auto_early', ?, ?)")
                        ->execute([$eid, $s, "تبكير {$early} دقيقة", $date, $_SESSION['admin_id']]);
                    db()->prepare("UPDATE employees SET stars_balance = stars_balance + ? WHERE id = ?")->execute([$s, $eid]);
                    $starsAdded += $s;
                    $addedCount++;
                }

                // خصم تأخير
                if ($late >= $lateMinMinutes) {
                    $s = -$starsDeductLate;
                    db()->prepare("INSERT INTO employee_stars (employee_id, stars, reason, source, reference_date, admin_id) VALUES (?, ?, ?, 'auto_late', ?, ?)")
                        ->execute([$eid, $s, "تأخير {$late} دقيقة", $date, $_SESSION['admin_id']]);
                    db()->prepare("UPDATE employees SET stars_balance = stars_balance + ? WHERE id = ?")->execute([$s, $eid]);
                    $starsDeducted += abs($s);
                    $addedCount++;
                }
            }

            db()->commit();
            auditLog('stars_auto', "حساب تلقائي من {$dateFrom} إلى {$dateTo}: +{$starsAdded} نجمة، -{$starsDeducted} خصم، {$addedCount} سجل");
            echo json_encode([
                'success' => true,
                'message' => "تم الحساب: +{$starsAdded} نجمة تبكير، -{$starsDeducted} خصم تأخير ({$addedCount} سجل جديد)"
            ]);
        } catch (Exception $e) {
            if (db()->inTransaction()) db()->rollBack();
            echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
        exit;
    }

    // حفظ الإعدادات
    if ($action === 'save_settings') {
        $settings = [
            'stars_per_early_day'       => max(1, (int)($_POST['stars_per_early_day'] ?? 1)),
            'stars_deduct_per_late_day' => max(1, (int)($_POST['stars_deduct_per_late_day'] ?? 1)),
            'stars_early_min_minutes'   => max(1, (int)($_POST['stars_early_min_minutes'] ?? 5)),
            'stars_late_min_minutes'    => max(1, (int)($_POST['stars_late_min_minutes'] ?? 5)),
            'stars_bonus_threshold'     => max(1, (int)($_POST['stars_bonus_threshold'] ?? 50)),
            'stars_auto_enabled'        => isset($_POST['stars_auto_enabled']) ? 1 : 0,
        ];
        try {
            foreach ($settings as $k => $v) {
                db()->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([(string)$v, $k]);
            }
            auditLog('stars_settings', 'تحديث إعدادات النجوم');
            echo json_encode(['success' => true, 'message' => 'تم حفظ الإعدادات']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

// ── الفلاتر ──
$branchFilter = (int)($_GET['branch_id'] ?? 0);
$searchName   = trim($_GET['search'] ?? '');
$sortBy       = $_GET['sort'] ?? 'balance';
$sortDir      = ($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

// ── جلب الموظفين مع أرصدتهم ──
$where = ["e.is_active = 1", "e.deleted_at IS NULL"];
$params = [];
if ($branchFilter > 0) { $where[] = "e.branch_id = ?"; $params[] = $branchFilter; }
if ($searchName !== '') { $where[] = "e.name LIKE ?"; $params[] = "%{$searchName}%"; }
$whereStr = implode(' AND ', $where);

$orderCol = match($sortBy) {
    'name'    => 'e.name',
    'branch'  => 'b.name',
    'balance' => 'e.stars_balance',
    default   => 'e.stars_balance'
};

$empStmt = db()->prepare("
    SELECT e.id, e.name, e.job_title, e.stars_balance, e.profile_photo,
           b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE {$whereStr}
    ORDER BY {$orderCol} {$sortDir}
");
$empStmt->execute($params);
$employees = $empStmt->fetchAll();

// ── إحصائيات ──
$totalStars    = array_sum(array_column($employees, 'stars_balance'));
$topEmployee   = !empty($employees) ? max(array_column($employees, 'stars_balance')) : 0;
$negativeCount = count(array_filter($employees, fn($e) => $e['stars_balance'] < 0));
$bonusCount    = count(array_filter($employees, fn($e) => $e['stars_balance'] >= $bonusThreshold));

// ── آخر العمليات ──
$recentStmt = db()->query("
    SELECT es.*, e.name AS employee_name, a.username AS admin_name
    FROM employee_stars es
    JOIN employees e ON es.employee_id = e.id
    LEFT JOIN admins a ON es.admin_id = a.id
    ORDER BY es.created_at DESC
    LIMIT 20
");
$recentLogs = $recentStmt->fetchAll();

// ── القوائم ──
$branchesList  = db()->query("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name")->fetchAll();
$employeesList = db()->query("SELECT id, name FROM employees WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
/* ── Stars Page Styles ── */
.stars-hero {
    background: linear-gradient(135deg, var(--royal-navy) 0%, #1a2d52 100%);
    border-radius: 16px; padding: 32px; margin-bottom: 24px;
    color: #fff; position: relative; overflow: hidden;
}
.stars-hero::before {
    content: '⭐'; position: absolute; right: 30px; top: 50%; transform: translateY(-50%);
    font-size: 6rem; opacity: .08;
}
.stars-hero h2 { margin: 0 0 6px; font-size: 1.5rem; color: var(--royal-gold-light); }
.stars-hero p { margin: 0; opacity: .7; font-size: .88rem; }

.stars-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; }
.star-stat {
    background: var(--surface1); border: 1px solid var(--border); border-radius: 12px;
    padding: 18px; text-align: center; position: relative; overflow: hidden;
}
.star-stat::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    border-radius: 3px 3px 0 0;
}
.star-stat.gold::before { background: linear-gradient(90deg, #c9a84c, #e8d9a0, #c9a84c); }
.star-stat.green::before { background: #10B981; }
.star-stat.red::before { background: #EF4444; }
.star-stat.blue::before { background: #3B82F6; }
.star-stat-icon { font-size: 1.8rem; margin-bottom: 6px; }
.star-stat-value { font-size: 1.6rem; font-weight: 900; color: var(--text1); }
.star-stat-label { font-size: .78rem; color: var(--text3); margin-top: 2px; }

.stars-actions-bar {
    display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 24px;
    padding: 16px; background: var(--surface1); border: 1px solid var(--border);
    border-radius: 12px;
}
.stars-actions-bar .btn-star {
    padding: 10px 20px; border-radius: 10px; border: none; cursor: pointer;
    font-weight: 700; font-size: .85rem; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px; transition: .2s;
}
.btn-star.gold { background: linear-gradient(135deg, #c9a84c, #a88a2a); color: #fff; }
.btn-star.gold:hover { box-shadow: 0 4px 15px rgba(201,168,76,.4); transform: translateY(-1px); }
.btn-star.navy { background: var(--royal-navy); color: #fff; }
.btn-star.navy:hover { box-shadow: 0 4px 15px rgba(15,27,51,.4); transform: translateY(-1px); }
.btn-star.green { background: #10B981; color: #fff; }
.btn-star.green:hover { box-shadow: 0 4px 15px rgba(16,185,129,.4); transform: translateY(-1px); }
.btn-star.outline {
    background: transparent; border: 2px solid var(--border); color: var(--text2);
}
.btn-star.outline:hover { border-color: var(--royal-gold); color: var(--royal-gold); }

/* Leaderboard Table */
.stars-table { width: 100%; border-collapse: collapse; }
.stars-table th {
    padding: 12px 14px; text-align: center; font-size: .78rem; font-weight: 700;
    color: var(--text3); text-transform: uppercase; letter-spacing: .04em;
    border-bottom: 2px solid var(--border); background: var(--surface2);
}
.stars-table td { padding: 12px 14px; text-align: center; border-bottom: 1px solid var(--border); }
.stars-table tbody tr:hover { background: rgba(201,168,76,.05); }

.emp-cell { display: flex; align-items: center; gap: 10px; text-align: right; }
.emp-cell img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--royal-gold); }
.emp-cell .emp-ph {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--royal-navy), #1a2d52);
    display: flex; align-items: center; justify-content: center;
    color: var(--royal-gold-light); font-weight: 800; font-size: .78rem;
}
.emp-cell .emp-info { line-height: 1.3; }
.emp-cell .emp-name { font-weight: 700; font-size: .88rem; }
.emp-cell .emp-job { font-size: .75rem; color: var(--text3); }

.stars-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: .88rem;
}
.stars-badge.positive { background: rgba(16,185,129,.1); color: #10B981; }
.stars-badge.negative { background: rgba(239,68,68,.1); color: #EF4444; }
.stars-badge.zero { background: var(--surface2); color: var(--text3); }

.rank-badge {
    width: 28px; height: 28px; border-radius: 50%; display: inline-flex;
    align-items: center; justify-content: center; font-weight: 900; font-size: .78rem;
}
.rank-badge.gold { background: linear-gradient(135deg, #c9a84c, #e8d9a0); color: #5a4a1a; }
.rank-badge.silver { background: linear-gradient(135deg, #94a3b8, #cbd5e1); color: #334155; }
.rank-badge.bronze { background: linear-gradient(135deg, #b8734a, #d4a574); color: #4a2c1a; }
.rank-badge.normal { background: var(--surface2); color: var(--text3); }

.stars-actions-cell { display: flex; gap: 4px; justify-content: center; }
.stars-actions-cell button {
    padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border);
    background: var(--surface1); cursor: pointer; font-size: .78rem;
    font-weight: 600; font-family: inherit; transition: .2s; color: var(--text2);
}
.stars-actions-cell button:hover { border-color: var(--royal-gold); color: var(--royal-gold); }
.stars-actions-cell button.danger:hover { border-color: #EF4444; color: #EF4444; }

/* Modal */
.stars-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 9999; align-items: center; justify-content: center; padding: 20px;
}
.stars-modal-overlay.show { display: flex; }
.stars-modal {
    background: var(--surface1); border-radius: 16px; padding: 28px;
    max-width: 460px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.3);
    position: relative;
}
.stars-modal h3 { margin: 0 0 20px; font-size: 1.1rem; color: var(--text1); display: flex; align-items: center; gap: 8px; }
.stars-modal .form-row { margin-bottom: 14px; }
.stars-modal .form-row label { display: block; font-size: .8rem; font-weight: 700; color: var(--text3); margin-bottom: 5px; }
.stars-modal .form-row input,
.stars-modal .form-row select,
.stars-modal .form-row textarea {
    width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: .88rem; font-family: inherit; background: var(--surface2); color: var(--text1);
    box-sizing: border-box;
}
.stars-modal .form-row textarea { resize: vertical; min-height: 70px; }
.stars-modal .form-row input:focus,
.stars-modal .form-row select:focus,
.stars-modal .form-row textarea:focus { border-color: var(--royal-gold); outline: none; box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
.stars-modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.stars-modal-actions .btn-star { flex: 1; justify-content: center; }
.modal-close { position: absolute; top: 16px; left: 16px; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text3); padding: 4px; }

/* Recent Log */
.recent-log { margin-top: 24px; }
.log-item {
    display: flex; align-items: center; gap: 12px; padding: 10px 14px;
    border-bottom: 1px solid var(--border); font-size: .84rem;
}
.log-item:last-child { border-bottom: none; }
.log-icon {
    width: 32px; height: 32px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0;
}
.log-icon.add { background: rgba(16,185,129,.1); }
.log-icon.sub { background: rgba(239,68,68,.1); }
.log-icon.auto { background: rgba(59,130,246,.1); }
.log-icon.reset { background: rgba(107,114,128,.1); }
.log-details { flex: 1; }
.log-details .log-name { font-weight: 700; }
.log-details .log-reason { color: var(--text3); font-size: .78rem; margin-top: 2px; }
.log-time { font-size: .75rem; color: var(--text3); white-space: nowrap; }

/* Settings panel */
.settings-panel { background: var(--surface1); border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-top: 24px; display: none; }
.settings-panel.show { display: block; }
.settings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
.setting-item label { display: block; font-size: .8rem; font-weight: 700; color: var(--text3); margin-bottom: 5px; }
.setting-item input {
    width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
    border-radius: 8px; font-size: .88rem; font-family: inherit;
    background: var(--surface2); color: var(--text1); box-sizing: border-box;
}
.setting-item input:focus { border-color: var(--royal-gold); outline: none; }
.toggle-row { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
.toggle-row label { font-size: .88rem; font-weight: 600; color: var(--text2); margin: 0; }

@media print {
    .stars-actions-bar, .stars-modal-overlay, .settings-panel, .stars-actions-cell,
    .sidebar, .topbar, .bottom-nav, .no-print { display: none !important; }
    .main-content { margin: 0 !important; }
    .content { padding: 0 !important; }
    .stars-hero { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
@media (max-width: 768px) {
    .stars-stats { grid-template-columns: repeat(2, 1fr); }
    .stars-actions-bar { flex-direction: column; }
    .emp-cell .emp-job { display: none; }
    .stars-modal { margin: 10px; }
}
</style>

<?php
$reportTitle    = 'نظام النجوم والمكافآت';
$reportSubtitle = 'نظام الحضور والانصراف';
$reportMeta     = ["إجمالي النجوم: {$totalStars}", "الموظفون: " . count($employees)];
require __DIR__ . '/../includes/report_print_header.php';
?>

<!-- Hero -->
<div class="stars-hero">
    <h2>⭐ نظام النجوم والمكافآت</h2>
    <p>نجوم تُمنح تلقائياً على التبكير وتُخصم على التأخير — مع إمكانية الإضافة والخصم اليدوي</p>
</div>

<!-- الإحصائيات -->
<div class="stars-stats">
    <div class="star-stat gold">
        <div class="star-stat-icon">⭐</div>
        <div class="star-stat-value"><?= $totalStars ?></div>
        <div class="star-stat-label">إجمالي النجوم</div>
    </div>
    <div class="star-stat green">
        <div class="star-stat-icon">🏆</div>
        <div class="star-stat-value"><?= $bonusCount ?></div>
        <div class="star-stat-label">حققوا المكافأة (<?= $bonusThreshold ?>+)</div>
    </div>
    <div class="star-stat red">
        <div class="star-stat-icon">📉</div>
        <div class="star-stat-value"><?= $negativeCount ?></div>
        <div class="star-stat-label">رصيد سالب</div>
    </div>
    <div class="star-stat blue">
        <div class="star-stat-icon">👥</div>
        <div class="star-stat-value"><?= count($employees) ?></div>
        <div class="star-stat-label">إجمالي الموظفين</div>
    </div>
</div>

<!-- أزرار الإجراءات -->
<div class="stars-actions-bar no-print">
    <button class="btn-star gold" onclick="openAddModal()">⭐ إضافة / خصم نجوم</button>
    <button class="btn-star navy" onclick="openAutoModal()">⚡ حساب تلقائي من الحضور</button>
    <button class="btn-star outline" onclick="toggleSettings()">⚙️ الإعدادات</button>
    <button class="btn-star outline" onclick="window.print()">🖨️ طباعة</button>

    <div style="margin-right:auto;display:flex;gap:8px;align-items:center">
        <select class="form-control" style="padding:8px 12px;border-radius:8px;font-size:.84rem" onchange="location.href='?branch_id='+this.value+'&search=<?= urlencode($searchName) ?>&sort=<?= $sortBy ?>&dir=<?= $sortDir ?>'">
            <option value="0">كل الفروع</option>
            <?php foreach ($branchesList as $br): ?>
            <option value="<?= $br['id'] ?>" <?= $branchFilter == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" class="form-control" style="padding:8px 12px;border-radius:8px;font-size:.84rem;max-width:200px" placeholder="بحث بالاسم..." value="<?= htmlspecialchars($searchName) ?>" onkeydown="if(event.key==='Enter') location.href='?branch_id=<?= $branchFilter ?>&search='+encodeURIComponent(this.value)+'&sort=<?= $sortBy ?>&dir=<?= $sortDir ?>'">
    </div>
</div>

<!-- لوحة الإعدادات -->
<div class="settings-panel" id="settingsPanel">
    <h3 style="margin:0 0 16px;font-size:1rem">⚙️ إعدادات نظام النجوم</h3>
    <div class="settings-grid">
        <div class="setting-item">
            <label>نجوم لكل يوم تبكير</label>
            <input type="number" id="s_per_early" value="<?= $starsPerEarly ?>" min="1" max="10">
        </div>
        <div class="setting-item">
            <label>خصم لكل يوم تأخير</label>
            <input type="number" id="s_deduct_late" value="<?= $starsDeductLate ?>" min="1" max="10">
        </div>
        <div class="setting-item">
            <label>حد أدنى تبكير (دقيقة)</label>
            <input type="number" id="s_early_min" value="<?= $earlyMinMinutes ?>" min="1" max="60">
        </div>
        <div class="setting-item">
            <label>حد أدنى تأخير (دقيقة)</label>
            <input type="number" id="s_late_min" value="<?= $lateMinMinutes ?>" min="1" max="60">
        </div>
        <div class="setting-item">
            <label>حد المكافأة (نجمة)</label>
            <input type="number" id="s_bonus" value="<?= $bonusThreshold ?>" min="1" max="1000">
        </div>
    </div>
    <div class="toggle-row">
        <input type="checkbox" id="s_auto_enabled" <?= $autoEnabled ? 'checked' : '' ?>>
        <label for="s_auto_enabled">تفعيل الحساب التلقائي للنجوم</label>
    </div>
    <div style="margin-top:16px;display:flex;gap:10px">
        <button class="btn-star gold" onclick="saveSettings()">💾 حفظ الإعدادات</button>
        <button class="btn-star outline" onclick="toggleSettings()">إغلاق</button>
    </div>
</div>

<!-- جدول الترتيب -->
<div class="report-table-wrap" style="margin-top:0">
    <div class="card-header" style="padding:16px 20px;margin:0;border-bottom:2px solid var(--royal-gold)">
        <span class="card-title"><span class="card-title-bar"></span> 🏅 ترتيب الموظفين حسب النجوم</span>
        <span class="badge badge-blue"><?= count($employees) ?> موظف</span>
    </div>
    <div style="overflow-x:auto">
    <table class="stars-table">
        <thead><tr>
            <th>#</th>
            <th style="text-align:right">الموظف</th>
            <th>الفرع</th>
            <th>
                <a href="?branch_id=<?= $branchFilter ?>&search=<?= urlencode($searchName) ?>&sort=balance&dir=<?= $sortBy === 'balance' && $sortDir === 'desc' ? 'asc' : 'desc' ?>" style="text-decoration:none;color:inherit">
                    الرصيد <?= $sortBy === 'balance' ? ($sortDir === 'desc' ? '↓' : '↑') : '⇅' ?>
                </a>
            </th>
            <th class="no-print">إجراءات</th>
        </tr></thead>
        <tbody>
        <?php if (empty($employees)): ?>
            <tr><td colspan="5" style="padding:40px;text-align:center;color:var(--text3)">لا يوجد موظفين</td></tr>
        <?php else: ?>
            <?php foreach ($employees as $i => $emp):
                $balance = (int)$emp['stars_balance'];
                $cls = $balance > 0 ? 'positive' : ($balance < 0 ? 'negative' : 'zero');
                $rankCls = match(true) {
                    $i === 0 && $sortBy === 'balance' && $sortDir === 'desc' => 'gold',
                    $i === 1 && $sortBy === 'balance' && $sortDir === 'desc' => 'silver',
                    $i === 2 && $sortBy === 'balance' && $sortDir === 'desc' => 'bronze',
                    default => 'normal'
                };
                $initials = mb_substr($emp['name'], 0, 2);
            ?>
            <tr>
                <td><span class="rank-badge <?= $rankCls ?>"><?= $i + 1 ?></span></td>
                <td>
                    <div class="emp-cell">
                        <?php if (!empty($emp['profile_photo'])): ?>
                        <img src="<?= SITE_URL ?>/api/serve-file.php?f=<?= urlencode($emp['profile_photo']) ?>" alt="">
                        <?php else: ?>
                        <div class="emp-ph"><?= $initials ?></div>
                        <?php endif; ?>
                        <div class="emp-info">
                            <div class="emp-name"><?= htmlspecialchars($emp['name']) ?></div>
                            <div class="emp-job"><?= htmlspecialchars($emp['job_title'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($emp['branch_name'] ?? '-') ?></span></td>
                <td>
                    <span class="stars-badge <?= $cls ?>">
                        <?= $balance > 0 ? '⭐ +' . $balance : ($balance < 0 ? '📉 ' . $balance : '— 0') ?>
                    </span>
                    <?php if ($balance >= $bonusThreshold): ?><span title="حقق حد المكافأة!" style="margin-right:4px">🏆</span><?php endif; ?>
                </td>
                <td class="no-print">
                    <div class="stars-actions-cell">
                        <button onclick="openAddModal(<?= $emp['id'] ?>)" title="إضافة/خصم">⭐</button>
                        <button class="danger" onclick="resetStars(<?= $emp['id'] ?>, '<?= htmlspecialchars(addslashes($emp['name']), ENT_QUOTES) ?>')" title="إعادة تعيين">🔄</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- سجل آخر العمليات -->
<div class="recent-log">
    <div class="report-table-wrap">
        <div class="card-header" style="padding:16px 20px;margin:0;border-bottom:2px solid var(--border)">
            <span class="card-title"><span class="card-title-bar"></span> 📋 آخر العمليات</span>
        </div>
        <?php if (empty($recentLogs)): ?>
        <div style="padding:30px;text-align:center;color:var(--text3)">لا توجد عمليات بعد</div>
        <?php else: ?>
        <?php foreach ($recentLogs as $log):
            $isPos = $log['stars'] > 0;
            $iconCls = match($log['source']) {
                'auto_early' => 'auto', 'auto_late' => 'auto',
                'reset' => 'reset',
                default => $isPos ? 'add' : 'sub'
            };
            $icon = match($log['source']) {
                'auto_early' => '⚡', 'auto_late' => '⚡',
                'reset' => '🔄',
                default => $isPos ? '⭐' : '📉'
            };
        ?>
        <div class="log-item">
            <div class="log-icon <?= $iconCls ?>"><?= $icon ?></div>
            <div class="log-details">
                <div><span class="log-name"><?= htmlspecialchars($log['employee_name']) ?></span>
                     — <span style="color:<?= $isPos ? '#10B981' : '#EF4444' ?>;font-weight:700"><?= ($isPos ? '+' : '') . $log['stars'] ?></span> نجمة</div>
                <div class="log-reason"><?= htmlspecialchars($log['reason']) ?><?php if ($log['admin_name']): ?> • بواسطة <?= htmlspecialchars($log['admin_name']) ?><?php endif; ?></div>
            </div>
            <div class="log-time"><?= date('Y/m/d H:i', strtotime($log['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: إضافة/خصم نجوم -->
<div class="stars-modal-overlay" id="addModal">
    <div class="stars-modal">
        <button class="modal-close" onclick="closeModals()">✕</button>
        <h3>⭐ إضافة / خصم نجوم</h3>
        <div class="form-row">
            <label>الموظف</label>
            <select id="modalEmpId">
                <option value="">اختر الموظف...</option>
                <?php foreach ($employeesList as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label>عدد النجوم (موجب=إضافة، سالب=خصم)</label>
            <input type="number" id="modalStars" placeholder="مثال: 5 أو -3" min="-100" max="100">
        </div>
        <div class="form-row">
            <label>السبب</label>
            <textarea id="modalReason" placeholder="أدخل سبب الإضافة أو الخصم..."></textarea>
        </div>
        <div class="stars-modal-actions">
            <button class="btn-star gold" onclick="submitStars()">✓ تنفيذ</button>
            <button class="btn-star outline" onclick="closeModals()">إلغاء</button>
        </div>
    </div>
</div>

<!-- Modal: حساب تلقائي -->
<div class="stars-modal-overlay" id="autoModal">
    <div class="stars-modal">
        <button class="modal-close" onclick="closeModals()">✕</button>
        <h3>⚡ حساب تلقائي من سجل الحضور</h3>
        <p style="font-size:.84rem;color:var(--text3);margin:0 0 16px">سيتم حساب نجوم التبكير والخصم لأيام التأخير تلقائياً من بيانات الحضور (بدون تكرار).</p>
        <div class="form-row">
            <label>من تاريخ</label>
            <input type="date" id="autoFrom" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="form-row">
            <label>إلى تاريخ</label>
            <input type="date" id="autoTo" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="stars-modal-actions">
            <button class="btn-star navy" onclick="runAutoCalc()">⚡ بدء الحساب</button>
            <button class="btn-star outline" onclick="closeModals()">إلغاء</button>
        </div>
    </div>
</div>

<script>
const CSRF = <?= json_encode(generateCsrfToken()) ?>;

function closeModals() {
    document.querySelectorAll('.stars-modal-overlay').forEach(m => m.classList.remove('show'));
}

function openAddModal(empId) {
    document.getElementById('addModal').classList.add('show');
    if (empId) document.getElementById('modalEmpId').value = empId;
}

function openAutoModal() {
    document.getElementById('autoModal').classList.add('show');
}

function toggleSettings() {
    document.getElementById('settingsPanel').classList.toggle('show');
}

async function postAction(data) {
    data.csrf_token = CSRF;
    const fd = new FormData();
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    try {
        const res = await fetch('stars.php', { method: 'POST', body: fd });
        return await res.json();
    } catch (e) {
        return { success: false, message: 'خطأ في الاتصال' };
    }
}

async function submitStars() {
    const empId = document.getElementById('modalEmpId').value;
    const stars = document.getElementById('modalStars').value;
    const reason = document.getElementById('modalReason').value.trim();
    if (!empId || !stars || !reason) return alert('يرجى تعبئة جميع الحقول');
    const r = await postAction({ action: 'add_stars', employee_id: empId, stars, reason });
    alert(r.message);
    if (r.success) location.reload();
}

async function resetStars(empId, name) {
    if (!confirm(`إعادة تعيين نجوم "${name}" إلى صفر؟`)) return;
    const r = await postAction({ action: 'reset_stars', employee_id: empId });
    alert(r.message);
    if (r.success) location.reload();
}

async function runAutoCalc() {
    const from = document.getElementById('autoFrom').value;
    const to = document.getElementById('autoTo').value;
    if (!from || !to) return alert('اختر التواريخ');
    if (!confirm(`حساب النجوم تلقائياً من ${from} إلى ${to}؟`)) return;
    const r = await postAction({ action: 'auto_calculate', date_from: from, date_to: to });
    alert(r.message);
    if (r.success) location.reload();
}

async function saveSettings() {
    const r = await postAction({
        action: 'save_settings',
        stars_per_early_day: document.getElementById('s_per_early').value,
        stars_deduct_per_late_day: document.getElementById('s_deduct_late').value,
        stars_early_min_minutes: document.getElementById('s_early_min').value,
        stars_late_min_minutes: document.getElementById('s_late_min').value,
        stars_bonus_threshold: document.getElementById('s_bonus').value,
        stars_auto_enabled: document.getElementById('s_auto_enabled').checked ? '1' : '0',
    });
    alert(r.message);
    if (r.success) location.reload();
}

// Close modal on overlay click
document.querySelectorAll('.stars-modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) closeModals(); });
});
</script>

<style>
@page { size: A4; margin: 12mm 10mm 15mm 10mm; }
</style>

<?php require __DIR__ . '/../includes/report_print_footer.php'; ?>
<?php require __DIR__ . '/../includes/print_settings.php'; ?>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
