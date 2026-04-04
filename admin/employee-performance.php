<?php
// =============================================================
// admin/employee-performance.php - بطاقة أداء الموظف
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'بطاقة أداء الموظف';
$activePage = 'employee-performance';

// الفلاتر
$employeeId  = (int)($_GET['employee_id'] ?? 0);
$dateFrom    = $_GET['date_from'] ?? date('Y-m-01');
$dateTo      = $_GET['date_to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = date('Y-m-d');

// إعدادات النظام
$weekendDaysStr = getSystemSetting('weekend_days', '5');
$weekendDays    = array_map('intval', explode(',', $weekendDaysStr));

// العطل الرسمية
$holidayDates = [];
try {
    $hStmt = db()->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?");
    $hStmt->execute([$dateFrom, $dateTo]);
    $holidayDates = array_column($hStmt->fetchAll(), 'holiday_date');
} catch (Exception $e) {}

// أيام العمل في الفترة
$workingDays = 0;
$current = strtotime($dateFrom);
$end     = strtotime($dateTo);
while ($current <= $end) {
    $dow = (int)date('w', $current);
    $d   = date('Y-m-d', $current);
    if (!in_array($dow, $weekendDays) && !in_array($d, $holidayDates)) $workingDays++;
    $current = strtotime('+1 day', $current);
}

// قائمة الموظفين
$employeesList = db()->query("SELECT id, name FROM employees WHERE is_active=1 AND deleted_at IS NULL ORDER BY name")->fetchAll();

$emp = null;
$perf = null;

if ($employeeId > 0) {
    // بيانات الموظف
    $empStmt = db()->prepare("
        SELECT e.*, b.name AS branch_name
        FROM employees e LEFT JOIN branches b ON e.branch_id = b.id
        WHERE e.id = ?
    ");
    $empStmt->execute([$employeeId]);
    $emp = $empStmt->fetch();

    if ($emp) {
        // الحضور
        $attStmt = db()->prepare("
            SELECT attendance_date, type, timestamp, late_minutes, early_minutes
            FROM attendances
            WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?
            ORDER BY attendance_date, timestamp
        ");
        $attStmt->execute([$employeeId, $dateFrom, $dateTo]);
        $allAtt = $attStmt->fetchAll();

        // تجميع
        $dayData = [];
        foreach ($allAtt as $a) {
            $dayData[$a['attendance_date']][] = $a;
        }

        $presentDays = 0;
        $lateDays    = 0;
        $totalLateMin = 0;
        $earlyDays   = 0;
        $totalEarlyMin = 0;
        $totalWorkMin = 0;
        $totalOTMin   = 0;
        $dailyHours   = [];
        $dailyChart   = [];

        foreach ($dayData as $date => $recs) {
            $dow = (int)date('w', strtotime($date));
            if (in_array($dow, $weekendDays) || in_array($date, $holidayDates)) continue;

            $ins = $outs = $otStarts = $otEnds = [];
            $dayLate = 0; $dayEarly = 0; $hasIn = false;
            foreach ($recs as $r) {
                if ($r['type'] === 'in') {
                    $hasIn = true;
                    $ins[] = strtotime($r['timestamp']);
                    $dayLate += (int)$r['late_minutes'];
                    $dayEarly += (int)$r['early_minutes'];
                } elseif ($r['type'] === 'out') {
                    $outs[] = strtotime($r['timestamp']);
                } elseif ($r['type'] === 'overtime-start') {
                    $otStarts[] = strtotime($r['timestamp']);
                } elseif ($r['type'] === 'overtime-end') {
                    $otEnds[] = strtotime($r['timestamp']);
                }
            }

            if ($hasIn) $presentDays++;
            if ($dayLate > 0)  { $lateDays++; $totalLateMin += $dayLate; }
            if ($dayEarly > 0) { $earlyDays++; $totalEarlyMin += $dayEarly; }

            sort($ins); sort($outs);
            $wm = 0;
            if (!empty($ins) && !empty($outs) && end($outs) > $ins[0]) {
                $wm = (int)round((end($outs) - $ins[0]) / 60);
            }
            $totalWorkMin += $wm;
            $dailyHours[] = $wm;

            sort($otStarts); sort($otEnds);
            $otm = 0;
            for ($i = 0; $i < min(count($otStarts), count($otEnds)); $i++) {
                if ($otEnds[$i] > $otStarts[$i]) $otm += (int)round(($otEnds[$i] - $otStarts[$i]) / 60);
            }
            $totalOTMin += $otm;

            $dailyChart[] = ['date' => $date, 'hours' => round($wm / 60, 1), 'late' => $dayLate, 'ot' => round($otm / 60, 1)];
        }

        // الإجازات
        $leaveStmt = db()->prepare("
            SELECT leave_type, SUM(DATEDIFF(LEAST(end_date, ?), GREATEST(start_date, ?)) + 1) AS days
            FROM leaves
            WHERE employee_id = ? AND status = 'approved' AND start_date <= ? AND end_date >= ?
            GROUP BY leave_type
        ");
        $leaveStmt->execute([$dateTo, $dateFrom, $employeeId, $dateTo, $dateFrom]);
        $leaveSummary = [];
        foreach ($leaveStmt->fetchAll() as $l) $leaveSummary[$l['leave_type']] = (int)$l['days'];
        $totalLeave = array_sum($leaveSummary);

        $absentDays = max(0, $workingDays - $presentDays - $totalLeave);
        $attendanceRate = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;
        $punctualityRate = $presentDays > 0 ? round((($presentDays - $lateDays) / $presentDays) * 100, 1) : 0;
        $avgWorkHours = $presentDays > 0 ? round($totalWorkMin / $presentDays / 60, 1) : 0;

        // نقاط الأداء (100 نقطة)
        $score = 0;
        $score += min(40, $attendanceRate * 0.4);          // 40 نقطة للحضور
        $score += min(30, $punctualityRate * 0.3);          // 30 نقطة للانضباط
        $score += min(15, $earlyDays > 0 ? 15 : 0);        // 15 نقطة للتبكير
        $score += min(15, ($totalOTMin > 0 ? 15 : 0));     // 15 نقطة للأوفرتايم
        $score = round($score);

        $scoreColor = $score >= 80 ? '#10B981' : ($score >= 60 ? '#F59E0B' : '#EF4444');
        $scoreLabel = $score >= 80 ? 'ممتاز' : ($score >= 60 ? 'جيد' : 'يحتاج تحسين');

        $perf = compact('presentDays', 'lateDays', 'totalLateMin', 'earlyDays', 'totalEarlyMin',
            'totalWorkMin', 'totalOTMin', 'absentDays', 'attendanceRate', 'punctualityRate',
            'avgWorkHours', 'leaveSummary', 'totalLeave', 'score', 'scoreColor', 'scoreLabel', 'dailyChart');
    }
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
.perf-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-bottom:20px }
.perf-card { background:var(--surface1); border-radius:12px; padding:20px; border:1px solid var(--surface3) }
.perf-card h3 { font-size:.9rem; color:var(--text3); margin:0 0 12px; border-bottom:1px solid var(--surface3); padding-bottom:8px }
.perf-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px dashed var(--surface3) }
.perf-row:last-child { border:none }
.perf-row .label { color:var(--text2); font-size:.85rem }
.perf-row .value { font-weight:700; font-size:.95rem }
.score-ring { width:140px; height:140px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-direction:column; margin:0 auto 12px; position:relative }
.score-ring .score-val { font-size:2.2rem; font-weight:900 }
.score-ring .score-lbl { font-size:.82rem; margin-top:2px }
.emp-header { display:flex; align-items:center; gap:16px; padding:20px; background:var(--surface1); border-radius:12px; border:1px solid var(--surface3); margin-bottom:20px }
.emp-header .emp-photo { width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid var(--primary) }
.emp-header .emp-info h2 { margin:0; font-size:1.2rem }
.emp-header .emp-info p { margin:2px 0; color:var(--text3); font-size:.85rem }
.chart-wrap { background:var(--surface1); border-radius:12px; padding:20px; border:1px solid var(--surface3); margin-bottom:20px }
</style>
<?php
$reportTitle    = 'بطاقة أداء الموظف';
$reportSubtitle = 'نظام الحضور والانصراف';
$reportMeta     = ["الفترة: {$dateFrom} إلى {$dateTo}"];
require __DIR__ . '/../includes/report_print_header.php';
?>

<!-- الفلاتر -->
<div class="report-filter">
    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label>الموظف</label>
            <select class="form-control" name="employee_id" required>
                <option value="">-- اختر موظف --</option>
                <?php foreach ($employeesList as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $employeeId == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>من تاريخ</label><input class="form-control" type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"></div>
        <div class="form-group"><label>إلى تاريخ</label><input class="form-control" type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"></div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><?= svgIcon('attendance', 16) ?> عرض الأداء</button>
            <button type="button" onclick="window.print()" class="btn-export"><?= svgIcon('document', 16) ?> طباعة</button>
        </div>
    </form>
</div>

<?php if ($emp && $perf): ?>

<!-- بيانات الموظف -->
<div class="emp-header">
    <?php if (!empty($emp['profile_photo'])): ?>
    <img src="<?= SITE_URL ?>/api/serve-file.php?f=<?= urlencode($emp['profile_photo']) ?>" class="emp-photo" alt="">
    <?php else: ?>
    <div class="emp-photo" style="background:linear-gradient(135deg,var(--royal-navy,#0f1b33),#1a2744);display:flex;align-items:center;justify-content:center;color:var(--royal-gold-light,#e8d9a0);font-size:1.5rem;font-weight:800;border:3px solid var(--royal-gold,#c9a84c)"><?= mb_substr($emp['name'], 0, 1) ?></div>
    <?php endif; ?>
    <div class="emp-info">
        <h2><?= htmlspecialchars($emp['name']) ?></h2>
        <p><?= htmlspecialchars($emp['job_title']) ?> — <?= htmlspecialchars($emp['branch_name'] ?? '-') ?></p>
        <p>الفترة: <?= $dateFrom ?> إلى <?= $dateTo ?> (<?= $workingDays ?> يوم عمل)</p>
    </div>
    <div style="margin-right:auto">
        <div class="score-ring" style="border:6px solid <?= $perf['scoreColor'] ?>">
            <div class="score-val" style="color:<?= $perf['scoreColor'] ?>"><?= $perf['score'] ?></div>
            <div class="score-lbl"><?= $perf['scoreLabel'] ?></div>
        </div>
    </div>
</div>

<!-- البطاقات -->
<div class="perf-grid">
    <!-- الحضور -->
    <div class="perf-card">
        <h3><?= svgIcon('attendance', 16) ?> الحضور والغياب</h3>
        <div class="perf-row"><span class="label">أيام الحضور</span><span class="value" style="color:#10B981"><?= $perf['presentDays'] ?> / <?= $workingDays ?></span></div>
        <div class="perf-row"><span class="label">أيام الغياب</span><span class="value" style="color:<?= $perf['absentDays'] > 0 ? '#EF4444' : 'var(--text1)' ?>"><?= $perf['absentDays'] ?></span></div>
        <div class="perf-row"><span class="label">نسبة الحضور</span><span class="value"><?= $perf['attendanceRate'] ?>%</span></div>
        <div class="perf-row"><span class="label">الإجازات</span><span class="value"><?= $perf['totalLeave'] ?> يوم</span></div>
    </div>

    <!-- الانضباط -->
    <div class="perf-card">
        <h3><?= svgIcon('clock', 16) ?> الانضباط</h3>
        <div class="perf-row"><span class="label">أيام التأخير</span><span class="value" style="color:<?= $perf['lateDays'] > 0 ? '#EF4444' : '#10B981' ?>"><?= $perf['lateDays'] ?></span></div>
        <div class="perf-row"><span class="label">إجمالي التأخير</span><span class="value"><?= $perf['totalLateMin'] ?> دقيقة</span></div>
        <div class="perf-row"><span class="label">نسبة الانضباط</span><span class="value"><?= $perf['punctualityRate'] ?>%</span></div>
        <div class="perf-row"><span class="label">أيام التبكير</span><span class="value" style="color:#10B981"><?= $perf['earlyDays'] ?></span></div>
    </div>

    <!-- ساعات العمل -->
    <div class="perf-card">
        <h3><?= svgIcon('overtime', 16) ?> ساعات العمل</h3>
        <div class="perf-row"><span class="label">إجمالي الساعات</span><span class="value"><?= sprintf('%d:%02d', intdiv($perf['totalWorkMin'], 60), $perf['totalWorkMin'] % 60) ?></span></div>
        <div class="perf-row"><span class="label">متوسط يومي</span><span class="value"><?= $perf['avgWorkHours'] ?> ساعة</span></div>
        <div class="perf-row"><span class="label">ساعات الأوفرتايم</span><span class="value" style="color:#3B82F6"><?= sprintf('%d:%02d', intdiv($perf['totalOTMin'], 60), $perf['totalOTMin'] % 60) ?></span></div>
        <div class="perf-row"><span class="label">إجمالي التبكير</span><span class="value"><?= $perf['totalEarlyMin'] ?> دقيقة</span></div>
    </div>

    <!-- الإجازات -->
    <div class="perf-card">
        <h3><?= svgIcon('leave', 16) ?> تفاصيل الإجازات</h3>
        <div class="perf-row"><span class="label">سنوية</span><span class="value"><?= $perf['leaveSummary']['annual'] ?? 0 ?> يوم</span></div>
        <div class="perf-row"><span class="label">مرضية</span><span class="value"><?= $perf['leaveSummary']['sick'] ?? 0 ?> يوم</span></div>
        <div class="perf-row"><span class="label">بدون راتب</span><span class="value"><?= $perf['leaveSummary']['unpaid'] ?? 0 ?> يوم</span></div>
        <div class="perf-row"><span class="label">أخرى</span><span class="value"><?= $perf['leaveSummary']['other'] ?? 0 ?> يوم</span></div>
    </div>
</div>

<!-- رسم بياني يومي -->
<div class="chart-wrap">
    <h3 style="margin:0 0 16px"><?= svgIcon('chart', 16) ?> الأداء اليومي</h3>
    <canvas id="perfChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const chartData = <?= json_encode($perf['dailyChart']) ?>;
new Chart(document.getElementById('perfChart'), {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.date),
        datasets: [
            { label: 'ساعات العمل', data: chartData.map(d => d.hours), backgroundColor: '#3B82F6', borderRadius: 4, order: 2 },
            { label: 'أوفرتايم', data: chartData.map(d => d.ot), backgroundColor: '#10B981', borderRadius: 4, order: 3 },
            { label: 'تأخير (دقيقة)', data: chartData.map(d => d.late), type: 'line', borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.3, yAxisID: 'y2', order: 1 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { font: { family: 'Tajawal' } } } },
        scales: {
            y: { title: { display: true, text: 'ساعات', font: { family: 'Tajawal' } }, beginAtZero: true },
            y2: { position: 'left', title: { display: true, text: 'تأخير (دقيقة)', font: { family: 'Tajawal' } }, beginAtZero: true, grid: { display: false } },
            x: { ticks: { font: { family: 'Tajawal', size: 10 } } }
        }
    }
});
</script>

<?php elseif ($employeeId > 0 && !$emp): ?>
<div class="report-empty" style="padding:60px;text-align:center"><p>الموظف غير موجود</p></div>
<?php else: ?>
<div class="report-empty" style="padding:60px;text-align:center">
    <div style="font-size:3rem;margin-bottom:12px"><?= svgIcon('user', 48) ?></div>
    <p>اختر موظفاً لعرض بطاقة الأداء</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
