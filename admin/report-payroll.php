<?php
// =============================================================
// admin/report-payroll.php - كشف الرواتب
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'كشف الرواتب';
$activePage = 'report-payroll';

// الفلاتر — دورة الرواتب: من يوم 5 الشهر الماضي إلى يوم 4 هذا الشهر
$today    = date('Y-m-d');
$day      = (int)date('d');
if ($day >= 5) {
    $defaultFrom = date('Y-m-05');
    $defaultTo   = date('Y-m-04', strtotime('+1 month'));
} else {
    $defaultFrom = date('Y-m-05', strtotime('-1 month'));
    $defaultTo   = date('Y-m-04');
}

$dateFrom    = $_GET['date_from'] ?? $defaultFrom;
$dateTo      = $_GET['date_to']   ?? $defaultTo;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $defaultFrom;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = $defaultTo;
if ($dateFrom > $dateTo) { $tmp = $dateFrom; $dateFrom = $dateTo; $dateTo = $tmp; }
$branchId    = (int)($_GET['branch_id'] ?? 0);
$employeeId  = (int)($_GET['employee_id'] ?? 0);

// إعدادات النظام
$lateDeduction    = (float)getSystemSetting('late_deduction_per_minute', '0');
$absenceDeduction = (float)getSystemSetting('absence_deduction_per_day', '0');
$otMultiplier     = (float)getSystemSetting('overtime_rate_multiplier', '1.5');
$weekendDaysStr   = getSystemSetting('weekend_days', '5');
$weekendDays      = array_map('intval', explode(',', $weekendDaysStr));

// جلب العطل الرسمية
$holidayDates = [];
try {
    $hStmt = db()->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?");
    $hStmt->execute([$dateFrom, $dateTo]);
    $holidayDates = array_column($hStmt->fetchAll(), 'holiday_date');
} catch (Exception $e) { /* holidays table may not exist yet */ }

// حساب أيام العمل في الفترة
$workingDays = 0;
$current     = strtotime($dateFrom);
$end         = strtotime($dateTo);
while ($current <= $end) {
    $dow = (int)date('w', $current);
    $d   = date('Y-m-d', $current);
    if (!in_array($dow, $weekendDays) && !in_array($d, $holidayDates)) {
        $workingDays++;
    }
    $current = strtotime('+1 day', $current);
}

// جلب الموظفين
$empWhere = ["e.is_active = 1", "e.deleted_at IS NULL"];
$empParams = [];
if ($employeeId > 0) { $empWhere[] = "e.id = ?";        $empParams[] = $employeeId; }
if ($branchId > 0)   { $empWhere[] = "e.branch_id = ?";  $empParams[] = $branchId; }
$empWhereStr = implode(' AND ', $empWhere);

$empStmt = db()->prepare("
    SELECT e.id, e.name, e.job_title, e.branch_id, e.salary, e.hourly_rate,
           b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE {$empWhereStr}
    ORDER BY e.name
");
$empStmt->execute($empParams);
$allEmployees = $empStmt->fetchAll();

// جلب بيانات الحضور
$attStmt = db()->prepare("
    SELECT employee_id, attendance_date, type, timestamp, late_minutes
    FROM attendances
    WHERE attendance_date BETWEEN ? AND ?
    ORDER BY employee_id, attendance_date, timestamp
");
$attStmt->execute([$dateFrom, $dateTo]);
$allAtt = $attStmt->fetchAll();

// تجميع الحضور حسب الموظف
$empAtt = [];
foreach ($allAtt as $a) {
    $empAtt[$a['employee_id']][$a['attendance_date']][] = $a;
}

// جلب الإجازات المعتمدة
$leaveStmt = db()->prepare("
    SELECT employee_id, leave_type, start_date, end_date
    FROM leaves
    WHERE status = 'approved' AND start_date <= ? AND end_date >= ?
");
$leaveStmt->execute([$dateTo, $dateFrom]);
$allLeaves = $leaveStmt->fetchAll();

$empLeaves = [];
foreach ($allLeaves as $lv) {
    $s = max(strtotime($lv['start_date']), strtotime($dateFrom));
    $e = min(strtotime($lv['end_date']), strtotime($dateTo));
    $days = 0;
    $c = $s;
    while ($c <= $e) {
        $dow = (int)date('w', $c);
        $d   = date('Y-m-d', $c);
        if (!in_array($dow, $weekendDays) && !in_array($d, $holidayDates)) $days++;
        $c = strtotime('+1 day', $c);
    }
    if (!isset($empLeaves[$lv['employee_id']])) {
        $empLeaves[$lv['employee_id']] = ['annual' => 0, 'sick' => 0, 'unpaid' => 0, 'other' => 0];
    }
    $empLeaves[$lv['employee_id']][$lv['leave_type']] = ($empLeaves[$lv['employee_id']][$lv['leave_type']] ?? 0) + $days;
}

// بناء كشف الرواتب
$payroll = [];
foreach ($allEmployees as $emp) {
    $eid = $emp['id'];
    $salary    = (float)$emp['salary'];
    $hourlyRate = (float)$emp['hourly_rate'];
    if ($hourlyRate <= 0 && $salary > 0) $hourlyRate = round($salary / 30 / 8, 2);
    $dailyRate = $salary > 0 ? round($salary / 30, 2) : 0;

    // حساب أيام الحضور
    $presentDays = 0;
    $totalLateMin = 0;
    $totalWorkMin = 0;
    $totalOTMin   = 0;

    $myAtt = $empAtt[$eid] ?? [];
    foreach ($myAtt as $date => $recs) {
        // تحقق أنه يوم عمل
        $dow = (int)date('w', strtotime($date));
        if (in_array($dow, $weekendDays) || in_array($date, $holidayDates)) continue;

        $hasIn = false;
        $ins = $outs = $otStarts = $otEnds = [];
        foreach ($recs as $r) {
            if ($r['type'] === 'in')             { $hasIn = true; $ins[] = strtotime($r['timestamp']); if ($r['late_minutes'] > 0) $totalLateMin += $r['late_minutes']; }
            elseif ($r['type'] === 'out')        $outs[] = strtotime($r['timestamp']);
            elseif ($r['type'] === 'overtime-start') $otStarts[] = strtotime($r['timestamp']);
            elseif ($r['type'] === 'overtime-end')   $otEnds[] = strtotime($r['timestamp']);
        }
        if ($hasIn) $presentDays++;

        // ساعات العمل
        sort($ins); sort($outs);
        if (!empty($ins) && !empty($outs)) {
            $diff = end($outs) - $ins[0];
            if ($diff > 0) $totalWorkMin += (int)round($diff / 60);
        }

        // أوفرتايم
        sort($otStarts); sort($otEnds);
        $otPairs = min(count($otStarts), count($otEnds));
        for ($i = 0; $i < $otPairs; $i++) {
            if ($otEnds[$i] > $otStarts[$i]) $totalOTMin += (int)round(($otEnds[$i] - $otStarts[$i]) / 60);
        }
    }

    // أيام الإجازات
    $leaves   = $empLeaves[$eid] ?? ['annual' => 0, 'sick' => 0, 'unpaid' => 0, 'other' => 0];
    $paidLeave = $leaves['annual'] + $leaves['sick'] + $leaves['other'];
    $unpaidLeave = $leaves['unpaid'];

    // أيام الغياب
    $absentDays = max(0, $workingDays - $presentDays - $paidLeave - $unpaidLeave);

    // الحسابات المالية
    $lateDeductionTotal  = round($totalLateMin * $lateDeduction, 2);
    $absDeductionTotal   = round($absentDays * ($absenceDeduction > 0 ? $absenceDeduction : $dailyRate), 2);
    $unpaidDeduction     = round($unpaidLeave * $dailyRate, 2);
    $otPayment           = round(($totalOTMin / 60) * $hourlyRate * $otMultiplier, 2);
    $totalDeductions     = $lateDeductionTotal + $absDeductionTotal + $unpaidDeduction;
    $netSalary           = $salary - $totalDeductions + $otPayment;

    $payroll[$eid] = [
        'name'              => $emp['name'],
        'job_title'         => $emp['job_title'],
        'branch_name'       => $emp['branch_name'],
        'salary'            => $salary,
        'present_days'      => $presentDays,
        'absent_days'       => $absentDays,
        'total_late_min'    => $totalLateMin,
        'total_work_hours'  => sprintf('%d:%02d', intdiv($totalWorkMin, 60), $totalWorkMin % 60),
        'total_ot_hours'    => sprintf('%d:%02d', intdiv($totalOTMin, 60), $totalOTMin % 60),
        'total_ot_min'      => $totalOTMin,
        'leave_annual'      => $leaves['annual'],
        'leave_sick'        => $leaves['sick'],
        'leave_unpaid'      => $unpaidLeave,
        'late_deduction'    => $lateDeductionTotal,
        'abs_deduction'     => $absDeductionTotal,
        'unpaid_deduction'  => $unpaidDeduction,
        'total_deductions'  => $totalDeductions,
        'ot_payment'        => $otPayment,
        'net_salary'        => $netSalary,
    ];
}

$grandSalary     = array_sum(array_column($payroll, 'salary'));
$grandNet        = array_sum(array_column($payroll, 'net_salary'));
$grandDeductions = array_sum(array_column($payroll, 'total_deductions'));
$grandOTPayment  = array_sum(array_column($payroll, 'ot_payment'));

// قوائم الفلاتر
$employeesList = db()->query("SELECT id, name FROM employees WHERE is_active=1 AND deleted_at IS NULL ORDER BY name")->fetchAll();
$branchesList  = db()->query("SELECT id, name FROM branches WHERE is_active=1 ORDER BY name")->fetchAll();

// CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="payroll_' . $dateFrom . '_' . $dateTo . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['الموظف', 'الوظيفة', 'الفرع', 'الراتب', 'أيام الحضور', 'أيام الغياب', 'إجازات', 'تأخير (دقيقة)', 'ساعات العمل', 'أوفرتايم', 'خصم التأخير', 'خصم الغياب', 'إجمالي الخصم', 'بدل أوفرتايم', 'صافي الراتب']);
    foreach ($payroll as $p) {
        fputcsv($out, [$p['name'], $p['job_title'], $p['branch_name'] ?? '-', $p['salary'], $p['present_days'], $p['absent_days'], $p['leave_annual'] + $p['leave_sick'], $p['total_late_min'], $p['total_work_hours'], $p['total_ot_hours'], $p['late_deduction'], $p['abs_deduction'] + $p['unpaid_deduction'], $p['total_deductions'], $p['ot_payment'], $p['net_salary']]);
    }
    fclose($out);
    exit;
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
.payroll-card { background:var(--surface1); border-radius:12px; padding:20px; border:1px solid var(--surface3) }
.payroll-summary { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:20px }
.payroll-box { text-align:center; padding:16px; border-radius:10px; background:var(--surface2) }
.payroll-box .val { font-size:1.5rem; font-weight:800 }
.payroll-box .lbl { font-size:.78rem; color:var(--text3); margin-top:4px }
.salary-positive { color:#10B981 }
.salary-negative { color:#EF4444 }
.filter-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-top:12px }
</style>
<?php
$reportTitle    = 'كشف الرواتب';
$reportSubtitle = 'نظام الحضور والانصراف';
$reportMeta     = ["الفترة: {$dateFrom} إلى {$dateTo}", "أيام العمل: {$workingDays}", "المعامل: {$otMultiplier}x"];
require __DIR__ . '/../includes/report_print_header.php';
?>

<!-- الفلاتر -->
<div class="report-filter">
    <form method="GET" class="filter-bar">
        <div class="form-group"><label>من تاريخ</label><input class="form-control" type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"></div>
        <div class="form-group"><label>إلى تاريخ</label><input class="form-control" type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"></div>
        <div class="form-group">
            <label>الفرع</label>
            <select class="form-control" name="branch_id">
                <option value="0">الكل</option>
                <?php foreach ($branchesList as $br): ?>
                <option value="<?= $br['id'] ?>" <?= $branchId == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>الموظف</label>
            <select class="form-control" name="employee_id">
                <option value="0">الكل</option>
                <?php foreach ($employeesList as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $employeeId == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><?= svgIcon('attendance', 16) ?> بحث</button>
            <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="btn-export"><?= svgIcon('backup', 16) ?> تصدير CSV</a>
            <button type="button" onclick="window.print()" class="btn-export"><?= svgIcon('document', 16) ?> طباعة</button>
        </div>
    </form>
</div>

<!-- ملخص إجمالي -->
<div class="payroll-summary">
    <div class="payroll-box"><div class="val"><?= count($payroll) ?></div><div class="lbl">عدد الموظفين</div></div>
    <div class="payroll-box"><div class="val"><?= $workingDays ?></div><div class="lbl">أيام العمل</div></div>
    <div class="payroll-box"><div class="val"><?= number_format($grandSalary, 2) ?></div><div class="lbl">إجمالي الرواتب</div></div>
    <div class="payroll-box"><div class="val salary-negative"><?= number_format($grandDeductions, 2) ?></div><div class="lbl">إجمالي الخصومات</div></div>
    <div class="payroll-box"><div class="val salary-positive"><?= number_format($grandOTPayment, 2) ?></div><div class="lbl">بدل الأوفرتايم</div></div>
    <div class="payroll-box" style="border:2px solid var(--primary)"><div class="val salary-positive" style="font-size:1.7rem"><?= number_format($grandNet, 2) ?></div><div class="lbl">صافي المستحقات</div></div>
</div>

<!-- الجدول -->
<div class="report-table-wrap">
    <div class="card-header" style="padding:16px 20px;margin:0;border-bottom:2px solid var(--surface3)">
        <span class="card-title"><span class="card-title-bar"></span> <?= svgIcon('document', 18) ?> كشف الرواتب التفصيلي</span>
    </div>
    <div style="overflow-x:auto">
    <table class="att-table" style="font-size:.82rem">
        <thead><tr>
            <th>#</th><th>الموظف</th><th>الفرع</th><th>الراتب</th>
            <th>حضور</th><th>غياب</th><th>إجازة</th>
            <th>تأخير</th><th>ساعات</th><th>أوفرتايم</th>
            <th>خصم تأخير</th><th>خصم غياب</th><th>بدل OT</th><th>الصافي</th>
        </tr></thead>
        <tbody>
        <?php if (empty($payroll)): ?>
            <tr><td colspan="14" class="report-empty" style="padding:40px"><p>لا يوجد بيانات</p></td></tr>
        <?php else: ?>
            <?php $i = 0; foreach ($payroll as $p): $i++; ?>
            <tr>
                <td><?= $i ?></td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small style="color:var(--text3)"><?= htmlspecialchars($p['job_title']) ?></small></td>
                <td><?= htmlspecialchars($p['branch_name'] ?? '-') ?></td>
                <td><?= number_format($p['salary'], 2) ?></td>
                <td><span class="badge badge-green"><?= $p['present_days'] ?></span></td>
                <td><?php if ($p['absent_days'] > 0): ?><span class="badge badge-red"><?= $p['absent_days'] ?></span><?php else: ?>0<?php endif; ?></td>
                <td><?= $p['leave_annual'] + $p['leave_sick'] ?><?php if ($p['leave_unpaid'] > 0): ?> <small style="color:#EF4444">(+<?= $p['leave_unpaid'] ?> بدون)</small><?php endif; ?></td>
                <td><?php if ($p['total_late_min'] > 0): ?><span class="badge badge-red"><?= $p['total_late_min'] ?>د</span><?php else: ?>0<?php endif; ?></td>
                <td><?= $p['total_work_hours'] ?></td>
                <td><?php if ($p['total_ot_min'] > 0): ?><span class="badge badge-green"><?= $p['total_ot_hours'] ?></span><?php else: ?>-<?php endif; ?></td>
                <td class="salary-negative"><?= $p['late_deduction'] > 0 ? number_format($p['late_deduction'], 2) : '-' ?></td>
                <td class="salary-negative"><?= ($p['abs_deduction'] + $p['unpaid_deduction']) > 0 ? number_format($p['abs_deduction'] + $p['unpaid_deduction'], 2) : '-' ?></td>
                <td class="salary-positive"><?= $p['ot_payment'] > 0 ? number_format($p['ot_payment'], 2) : '-' ?></td>
                <td><strong><?= number_format($p['net_salary'], 2) ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:var(--surface2);font-weight:700">
                <td colspan="3">الإجمالي</td>
                <td><?= number_format($grandSalary, 2) ?></td>
                <td colspan="6"></td>
                <td class="salary-negative"><?= number_format(array_sum(array_column($payroll, 'late_deduction')), 2) ?></td>
                <td class="salary-negative"><?= number_format(array_sum(array_column($payroll, 'abs_deduction')) + array_sum(array_column($payroll, 'unpaid_deduction')), 2) ?></td>
                <td class="salary-positive"><?= number_format($grandOTPayment, 2) ?></td>
                <td><strong><?= number_format($grandNet, 2) ?></strong></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<style>
@page { size: A4 landscape; margin: 10mm 8mm; }
@media print {
    .sidebar, .topbar, .bottom-nav, form, .no-print, .report-filter, .filter-grid { display: none !important; }
    .main-content { margin: 0 !important; }
    .content { padding: 0 !important; max-width: 100% !important; }
    .card, .payroll-card { break-inside: avoid; box-shadow: none !important; border: 1px solid #e5dcc8; }
    .payroll-summary { display: flex !important; flex-wrap: wrap; gap: 8px; }
    .payroll-box { flex: 1 1 22%; border: 1px solid #e5dcc8; }
    .print-report-header, .print-report-footer { display: block !important; }
    table { font-size: 9px; }
    th, td { padding: 4px 5px !important; }
}
</style>

<?php require __DIR__ . '/../includes/report_print_footer.php'; ?>
<?php require __DIR__ . '/../includes/print_settings.php'; ?>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
