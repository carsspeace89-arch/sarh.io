<?php
// =============================================================
// admin/report-monthly.php - التقرير الشهري للحضور
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'التقرير الشهري';
$activePage = 'report-monthly';

// =================== فلاتر ===================
$month    = $_GET['month'] ?? date('Y-m');
$branchId = !empty($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
$empId    = !empty($_GET['emp_id']) ? (int)$_GET['emp_id'] : null;
$filterShift = (int)($_GET['shift'] ?? 0);

$year  = (int)date('Y', strtotime($month . '-01'));
$mon   = (int)date('m', strtotime($month . '-01'));
$daysInMonth = (int)date('t', strtotime($month . '-01'));
$monthName = date('F Y', strtotime($month . '-01'));

// الفروع
$branches = db()->query("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name")->fetchAll();

// الموظفون (مع فلتر الفرع)
$empWhere = "e.is_active = 1 AND e.deleted_at IS NULL";
$empParams = [];
if ($branchId) {
    $empWhere .= " AND e.branch_id = ?";
    $empParams[] = $branchId;
}
if ($empId) {
    $empWhere .= " AND e.id = ?";
    $empParams[] = $empId;
}

$empStmt = db()->prepare("
    SELECT e.id, e.name, e.job_title, b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE {$empWhere}
    ORDER BY e.name
");
$empStmt->execute($empParams);
$employees = $empStmt->fetchAll();

// بيانات الحضور لهذا الشهر
$startDate = "{$year}-" . str_pad($mon, 2, '0', STR_PAD_LEFT) . "-01";
$endDate   = "{$year}-" . str_pad($mon, 2, '0', STR_PAD_LEFT) . "-" . str_pad($daysInMonth, 2, '0', STR_PAD_LEFT);

// فلتر الوردية
$shiftTimeCond = '';
$shiftTimeParams = [];
if ($filterShift > 0) {
    $sf = buildShiftTimeFilter($filterShift);
    if ($sf) { $shiftTimeCond = "AND " . $sf['sql']; $shiftTimeParams = $sf['params']; }
}

$attStmt = db()->prepare("
    SELECT a.employee_id, a.attendance_date, a.type, a.timestamp, a.late_minutes
    FROM attendances a
    WHERE a.attendance_date BETWEEN ? AND ?
    $shiftTimeCond
    ORDER BY a.timestamp ASC
");
$attStmt->execute(array_merge([$startDate, $endDate], $shiftTimeParams));
$attDataRaw = $attStmt->fetchAll();

// جلب ورديات الفروع
$allBranchShifts = [];
$bsStmt = db()->query("SELECT branch_id, shift_number, shift_start, shift_end FROM branch_shifts WHERE is_active = 1 ORDER BY branch_id, shift_number");
foreach ($bsStmt->fetchAll() as $s) {
    $allBranchShifts[$s['branch_id']][] = $s;
}
$maxShifts = 1;
foreach ($allBranchShifts as $bs) {
    $maxShifts = max($maxShifts, count($bs));
}

// بناء خريطة موظف => فرع
$empBranchMap = [];
foreach ($employees as $emp) {
    $empBranchMap[$emp['id']] = $emp['branch_id'] ?? null;
}

// ترتيب البيانات: [emp_id][date][shift_number] => {in, out, late}
$attMap = [];
foreach ($attDataRaw as $row) {
    $eid = $row['employee_id'];
    $dateStr = $row['attendance_date'];
    $branchId2 = $empBranchMap[$eid] ?? null;
    $branchShifts = $allBranchShifts[$branchId2] ?? [
        ['shift_number' => 1, 'shift_start' => getSystemSetting('work_start_time', '08:00'), 'shift_end' => getSystemSetting('work_end_time', '16:00')]
    ];
    $shiftNum = assignTimeToShift(date('H:i', strtotime($row['timestamp'])), $branchShifts);

    if (!isset($attMap[$eid][$dateStr])) {
        $attMap[$eid][$dateStr] = ['_present' => true, '_late' => 0];
        for ($sn = 1; $sn <= 3; $sn++) {
            $attMap[$eid][$dateStr][$sn] = ['in' => null, 'out' => null, 'late' => 0];
        }
    }
    if ($row['type'] === 'in' && !$attMap[$eid][$dateStr][$shiftNum]['in']) {
        $attMap[$eid][$dateStr][$shiftNum]['in'] = $row['timestamp'];
        $attMap[$eid][$dateStr][$shiftNum]['late'] = (int)($row['late_minutes'] ?? 0);
        $attMap[$eid][$dateStr]['_late'] = max($attMap[$eid][$dateStr]['_late'], (int)($row['late_minutes'] ?? 0));
    } elseif ($row['type'] === 'out') {
        $attMap[$eid][$dateStr][$shiftNum]['out'] = $row['timestamp'];
    }
}

// الإجازات المعتمدة
$leaveStmt = db()->prepare("
    SELECT employee_id, start_date, end_date, leave_type
    FROM leaves
    WHERE status = 'approved' AND start_date <= ? AND end_date >= ?
");
$leaveStmt->execute([$endDate, $startDate]);
$leaveData = $leaveStmt->fetchAll();

$leaveMap = [];
foreach ($leaveData as $lv) {
    $s = max(strtotime($lv['start_date']), strtotime($startDate));
    $e = min(strtotime($lv['end_date']), strtotime($endDate));
    for ($d = $s; $d <= $e; $d += 86400) {
        $dateStr = date('Y-m-d', $d);
        $leaveMap[$lv['employee_id']][$dateStr] = $lv['leave_type'];
    }
}

// جميع الموظفين (بدون فلتر) للقائمة المنسدلة
$allEmps = db()->query("SELECT id, name FROM employees WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name")->fetchAll();

// =========================================================
// تصدير CSV
// =========================================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="monthly-report-' . $month . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $csvHeader = ['الموظف', 'الفرع'];
    $today = date('Y-m-d');
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $csvHeader[] = $d;
    }
    $csvHeader[] = 'حضور';
    $csvHeader[] = 'غياب';
    $csvHeader[] = 'تأخر';
    $csvHeader[] = 'إجازة';
    fputcsv($out, $csvHeader);
    foreach ($employees as $emp) {
        $row = [$emp['name'], $emp['branch_name'] ?? ''];
        $pCount = 0; $aCount = 0; $lCount = 0; $lvCount = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            if ($dateStr > $today) { $row[] = '-'; continue; }
            $dow = date('w', strtotime($dateStr));
            if ($dow == 5) { $row[] = 'جمعة'; continue; }
            if (isset($leaveMap[$emp['id']][$dateStr])) { $row[] = 'إجازة'; $lvCount++; continue; }
            if (isset($attMap[$emp['id']][$dateStr])) {
                $late = $attMap[$emp['id']][$dateStr]['_late'] ?? 0;
                if ($late > 0) { $row[] = "تأخر {$late}د"; $pCount++; $lCount++; }
                else { $row[] = '✓'; $pCount++; }
            } else { $row[] = 'غائب'; $aCount++; }
        }
        $row[] = $pCount;
        $row[] = $aCount;
        $row[] = $lCount;
        $row[] = $lvCount;
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- الأدوات -->
<?php
$reportTitle = 'التقرير الشهري';
$reportSubtitle = 'نظام الحضور والانصراف';
$reportMeta = ["الشهر: {$month}"];
if ($branchId) { $bName = ''; foreach($branches as $bb) if($bb['id']==$branchId) $bName=$bb['name']; $reportMeta[] = "الفرع: {$bName}"; }
require __DIR__ . '/../includes/report_print_header.php';
?>
<div class="card" style="margin-bottom:16px;padding:14px">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">الشهر</label>
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">الفرع</label>
            <select name="branch_id" id="branchSelect" style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="">كل الفروع</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $branchId == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">الوردية</label>
            <select name="shift" id="shiftSelect" style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="0">كل الورديات</option>
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">الموظف</label>
            <select name="emp_id" style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="">كل الموظفين</option>
                <?php foreach ($allEmps as $ae): ?>
                    <option value="<?= $ae['id'] ?>" <?= $empId == $ae['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ae['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:8px 20px">عرض</button>
        <a href="?month=<?= urlencode($month) ?>&branch_id=<?= $branchId ?>&emp_id=<?= $empId ?>&shift=<?= $filterShift ?>&export=csv" class="btn btn-secondary" style="padding:8px 16px;text-decoration:none">📥 CSV</a>
        <button type="button" onclick="window.print()" class="btn btn-secondary" style="padding:8px 16px">🖨️ طباعة</button>
    </form>
</div>

<!-- ملخص -->
<?php
$totalPresent = 0; $totalAbsent = 0; $totalLate = 0; $totalLateMin = 0;
$today = date('Y-m-d');
foreach ($employees as $emp) {
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
        if ($dateStr > $today) continue;
        $dayOfWeek = date('w', strtotime($dateStr));
        if ($dayOfWeek == 5) continue; // الجمعة عطلة
        
        if (isset($attMap[$emp['id']][$dateStr])) {
            $totalPresent++;
            $late = (int)($attMap[$emp['id']][$dateStr]['_late'] ?? 0);
            if ($late > 0) { $totalLate++; $totalLateMin += $late; }
        } elseif (isset($leaveMap[$emp['id']][$dateStr])) {
            // إجازة - لا يحسب غياب
        } else {
            $totalAbsent++;
        }
    }
}
?>
<div class="stats-grid" style="margin-bottom:16px">
    <div class="stat-card">
        <div class="stat-icon-wrap green">✓</div>
        <div><div class="stat-value"><?= $totalPresent ?></div><div class="stat-label">أيام حضور</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap red">✗</div>
        <div><div class="stat-value"><?= $totalAbsent ?></div><div class="stat-label">أيام غياب</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#FEF3C7;color:#D97706">⏰</div>
        <div><div class="stat-value"><?= $totalLate ?></div><div class="stat-label">تأخيرات</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#DBEAFE;color:#2563EB">📊</div>
        <div><div class="stat-value"><?= $totalLateMin ?> د</div><div class="stat-label">إجمالي دقائق التأخير</div></div>
    </div>
</div>

<!-- الجدول -->
<?php foreach ($employees as $empIdx => $emp):
    // حساب إحصائيات الموظف
    $empPresent = 0; $empAbsent = 0; $empLate = 0; $empLeave = 0; $empLateMin = 0; $empFriday = 0;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
        if ($dateStr > $today) continue;
        $dayOfWeek = date('w', strtotime($dateStr));
        if ($dayOfWeek == 5) { $empFriday++; continue; }
        if (isset($attMap[$emp['id']][$dateStr])) {
            $empPresent++;
            $late = (int)($attMap[$emp['id']][$dateStr]['_late'] ?? 0);
            if ($late > 0) { $empLate++; $empLateMin += $late; }
        } elseif (isset($leaveMap[$emp['id']][$dateStr])) {
            $empLeave++;
        } else {
            $empAbsent++;
        }
    }
?>
<div class="emp-page">
    <!-- ترويسة الموظف للطباعة -->
    <div class="emp-print-header">
        <div class="eph-top">
            <div class="eph-logo">
                <img src="<?= SITE_URL ?>/assets/images/loogo.png" alt="">
            </div>
            <div class="eph-center">
                <div class="eph-title">التقرير الشهري للحضور والانصراف</div>
                <div class="eph-month"><?= $monthName ?></div>
            </div>
            <div class="eph-date"><?= date('Y/m/d') ?></div>
        </div>
        <div class="eph-emp-info">
            <div class="eph-emp-field"><span class="eph-label">الموظف:</span> <strong><?= htmlspecialchars($emp['name']) ?></strong></div>
            <div class="eph-emp-field"><span class="eph-label">المسمى:</span> <?= htmlspecialchars($emp['job_title'] ?? '-') ?></div>
            <div class="eph-emp-field"><span class="eph-label">الفرع:</span> <?= htmlspecialchars($emp['branch_name'] ?? '-') ?></div>
        </div>
    </div>

    <!-- بطاقة الشاشة -->
    <div class="emp-screen-header card" style="padding:14px 16px;margin-bottom:0;border-radius:12px 12px 0 0;background:var(--surface2,#F8FAFC);border-bottom:1px solid var(--border-color,#E2E8F0);display:flex;justify-content:space-between;align-items:center">
        <div>
            <strong style="font-size:.95rem"><?= htmlspecialchars($emp['name']) ?></strong>
            <span style="color:var(--text3);font-size:.82rem;margin-right:8px"><?= htmlspecialchars($emp['job_title']) ?></span>
            <span style="color:var(--text3);font-size:.78rem;margin-right:8px">— <?= htmlspecialchars($emp['branch_name'] ?? 'بدون فرع') ?></span>
        </div>
        <div style="display:flex;gap:10px;font-size:.78rem">
            <span style="color:#10B981">حضور: <?= $empPresent ?></span>
            <span style="color:#EF4444">غياب: <?= $empAbsent ?></span>
            <span style="color:#D97706">تأخير: <?= $empLate ?></span>
            <span style="color:#6366F1">إجازة: <?= $empLeave ?></span>
        </div>
    </div>

    <div class="card emp-table-card" style="margin-bottom:16px;padding:0;overflow:hidden;border-radius:0 0 12px 12px">
    <div style="overflow-x:auto">
        <table class="monthly-att-table" style="width:100%;border-collapse:collapse;font-size:.8rem;min-width:<?= 200 + $maxShifts * 200 ?>px">
            <thead>
                <tr style="background:var(--surface2,#F8FAFC)">
                    <th style="padding:8px;text-align:center;width:34px">اليوم</th>
                    <th style="padding:8px;text-align:center">الحالة</th>
                    <?php for ($sn = 1; $sn <= $maxShifts; $sn++): ?>
                    <th style="padding:8px;text-align:center">حضور و<?= $sn ?></th>
                    <th style="padding:8px;text-align:center">انصراف و<?= $sn ?></th>
                    <?php endfor; ?>
                    <th style="padding:8px;text-align:center">التأخير</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($d = 1; $d <= $daysInMonth; $d++):
                $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
                $dayOfWeek = date('w', strtotime($dateStr));
                $dayName = ['أحد','إثنين','ثلاثاء','أربعاء','خميس','جمعة','سبت'][$dayOfWeek];
                $isFriday = $dayOfWeek == 5;
                $isFuture = $dateStr > $today;
                $att = $attMap[$emp['id']][$dateStr] ?? null;
                $leave = $leaveMap[$emp['id']][$dateStr] ?? null;
                
                $statusLabel = '';
                $statusCls = '';
                if ($isFuture) {
                    $statusLabel = '—'; $statusCls = '';
                } elseif ($isFriday) {
                    $statusLabel = 'عطلة'; $statusCls = 'color:var(--text3)';
                } elseif ($att) {
                    $late = (int)($att['_late'] ?? 0);
                    if ($late > 0) { $statusLabel = 'متأخر'; $statusCls = 'color:#D97706;font-weight:600'; }
                    else { $statusLabel = 'حاضر'; $statusCls = 'color:#10B981;font-weight:600'; }
                } elseif ($leave) {
                    $types = ['annual'=>'سنوية','sick'=>'مرضية','unpaid'=>'بدون راتب','other'=>'أخرى'];
                    $statusLabel = 'إجازة (' . ($types[$leave] ?? $leave) . ')';
                    $statusCls = 'color:#6366F1;font-weight:600';
                } else {
                    $statusLabel = 'غائب'; $statusCls = 'color:#EF4444;font-weight:600';
                }
            ?>
                <tr style="border-bottom:1px solid var(--border-color,#E2E8F0);<?= $isFriday ? 'background:var(--surface2,#F8FAFC);opacity:.6' : '' ?>">
                    <td style="padding:6px 8px;text-align:center;font-weight:600"><?= $d ?> <small style="color:var(--text3)"><?= $dayName ?></small></td>
                    <td style="padding:6px 8px;text-align:center;<?= $statusCls ?>"><?= $statusLabel ?></td>
                    <?php for ($sn = 1; $sn <= $maxShifts; $sn++): ?>
                    <td style="padding:6px 8px;text-align:center;direction:ltr"><?= $att && isset($att[$sn]) && $att[$sn]['in'] ? date('h:i A', strtotime($att[$sn]['in'])) : '—' ?></td>
                    <td style="padding:6px 8px;text-align:center;direction:ltr"><?= $att && isset($att[$sn]) && $att[$sn]['out'] ? date('h:i A', strtotime($att[$sn]['out'])) : '—' ?></td>
                    <?php endfor; ?>
                    <td style="padding:6px 8px;text-align:center;<?= ($att && ($att['_late'] ?? 0) > 0) ? 'color:#D97706;font-weight:600' : '' ?>">
                        <?= $att && ($att['_late'] ?? 0) > 0 ? $att['_late'] . ' د' : '—' ?>
                    </td>
                </tr>
            <?php endfor; ?>
            </tbody>
            <!-- صف المجموع -->
            <tfoot>
                <tr class="emp-totals-row">
                    <td colspan="2" style="text-align:center;font-weight:800">المجموع</td>
                    <?php for ($sn = 1; $sn <= $maxShifts; $sn++): ?>
                    <td colspan="2" style="text-align:center">—</td>
                    <?php endfor; ?>
                    <td style="text-align:center;font-weight:700;color:#D97706"><?= $empLateMin > 0 ? $empLateMin . ' د' : '—' ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- ملخص الموظف -->
    <div class="emp-summary-bar">
        <div class="emp-sum-item green">✓ حضور: <strong><?= $empPresent ?></strong></div>
        <div class="emp-sum-item red">✗ غياب: <strong><?= $empAbsent ?></strong></div>
        <div class="emp-sum-item orange">⏰ تأخير: <strong><?= $empLate ?></strong> (<?= $empLateMin ?> د)</div>
        <div class="emp-sum-item purple">📋 إجازة: <strong><?= $empLeave ?></strong></div>
        <div class="emp-sum-item gray">🕌 جمعة: <strong><?= $empFriday ?></strong></div>
    </div>
    </div>

    <!-- فوتر توقيعات الموظف للطباعة -->
    <div class="emp-print-footer">
        <div class="epf-sigs">
            <div class="epf-sig"><div class="epf-sig-line">توقيع الموظف</div></div>
            <div class="epf-sig"><div class="epf-sig-line">توقيع المدير المباشر</div></div>
            <div class="epf-sig"><div class="epf-sig-line">توقيع الموارد البشرية</div></div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($employees)): ?>
<div class="card" style="padding:40px;text-align:center;color:var(--text3)">لا يوجد موظفون مطابقون للفلاتر</div>
<?php endif; ?>

<script>
(function(){
    const branchShifts = <?= json_encode(getAllBranchShifts(), JSON_UNESCAPED_UNICODE) ?>;
    const branchSel = document.getElementById('branchSelect');
    const shiftSel = document.getElementById('shiftSelect');
    const curShift = <?= $filterShift ?>;
    function updateShifts(){
        const bid = branchSel ? branchSel.value : 0;
        shiftSel.innerHTML = '<option value="0">كل الورديات</option>';
        if(bid && branchShifts[bid]){
            branchShifts[bid].forEach(s=>{
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = 'وردية '+s.num+' ('+s.start+' - '+s.end+')';
                if(s.id == curShift) o.selected = true;
                shiftSel.appendChild(o);
            });
        }
    }
    if(branchSel) branchSel.addEventListener('change', ()=>{ shiftSel.value = 0; updateShifts(); });
    updateShifts();
})();
</script>

<style>
/* ── إخفاء ترويسة/فوتر الموظف على الشاشة ── */
.emp-print-header, .emp-print-footer { display: none; }

/* ── شريط ملخص الموظف ── */
.emp-summary-bar {
    display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 16px;
    background: var(--surface2, #F8FAFC); border-top: 2px solid var(--border-color, #E2E8F0);
}
.emp-sum-item {
    font-size: .78rem; padding: 4px 12px; border-radius: 6px; font-weight: 600;
}
.emp-sum-item.green { background: rgba(16,185,129,.08); color: #10B981; }
.emp-sum-item.red { background: rgba(239,68,68,.08); color: #EF4444; }
.emp-sum-item.orange { background: rgba(217,119,6,.08); color: #D97706; }
.emp-sum-item.purple { background: rgba(99,102,241,.08); color: #6366F1; }
.emp-sum-item.gray { background: rgba(107,114,128,.08); color: #6B7280; }

.emp-totals-row td {
    padding: 8px !important; background: var(--surface2, #F8FAFC);
    border-top: 2px solid var(--border-color, #E2E8F0); font-weight: 700;
}

@page { size: A4 portrait; margin: 5mm 5mm 8mm 5mm; }

@media print {
    /* ── إخفاء عناصر غير مطلوبة ── */
    .sidebar, .topbar, .bottom-nav, form, .no-print,
    .stats-grid, .emp-screen-header,
    .print-report-header, .print-report-footer { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
    .content { padding: 0 !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; }
    html, body { width: 100% !important; }
    .content::after { opacity: .03 !important; }

    /* ── صفحة لكل موظف ── */
    .emp-page {
        page-break-after: always;
        page-break-inside: avoid;
        position: relative;
    }
    .emp-page:last-child { page-break-after: avoid; }

    /* ── ترويسة الموظف ── */
    .emp-print-header {
        display: block !important;
        margin-bottom: 4px;
    }
    .eph-top {
        display: flex; align-items: center; justify-content: space-between;
        padding: 6px 0; border-bottom: 2px solid #0f1b33;
    }
    .eph-logo img { height: 32px; }
    .eph-center { text-align: center; flex: 1; }
    .eph-title { font-size: 11pt; font-weight: 900; color: #0f1b33; }
    .eph-month { font-size: 9pt; color: #555; margin-top: 1px; }
    .eph-date { font-size: 8pt; color: #888; }

    .eph-emp-info {
        display: flex; gap: 20px; padding: 5px 0 4px;
        border-bottom: 1px solid #ddd; font-size: 8.5pt;
    }
    .eph-label { color: #888; font-weight: 400; }
    .eph-emp-info strong { color: #0f1b33; }

    /* ── الجدول مضغوط ── */
    .emp-table-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        border-radius: 0 !important;
        margin-bottom: 0 !important;
    }
    .monthly-att-table { min-width: 0 !important; font-size: 7pt !important; width: 100% !important; }
    .monthly-att-table th {
        padding: 3px 4px !important; font-size: 6.5pt !important;
        background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;
        border-bottom: 1.5px solid #333 !important;
    }
    .monthly-att-table td {
        padding: 2.5px 3px !important; font-size: 7pt !important;
        border-bottom: 0.5px solid #ddd !important; line-height: 1.2 !important;
    }
    .monthly-att-table small { font-size: 5.5pt !important; }
    .monthly-att-table tbody tr { page-break-inside: avoid; }

    .emp-totals-row td {
        padding: 3px 2px !important; font-size: 7.5pt !important;
        border-top: 1.5px solid #333 !important;
        background: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    /* ── ملخص ── */
    .emp-summary-bar {
        padding: 4px 8px !important; gap: 6px !important;
        border-top: 1px solid #ccc !important; background: #fafafa !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .emp-sum-item {
        font-size: 7pt !important; padding: 2px 6px !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    /* ── فوتر التوقيعات ── */
    .emp-print-footer {
        display: block !important;
        margin-top: 8px;
    }
    .epf-sigs {
        display: flex; justify-content: space-between; gap: 20px;
        padding-top: 6px;
    }
    .epf-sig { text-align: center; flex: 1; }
    .epf-sig-line {
        border-top: 1px solid #333; padding-top: 4px;
        font-size: 7.5pt; color: #555; margin-top: 30px;
    }
}
</style>

<?php require __DIR__ . '/../includes/report_print_footer.php'; ?>
<?php require __DIR__ . '/../includes/print_settings.php'; ?>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
</div></div>
</body></html>
