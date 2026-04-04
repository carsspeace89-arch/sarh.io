<?php
// =============================================================
// admin/report-builder.php - التقرير المخصص (Report Builder)
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'التقرير المخصص';
$activePage = 'report-builder';

// حذف تقرير محفوظ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_saved') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $sid = (int)($_POST['saved_id'] ?? 0);
        db()->prepare("DELETE FROM saved_reports WHERE id = ? AND admin_id = ?")->execute([$sid, $_SESSION['admin_id']]);
    }
    header('Location: report-builder.php');
    exit;
}

// حفظ تقرير
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_report') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $reportName = trim($_POST['report_name'] ?? '');
        if ($reportName !== '') {
            $filters = $_POST['filters'] ?? '';
            $columns = $_POST['columns'] ?? '';
            try {
                db()->prepare("INSERT INTO saved_reports (admin_id, report_name, report_type, filters_json, columns_json) VALUES (?, ?, 'custom', ?, ?)")
                    ->execute([$_SESSION['admin_id'], $reportName, $filters, $columns]);
            } catch (Exception $e) { /* table may not exist */ }
        }
    }
    header('Location: report-builder.php?' . ($_POST['query_string'] ?? ''));
    exit;
}

// الفلاتر
$dateFrom    = $_GET['date_from'] ?? date('Y-m-01');
$dateTo      = $_GET['date_to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = date('Y-m-d');
if ($dateFrom > $dateTo) { $tmp = $dateFrom; $dateFrom = $dateTo; $dateTo = $tmp; }
$branchId    = (int)($_GET['branch_id'] ?? 0);
$employeeId  = (int)($_GET['employee_id'] ?? 0);
$filterShift = (int)($_GET['shift'] ?? 0);

// الأعمدة المطلوبة
$availableColumns = [
    'name'          => 'اسم الموظف',
    'job_title'     => 'الوظيفة',
    'branch'        => 'الفرع',
    'present_days'  => 'أيام الحضور',
    'absent_days'   => 'أيام الغياب',
    'late_days'     => 'أيام التأخير',
    'late_minutes'  => 'دقائق التأخير',
    'early_days'    => 'أيام التبكير',
    'early_minutes' => 'دقائق التبكير',
    'work_hours'    => 'ساعات العمل',
    'avg_hours'     => 'متوسط يومي',
    'ot_hours'      => 'ساعات الأوفرتايم',
    'leave_days'    => 'أيام الإجازة',
    'attendance_rate'=> 'نسبة الحضور %',
    'punctuality'   => 'نسبة الانضباط %',
];

$selectedCols = $_GET['cols'] ?? array_keys($availableColumns);
if (is_string($selectedCols)) $selectedCols = explode(',', $selectedCols);
$selectedCols = array_intersect($selectedCols, array_keys($availableColumns));
if (empty($selectedCols)) $selectedCols = array_keys($availableColumns);

// إعدادات
$weekendDaysStr = getSystemSetting('weekend_days', '5');
$weekendDays    = array_map('intval', explode(',', $weekendDaysStr));

$holidayDates = [];
try {
    $hStmt = db()->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?");
    $hStmt->execute([$dateFrom, $dateTo]);
    $holidayDates = array_column($hStmt->fetchAll(), 'holiday_date');
} catch (Exception $e) {}

// أيام العمل
$workingDays = 0;
$cur = strtotime($dateFrom);
$endD = strtotime($dateTo);
while ($cur <= $endD) {
    $dow = (int)date('w', $cur);
    $d = date('Y-m-d', $cur);
    if (!in_array($dow, $weekendDays) && !in_array($d, $holidayDates)) $workingDays++;
    $cur = strtotime('+1 day', $cur);
}

// بناء البيانات
$empWhere = ["e.is_active = 1", "e.deleted_at IS NULL"];
$empParams = [];
if ($employeeId > 0) { $empWhere[] = "e.id = ?"; $empParams[] = $employeeId; }
if ($branchId > 0) { $empWhere[] = "e.branch_id = ?"; $empParams[] = $branchId; }
$empWhereStr = implode(' AND ', $empWhere);

$empStmt = db()->prepare("SELECT e.id, e.name, e.job_title, e.branch_id, b.name AS branch_name FROM employees e LEFT JOIN branches b ON e.branch_id = b.id WHERE {$empWhereStr} ORDER BY e.name");
$empStmt->execute($empParams);
$allEmps = $empStmt->fetchAll();

// الحضور
$attWhere = ["a.attendance_date BETWEEN ? AND ?"];
$attParams = [$dateFrom, $dateTo];
if ($filterShift > 0) {
    $sf = buildShiftTimeFilter($filterShift);
    if ($sf) { $attWhere[] = $sf['sql']; $attParams = array_merge($attParams, $sf['params']); }
}
$attWhereStr = implode(' AND ', $attWhere);

$attStmt = db()->prepare("SELECT employee_id, attendance_date, type, timestamp, late_minutes, early_minutes FROM attendances a WHERE {$attWhereStr} ORDER BY employee_id, attendance_date, timestamp");
$attStmt->execute($attParams);
$rawAtt = $attStmt->fetchAll();

$empAtt = [];
foreach ($rawAtt as $a) $empAtt[$a['employee_id']][$a['attendance_date']][] = $a;

// الإجازات
$leaveStmt = db()->prepare("SELECT employee_id, COUNT(*) AS days FROM leaves WHERE status='approved' AND start_date <= ? AND end_date >= ? GROUP BY employee_id");
$leaveStmt->execute([$dateTo, $dateFrom]);
$empLeave = array_column($leaveStmt->fetchAll(), 'days', 'employee_id');

// الحسابات
$reportData = [];
foreach ($allEmps as $emp) {
    $eid = $emp['id'];
    $myAtt = $empAtt[$eid] ?? [];

    $presentDays = 0; $lateDays = 0; $totalLateMin = 0;
    $earlyDays = 0; $totalEarlyMin = 0; $totalWorkMin = 0; $totalOTMin = 0;

    foreach ($myAtt as $date => $recs) {
        $dow = (int)date('w', strtotime($date));
        if (in_array($dow, $weekendDays) || in_array($date, $holidayDates)) continue;

        $hasIn = false; $dayLate = 0; $dayEarly = 0;
        $ins = $outs = $otS = $otE = [];
        foreach ($recs as $r) {
            if ($r['type'] === 'in') { $hasIn = true; $ins[] = strtotime($r['timestamp']); $dayLate += (int)$r['late_minutes']; $dayEarly += (int)$r['early_minutes']; }
            elseif ($r['type'] === 'out') $outs[] = strtotime($r['timestamp']);
            elseif ($r['type'] === 'overtime-start') $otS[] = strtotime($r['timestamp']);
            elseif ($r['type'] === 'overtime-end') $otE[] = strtotime($r['timestamp']);
        }
        if ($hasIn) $presentDays++;
        if ($dayLate > 0) { $lateDays++; $totalLateMin += $dayLate; }
        if ($dayEarly > 0) { $earlyDays++; $totalEarlyMin += $dayEarly; }

        sort($ins); sort($outs);
        if (!empty($ins) && !empty($outs) && end($outs) > $ins[0]) {
            $totalWorkMin += (int)round((end($outs) - $ins[0]) / 60);
        }
        sort($otS); sort($otE);
        for ($i = 0; $i < min(count($otS), count($otE)); $i++) {
            if ($otE[$i] > $otS[$i]) $totalOTMin += (int)round(($otE[$i] - $otS[$i]) / 60);
        }
    }

    $leaveDays = (int)($empLeave[$eid] ?? 0);
    $absentDays = max(0, $workingDays - $presentDays - $leaveDays);
    $attRate = $workingDays > 0 ? round($presentDays / $workingDays * 100, 1) : 0;
    $punctRate = $presentDays > 0 ? round(($presentDays - $lateDays) / $presentDays * 100, 1) : 0;

    $reportData[] = [
        'name'           => $emp['name'],
        'job_title'      => $emp['job_title'],
        'branch'         => $emp['branch_name'] ?? '-',
        'present_days'   => $presentDays,
        'absent_days'    => $absentDays,
        'late_days'      => $lateDays,
        'late_minutes'   => $totalLateMin,
        'early_days'     => $earlyDays,
        'early_minutes'  => $totalEarlyMin,
        'work_hours'     => sprintf('%d:%02d', intdiv($totalWorkMin, 60), $totalWorkMin % 60),
        'avg_hours'      => $presentDays > 0 ? sprintf('%0.1f', $totalWorkMin / $presentDays / 60) : '0',
        'ot_hours'       => sprintf('%d:%02d', intdiv($totalOTMin, 60), $totalOTMin % 60),
        'leave_days'     => $leaveDays,
        'attendance_rate' => $attRate . '%',
        'punctuality'    => $punctRate . '%',
        '_sort_work'     => $totalWorkMin,
    ];
}

// قوائم
$employeesList = db()->query("SELECT id, name FROM employees WHERE is_active=1 AND deleted_at IS NULL ORDER BY name")->fetchAll();
$branchesList  = db()->query("SELECT id, name FROM branches WHERE is_active=1 ORDER BY name")->fetchAll();

// التقارير المحفوظة
$savedReports = [];
try {
    $srStmt = db()->prepare("SELECT * FROM saved_reports WHERE admin_id = ? ORDER BY created_at DESC");
    $srStmt->execute([$_SESSION['admin_id']]);
    $savedReports = $srStmt->fetchAll();
} catch (Exception $e) {}

// CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="custom_report_' . $dateFrom . '_' . $dateTo . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    $headers = [];
    foreach ($selectedCols as $c) $headers[] = $availableColumns[$c];
    fputcsv($out, $headers);
    foreach ($reportData as $row) {
        $csvRow = [];
        foreach ($selectedCols as $c) $csvRow[] = $row[$c];
        fputcsv($out, $csvRow);
    }
    fclose($out);
    exit;
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
.cols-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:8px; margin:12px 0 }
.col-check { display:flex; align-items:center; gap:6px; padding:8px 12px; background:var(--surface2); border-radius:8px; cursor:pointer; transition:.2s; border:2px solid transparent; font-size:.85rem }
.col-check:hover { border-color:var(--primary) }
.col-check.checked { border-color:var(--primary); background:rgba(59,130,246,.08) }
.col-check input { accent-color:var(--primary) }
.saved-list { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px }
.saved-tag { padding:6px 14px; border-radius:20px; background:var(--surface2); border:1px solid var(--surface3); font-size:.82rem; display:flex; align-items:center; gap:6px; cursor:pointer; transition:.2s }
.saved-tag:hover { border-color:var(--primary) }
.saved-tag .del-btn { color:#EF4444; font-weight:700; cursor:pointer; font-size:.9rem }
</style>
<?php
$reportTitle    = 'التقرير المخصص';
$reportSubtitle = 'نظام الحضور والانصراف';
$reportMeta     = ["الفترة: {$dateFrom} إلى {$dateTo}", "أيام العمل: {$workingDays}"];
require __DIR__ . '/../includes/report_print_header.php';
?>

<!-- التقارير المحفوظة -->
<?php if (!empty($savedReports)): ?>
<div style="margin-bottom:16px">
    <h4 style="margin:0 0 8px;font-size:.9rem;color:var(--text3)">التقارير المحفوظة</h4>
    <div class="saved-list">
        <?php foreach ($savedReports as $sr): ?>
        <div class="saved-tag">
            <a href="?<?= htmlspecialchars($sr['filters_json']) ?>&cols=<?= htmlspecialchars($sr['columns_json'] ?? '') ?>" style="text-decoration:none;color:inherit"><?= htmlspecialchars($sr['report_name']) ?></a>
            <form method="POST" style="display:inline" onsubmit="return confirm('حذف؟')">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                <input type="hidden" name="action" value="delete_saved">
                <input type="hidden" name="saved_id" value="<?= $sr['id'] ?>">
                <button type="submit" class="del-btn" style="background:none;border:none;cursor:pointer;color:#EF4444">✕</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- الفلاتر -->
<div class="report-filter">
    <form method="GET" class="filter-bar" id="builderForm">
        <div class="form-group"><label>من تاريخ</label><input class="form-control" type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"></div>
        <div class="form-group"><label>إلى تاريخ</label><input class="form-control" type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"></div>
        <div class="form-group">
            <label>الفرع</label>
            <select class="form-control" name="branch_id" id="branchSelect">
                <option value="0">الكل</option>
                <?php foreach ($branchesList as $br): ?>
                <option value="<?= $br['id'] ?>" <?= $branchId == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>الوردية</label>
            <select class="form-control" name="shift" id="shiftSelect"><option value="0">كل الورديات</option></select>
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

        <!-- اختيار الأعمدة -->
        <div style="grid-column:1/-1">
            <label style="font-weight:600;margin-bottom:8px;display:block">اختر الأعمدة المطلوبة:</label>
            <div class="cols-grid">
                <?php foreach ($availableColumns as $colKey => $colLabel): ?>
                <label class="col-check <?= in_array($colKey, $selectedCols) ? 'checked' : '' ?>">
                    <input type="checkbox" name="cols[]" value="<?= $colKey ?>" <?= in_array($colKey, $selectedCols) ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('checked')">
                    <?= $colLabel ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><?= svgIcon('chart', 16) ?> إنشاء التقرير</button>
            <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="btn-export"><?= svgIcon('backup', 16) ?> تصدير CSV</a>
            <button type="button" onclick="window.print()" class="btn-export"><?= svgIcon('document', 16) ?> طباعة</button>
            <button type="button" class="btn-export" onclick="saveReport()" title="حفظ هذا التقرير"><?= svgIcon('star', 16) ?> حفظ</button>
        </div>
    </form>
</div>

<!-- الإحصائيات -->
<div class="report-stats">
    <div class="report-stat accent-blue"><div class="report-stat-icon is-blue"><?= svgIcon('employees', 24) ?></div><div><div class="report-stat-value"><?= count($reportData) ?></div><div class="report-stat-label">موظف</div></div></div>
    <div class="report-stat accent-green"><div class="report-stat-icon is-green"><?= svgIcon('calendar', 24) ?></div><div><div class="report-stat-value"><?= $workingDays ?></div><div class="report-stat-label">يوم عمل</div></div></div>
    <div class="report-stat accent-orange"><div class="report-stat-icon is-orange"><?= svgIcon('chart', 24) ?></div><div><div class="report-stat-value"><?= count($selectedCols) ?></div><div class="report-stat-label">أعمدة مختارة</div></div></div>
</div>

<!-- الجدول -->
<div class="report-table-wrap">
    <div class="card-header" style="padding:16px 20px;margin:0;border-bottom:2px solid var(--surface3)">
        <span class="card-title"><span class="card-title-bar"></span> <?= svgIcon('document', 18) ?> نتائج التقرير</span>
        <span class="badge badge-blue"><?= count($reportData) ?> موظف</span>
    </div>
    <div style="overflow-x:auto">
    <table class="att-table" id="reportTable">
        <thead><tr>
            <th>#</th>
            <?php foreach ($selectedCols as $c): ?>
            <th style="cursor:pointer" onclick="sortTable(this)"><?= $availableColumns[$c] ?> ⇅</th>
            <?php endforeach; ?>
        </tr></thead>
        <tbody>
        <?php if (empty($reportData)): ?>
            <tr><td colspan="<?= count($selectedCols) + 1 ?>" class="report-empty" style="padding:40px"><p>لا يوجد بيانات</p></td></tr>
        <?php else: ?>
            <?php foreach ($reportData as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <?php foreach ($selectedCols as $c): ?>
                <td><?php
                    $v = $row[$c];
                    if (in_array($c, ['late_days', 'late_minutes', 'absent_days']) && $v > 0) echo '<span class="badge badge-red">' . $v . '</span>';
                    elseif (in_array($c, ['early_days', 'early_minutes', 'present_days']) && $v > 0) echo '<span class="badge badge-green">' . $v . '</span>';
                    elseif ($c === 'name') echo '<strong>' . htmlspecialchars($v) . '</strong>';
                    else echo htmlspecialchars($v);
                ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
function saveReport() {
    const name = prompt('اسم التقرير المحفوظ:');
    if (!name) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="action" value="save_report">
        <input type="hidden" name="report_name" value="${name}">
        <input type="hidden" name="filters" value="${new URLSearchParams(new FormData(document.getElementById('builderForm'))).toString()}">
        <input type="hidden" name="columns" value="<?= implode(',', $selectedCols) ?>">
        <input type="hidden" name="query_string" value="${window.location.search.substring(1)}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function sortTable(th) {
    const table = document.getElementById('reportTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const col = Array.from(th.parentElement.children).indexOf(th);
    const dir = th.dataset.dir === 'asc' ? 'desc' : 'asc';
    th.dataset.dir = dir;
    rows.sort((a, b) => {
        let va = a.cells[col]?.textContent.trim() || '';
        let vb = b.cells[col]?.textContent.trim() || '';
        const na = parseFloat(va.replace(/[^0-9.\-]/g, ''));
        const nb = parseFloat(vb.replace(/[^0-9.\-]/g, ''));
        if (!isNaN(na) && !isNaN(nb)) return dir === 'asc' ? na - nb : nb - na;
        return dir === 'asc' ? va.localeCompare(vb, 'ar') : vb.localeCompare(va, 'ar');
    });
    rows.forEach(r => tbody.appendChild(r));
}

// تحميل الورديات
const allShifts = <?= json_encode(getAllBranchShifts()) ?>;
document.getElementById('branchSelect')?.addEventListener('change', function() {
    const sel = document.getElementById('shiftSelect');
    sel.innerHTML = '<option value="0">كل الورديات</option>';
    (allShifts[this.value] || []).forEach(s => sel.innerHTML += `<option value="${s.id}">الوردية ${s.num} (${s.start} - ${s.end})</option>`);
});
(function() {
    const bv = document.getElementById('branchSelect')?.value;
    if (bv && bv !== '0' && allShifts[bv]) {
        const sel = document.getElementById('shiftSelect');
        sel.innerHTML = '<option value="0">كل الورديات</option>';
        allShifts[bv].forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id; opt.textContent = `الوردية ${s.num} (${s.start} - ${s.end})`;
            if (s.id == <?= $filterShift ?>) opt.selected = true;
            sel.appendChild(opt);
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
