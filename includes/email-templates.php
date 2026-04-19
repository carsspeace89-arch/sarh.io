<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// includes/email-templates.php - قوالب التقارير (تصميم صرح الملكي)
// =============================================================

/**
 * القالب الرئيسي — مطابق لتصميم نظام صرح
 */
function getBaseTemplate(string $title, string $subtitle, string $bodyContent, string $type = 'default', string $periodLabel = ''): string {
    $date = date('Y-m-d');
    $time = date('h:i A');
    $colors = getReportColors($type);
    $periodHtml = $periodLabel ? "<div style=\"display:inline-block;background:rgba(255,255,255,0.18);padding:5px 16px;border-radius:20px;font-size:12px;margin-top:10px;color:#fff;\">📅 {$periodLabel}</div>" : '';
    
    return '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>' . e($title) . '</title>
<style>
@import url("https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap");
*{font-family:"Tajawal",sans-serif;margin:0;padding:0;box-sizing:border-box}
body{background:#F5F7FA;padding:24px 12px}
.wrap{max-width:700px;margin:0 auto}
.top-bar{background:#0f1b33;padding:10px 24px;border-radius:14px 14px 0 0;display:flex;justify-content:space-between;align-items:center}
.top-bar span{color:rgba(255,255,255,.6);font-size:11px}
.top-bar .brand{color:#c9a84c;font-weight:800;font-size:13px;letter-spacing:.5px}
.container{background:linear-gradient(180deg,#fff 0%,#FEFCF8 100%);border:1px solid #e8dcc5;border-top:none;border-radius:0 0 14px 14px;overflow:hidden;box-shadow:0 4px 24px rgba(201,168,76,.12),0 1px 3px rgba(0,0,0,.06)}
.header{background:linear-gradient(135deg,' . $colors['primary'] . ' 0%,' . $colors['secondary'] . ' 100%);padding:32px 24px;text-align:center;position:relative}
.header::after{content:"";position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#c9a84c,#e8d9a0,#c9a84c)}
.header-icon{font-size:40px;margin-bottom:8px}
.header h1{color:#fff;font-size:22px;font-weight:800;margin:0 0 4px;letter-spacing:-.3px}
.header p{color:rgba(255,255,255,.85);font-size:13px;font-weight:300;margin:0}
.header .date-pill{display:inline-block;background:rgba(255,255,255,.18);padding:5px 16px;border-radius:20px;font-size:12px;margin-top:10px;color:#fff}
.body{padding:24px}
.summary-grid{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.stat-card{flex:1;min-width:130px;background:linear-gradient(180deg,#fff 0%,#FDFBF6 100%);border:1px solid #e8dcc5;border-radius:14px;padding:18px 14px;text-align:center;position:relative;overflow:hidden}
.stat-card::before{content:"";position:absolute;top:0;right:0;width:4px;height:100%;background:linear-gradient(180deg,#c9a84c,#a88a2a);border-radius:0 14px 14px 0}
.stat-card.danger::before{background:linear-gradient(180deg,#D42B2B,#a31f1f)}
.stat-card.warning::before{background:linear-gradient(180deg,#C78B06,#9a6c04)}
.stat-card.success::before{background:linear-gradient(180deg,#0D9668,#0a7a54)}
.stat-value{font-size:32px;font-weight:800;color:#0f1b33;line-height:1;margin-bottom:4px}
.stat-value.danger{color:#D42B2B}
.stat-value.warning{color:#C78B06}
.stat-value.success{color:#0D9668}
.stat-value.gold{color:#a88a2a}
.stat-label{font-size:12px;color:#7A8B9F;font-weight:500}
.section-hdr{display:flex;align-items:center;gap:8px;margin:20px 0 12px;padding-bottom:10px;border-bottom:2px solid #ECF0F5}
.section-hdr h3{font-size:16px;font-weight:700;color:#151D2B;margin:0}
.section-hdr .count{background:linear-gradient(135deg,#c9a84c,#a88a2a);color:#fff;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.tbl-wrap{border-radius:14px;overflow:hidden;border:1px solid #e8dcc5;margin:12px 0}
table{width:100%;border-collapse:collapse}
thead th{background:linear-gradient(180deg,#f7f3e8 0%,#f0ead8 100%);color:#0f1b33;padding:12px 14px;font-weight:700;font-size:12px;letter-spacing:.03em;border-bottom:2px solid #c9a84c;text-align:right;white-space:nowrap}
tbody td{padding:11px 14px;font-size:13px;color:#354256;border-bottom:1px solid #ECF0F5}
tbody tr:nth-child(even){background:#fdfbf6}
tbody tr:hover{background:#fdf8ed}
td.num{color:#7A8B9F;font-weight:600;font-size:11px;text-align:center;width:36px}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-green{background:#D4F5E7;color:#0D9668}
.badge-red{background:#FCE4E4;color:#D42B2B}
.badge-blue{background:#D6E8FC;color:#1D6FE0}
.badge-yellow{background:#FDF2D0;color:#C78B06}
.badge-orange{background:#FDE8D8;color:#C2570E}
.badge-purple{background:#EBE3FC;color:#6D31D9}
.late-bar{display:inline-flex;align-items:center;gap:4px}
.late-bar .bar{display:inline-block;height:6px;border-radius:3px;min-width:16px;max-width:70px}
.late-bar .bar.red{background:linear-gradient(90deg,#fbbf24,#D42B2B)}
.late-bar .bar.green{background:linear-gradient(90deg,#34d399,#0D9668)}
.late-bar strong{font-size:12px;color:#354256}
.info-box{border-radius:10px;padding:14px;margin:14px 0;display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#354256;line-height:1.6}
.info-box.warn{background:#FDF2D0;border:1px solid #fde68a}
.info-box.ok{background:#D4F5E7;border:1px solid #A7F3D0}
.info-box.info{background:#D6E8FC;border:1px solid #93C5FD}
.empty{text-align:center;padding:40px 16px}
.empty .icon{font-size:44px;margin-bottom:10px}
.empty .text{color:#7A8B9F;font-size:15px}
.progress-bar{display:flex;height:20px;border-radius:10px;overflow:hidden;background:#ECF0F5;margin:16px 0}
.progress-bar div{transition:width .5s}
.footer{background:linear-gradient(180deg,#f7f3e8 0%,#f0ead8 100%);padding:20px 24px;text-align:center;border-top:2px solid #c9a84c}
.footer .logo{font-size:18px;font-weight:800;color:#0f1b33;margin-bottom:4px}
.footer .logo span{color:#c9a84c}
.footer .gold-line{width:50px;height:3px;background:linear-gradient(90deg,#a88a2a,#c9a84c,#e8d9a0);margin:8px auto;border-radius:2px}
.footer p{color:#7A8B9F;font-size:11px;line-height:1.6;margin:0}
.footer a{color:#c9a84c;text-decoration:none;font-weight:600;font-size:12px}
@media(max-width:600px){body{padding:8px}.header{padding:24px 16px}.body{padding:16px}.summary-grid{flex-direction:column}.stat-card{min-width:100%}th,td{padding:8px 10px;font-size:11px}}
</style>
</head>
<body>
<div class="wrap">
<div class="top-bar">
<span class="brand">صرح | SARH</span>
<span>' . $date . ' &bull; ' . $time . '</span>
</div>
<div class="container">
<div class="header">
<div class="header-icon">' . $colors['icon'] . '</div>
<h1>' . e($title) . '</h1>
<p>' . e($subtitle) . '</p>
<div class="date-pill">🕐 ' . $date . ' — ' . $time . '</div>
' . $periodHtml . '
</div>
<div class="body">
' . $bodyContent . '
</div>
<div class="footer">
<div class="logo">صرح | <span>SARH</span></div>
<div class="gold-line"></div>
<p>رسالة تلقائية من نظام صرح لإدارة الحضور والانصراف<br>
المرسل: etgan@sarh.io<br>
© ' . date('Y') . ' جميع الحقوق محفوظة</p>
<div style="margin-top:10px"><a href="https://sarh.io/admin/">🔗 لوحة التحكم</a></div>
</div>
</div>
</div>
</body>
</html>';
}

/**
 * ألوان التقارير — مبنية على ألوان النظام
 */
function getReportColors(string $type): array {
    $schemes = [
        'daily'    => ['primary'=>'#1a2744','secondary'=>'#0f1b33','accent'=>'#c9a84c','icon'=>'📋'],
        'late'     => ['primary'=>'#D42B2B','secondary'=>'#a31f1f','accent'=>'#c9a84c','icon'=>'⏰'],
        'absent'   => ['primary'=>'#C78B06','secondary'=>'#9a6c04','accent'=>'#c9a84c','icon'=>'🚫'],
        'overtime' => ['primary'=>'#0D9668','secondary'=>'#0a7a54','accent'=>'#c9a84c','icon'=>'⏱️'],
        'monthly'  => ['primary'=>'#6D31D9','secondary'=>'#5521b3','accent'=>'#c9a84c','icon'=>'📊'],
        'summary'  => ['primary'=>'#1D6FE0','secondary'=>'#1558b3','accent'=>'#c9a84c','icon'=>'📈'],
        'payroll'  => ['primary'=>'#0D9668','secondary'=>'#0a7a54','accent'=>'#c9a84c','icon'=>'💰'],
        'default'  => ['primary'=>'#0f1b33','secondary'=>'#1a2744','accent'=>'#c9a84c','icon'=>'📧'],
    ];
    return $schemes[$type] ?? $schemes['default'];
}

function rowNum(int $i): string {
    return '<td class="num">' . $i . '</td>';
}

function minutesBar(int $minutes, string $type = 'red', int $max = 120): string {
    $w = min(70, max(16, ($minutes / $max) * 70));
    return '<span class="late-bar"><span class="bar ' . $type . '" style="width:' . $w . 'px"></span><strong>' . $minutes . ' د</strong></span>';
}

function getHijriApprox(): string { return ''; }

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// قوالب التقارير
// ============================================================

/**
 * تقرير الحضور اليومي
 */
function buildDailyReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'تقرير الحضور والانصراف', '<div class="empty"><div class="icon">📭</div><div class="text">لا توجد سجلات حضور لهذه الفترة</div></div>', 'daily', $periodLabel);
    }
    
    $ins = count(array_filter($data, fn($r) => ($r['type'] ?? '') === 'in'));
    $outs = count(array_filter($data, fn($r) => ($r['type'] ?? '') === 'out'));
    $lates = count(array_filter($data, fn($r) => ($r['late_minutes'] ?? 0) > 0));
    
    $b = '<div class="summary-grid">
        <div class="stat-card"><div class="stat-value success">' . $ins . '</div><div class="stat-label">✅ تسجيل حضور</div></div>
        <div class="stat-card"><div class="stat-value gold">' . $outs . '</div><div class="stat-label">🚪 تسجيل انصراف</div></div>
        <div class="stat-card danger"><div class="stat-value danger">' . $lates . '</div><div class="stat-label">⏰ متأخرون</div></div>
        <div class="stat-card"><div class="stat-value">' . $count . '</div><div class="stat-label">📋 إجمالي السجلات</div></div>
    </div>';
    
    $b .= '<div class="section-hdr"><span style="font-size:20px">📋</span><h3>تفاصيل الحضور</h3><span class="count">' . $count . ' سجل</span></div>';
    $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th><th>الفرع</th><th>النوع</th><th>الوقت</th><th>التأخير</th></tr></thead><tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $typ = ($r['type'] ?? '') === 'in' ? '<span class="badge badge-green">⬅️ حضور</span>' : '<span class="badge badge-blue">➡️ انصراف</span>';
        $tm = !empty($r['timestamp']) ? date('h:i A', strtotime($r['timestamp'])) : '-';
        $late = (int)($r['late_minutes'] ?? 0);
        $lh = $late > 0 ? minutesBar($late) : '<span style="color:#0D9668">✓</span>';
        $b .= '<tr>' . rowNum($i++) . '<td><strong>' . e($r['name'] ?? '') . '</strong></td><td>' . e($r['branch_name'] ?? '-') . '</td><td>' . $typ . '</td><td style="font-weight:600">' . $tm . '</td><td>' . $lh . '</td></tr>';
    }
    $b .= '</tbody></table></div>';
    
    return getBaseTemplate($title, 'تقرير الحضور والانصراف', $b, 'daily', $periodLabel);
}

/**
 * تقرير المتأخرين
 */
function buildLateReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'تقرير المتأخرين', '<div class="empty"><div class="icon">🎉</div><div class="text">لا يوجد متأخرون! أداء ممتاز</div></div>', 'late', $periodLabel);
    }
    
    $totalMin = array_sum(array_column($data, 'late_minutes'));
    $avg = round($totalMin / $count);
    $mx = max(array_column($data, 'late_minutes'));
    
    $b = '<div class="summary-grid">
        <div class="stat-card danger"><div class="stat-value danger">' . $count . '</div><div class="stat-label">⏰ عدد المتأخرين</div></div>
        <div class="stat-card warning"><div class="stat-value warning">' . $totalMin . '<small style="font-size:13px"> د</small></div><div class="stat-label">⏳ إجمالي التأخير</div></div>
        <div class="stat-card"><div class="stat-value">' . $avg . '<small style="font-size:13px"> د</small></div><div class="stat-label">📊 المتوسط</div></div>
        <div class="stat-card danger"><div class="stat-value danger">' . $mx . '<small style="font-size:13px"> د</small></div><div class="stat-label">🔴 الأعلى</div></div>
    </div>';
    
    if ($count >= 5) {
        $b .= '<div class="info-box warn"><span>⚠️</span><span><strong>تنبيه:</strong> عدد المتأخرين مرتفع (' . $count . ' موظف). يرجى المتابعة.</span></div>';
    }
    
    $b .= '<div class="section-hdr"><span style="font-size:20px">⏰</span><h3>قائمة المتأخرين</h3><span class="count">' . $count . '</span></div>';
    $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th><th>الفرع</th><th>وقت الحضور</th><th>مدة التأخير</th></tr></thead><tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $tm = !empty($r['timestamp']) ? date('h:i A', strtotime($r['timestamp'])) : '-';
        $late = (int)($r['late_minutes'] ?? 0);
        $rs = $late > 30 ? ' style="background:#FCE4E4"' : '';
        $b .= '<tr' . $rs . '>' . rowNum($i++) . '<td><strong>' . e($r['name'] ?? '') . '</strong></td><td>' . e($r['branch_name'] ?? '-') . '</td><td style="font-weight:600">' . $tm . '</td><td>' . minutesBar($late) . '</td></tr>';
    }
    $b .= '</tbody></table></div>';
    
    return getBaseTemplate($title, 'تقرير الموظفين المتأخرين', $b, 'late', $periodLabel);
}

/**
 * تقرير الغائبين
 */
function buildAbsentReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'تقرير الغائبين', '<div class="empty"><div class="icon">🌟</div><div class="text">جميع الموظفين حضروا! عمل رائع</div></div>', 'absent', $periodLabel);
    }
    
    $b = '<div class="summary-grid"><div class="stat-card danger"><div class="stat-value danger">' . $count . '</div><div class="stat-label">🚫 عدد الغائبين</div></div></div>';
    
    if ($count >= 3) {
        $b .= '<div class="info-box warn"><span>📢</span><span>' . $count . ' موظف لم يسجلوا حضورهم. يرجى التحقق من الإجازات والتصاريح.</span></div>';
    }
    
    // تجميع حسب الفرع
    $byBranch = [];
    foreach ($data as $r) { $byBranch[$r['branch_name'] ?? 'بدون فرع'][] = $r; }
    
    foreach ($byBranch as $br => $emps) {
        $b .= '<div class="section-hdr"><span style="font-size:20px">🏢</span><h3>' . e($br) . '</h3><span class="count">' . count($emps) . '</span></div>';
        $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th></tr></thead><tbody>';
        $i = 1;
        foreach ($emps as $r) {
            $b .= '<tr>' . rowNum($i++) . '<td><strong>' . e($r['name'] ?? '') . '</strong></td></tr>';
        }
        $b .= '</tbody></table></div>';
    }
    
    return getBaseTemplate($title, 'تقرير الموظفين الغائبين', $b, 'absent', $periodLabel);
}

/**
 * تقرير العمل الإضافي
 */
function buildOvertimeReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'تقرير العمل الإضافي', '<div class="empty"><div class="icon">🏠</div><div class="text">لا يوجد عمل إضافي مسجل</div></div>', 'overtime', $periodLabel);
    }
    
    $totalOT = array_sum(array_column($data, 'overtime_minutes'));
    $h = floor($totalOT / 60); $m = $totalOT % 60;
    $avg = round($totalOT / $count);
    
    $b = '<div class="summary-grid">
        <div class="stat-card"><div class="stat-value gold">' . $count . '</div><div class="stat-label">👷 عدد الموظفين</div></div>
        <div class="stat-card success"><div class="stat-value success">' . $h . '<small>س</small> ' . $m . '<small>د</small></div><div class="stat-label">⏱️ الإجمالي</div></div>
        <div class="stat-card"><div class="stat-value">' . $avg . '<small style="font-size:13px"> د</small></div><div class="stat-label">📊 المتوسط</div></div>
    </div>';
    
    $b .= '<div class="section-hdr"><span style="font-size:20px">⏱️</span><h3>تفاصيل العمل الإضافي</h3><span class="count">' . $count . '</span></div>';
    $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th><th>الفرع</th><th>المدة</th></tr></thead><tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $ot = (int)($r['overtime_minutes'] ?? 0);
        $b .= '<tr>' . rowNum($i++) . '<td><strong>' . e($r['name'] ?? '') . '</strong></td><td>' . e($r['branch_name'] ?? '-') . '</td><td>' . minutesBar($ot, 'green', 180) . '</td></tr>';
    }
    $b .= '</tbody></table></div>';
    
    return getBaseTemplate($title, 'تقرير العمل الإضافي', $b, 'overtime', $periodLabel);
}

/**
 * التقرير الشهري
 */
function buildMonthlyReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'التقرير الشهري', '<div class="empty"><div class="icon">📭</div><div class="text">لا توجد بيانات للفترة المحددة</div></div>', 'monthly', $periodLabel);
    }
    
    $totalLate = array_sum(array_column($data, 'late_count'));
    $perfect = count(array_filter($data, fn($r) => ($r['late_count'] ?? 0) == 0));
    
    $b = '<div class="summary-grid">
        <div class="stat-card"><div class="stat-value gold">' . $count . '</div><div class="stat-label">👥 الموظفون</div></div>
        <div class="stat-card success"><div class="stat-value success">' . $perfect . '</div><div class="stat-label">🌟 بدون تأخير</div></div>
        <div class="stat-card warning"><div class="stat-value warning">' . $totalLate . '</div><div class="stat-label">⏰ حالات تأخير</div></div>
    </div>';
    
    $b .= '<div class="section-hdr"><span style="font-size:20px">📊</span><h3>تفاصيل الحضور</h3><span class="count">' . $count . '</span></div>';
    $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th><th>أيام الحضور</th><th>مرات التأخير</th><th>دقائق التأخير</th><th>التقييم</th></tr></thead><tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $days = (int)($r['days_attended'] ?? 0);
        $lc = (int)($r['late_count'] ?? 0);
        $lm = (int)($r['total_late_minutes'] ?? 0);
        
        if ($lc == 0) $rt = '<span class="badge badge-green">⭐ ممتاز</span>';
        elseif ($lc <= 3) $rt = '<span class="badge badge-blue">👍 جيد</span>';
        elseif ($lc <= 7) $rt = '<span class="badge badge-yellow">⚠️ مقبول</span>';
        else $rt = '<span class="badge badge-red">🔴 متابعة</span>';
        
        $lcHtml = $lc > 0 ? '<span style="color:#D42B2B;font-weight:700">' . $lc . '</span>' : '<span style="color:#0D9668">0</span>';
        $lmHtml = $lm > 0 ? minutesBar($lm, 'red', 300) : '<span style="color:#0D9668">-</span>';
        
        $b .= '<tr>' . rowNum($i++) . '<td><strong>' . e($r['name'] ?? '') . '</strong></td><td style="text-align:center;font-weight:600">' . $days . '</td><td style="text-align:center">' . $lcHtml . '</td><td>' . $lmHtml . '</td><td>' . $rt . '</td></tr>';
    }
    $b .= '</tbody></table></div>';
    
    return getBaseTemplate($title, 'التقرير الشهري للحضور', $b, 'monthly', $periodLabel);
}

/**
 * ملخص الحضور
 */
function buildSummaryReport($data, string $title, string $periodLabel = ''): string {
    if (!is_array($data)) $data = [];
    $total = $data['total_employees'] ?? 0;
    $present = $data['present'] ?? 0;
    $absent = $data['absent'] ?? 0;
    $late = $data['late'] ?? 0;
    $onTime = max(0, $present - $late);
    $pPct = $total > 0 ? round(($present / $total) * 100) : 0;
    $aPct = $total > 0 ? round(($absent / $total) * 100) : 0;
    $lPct = $present > 0 ? round(($late / $present) * 100) : 0;
    
    $b = '<div class="summary-grid">
        <div class="stat-card"><div class="stat-value gold">' . $total . '</div><div class="stat-label">👥 إجمالي الموظفين</div></div>
        <div class="stat-card success"><div class="stat-value success">' . $present . '</div><div class="stat-label">✅ حاضرون (' . $pPct . '%)</div></div>
        <div class="stat-card danger"><div class="stat-value danger">' . $absent . '</div><div class="stat-label">🚫 غائبون (' . $aPct . '%)</div></div>
        <div class="stat-card warning"><div class="stat-value warning">' . $late . '</div><div class="stat-label">⏰ متأخرون (' . $lPct . '%)</div></div>
    </div>';
    
    // شريط النسب
    $gwp = $total > 0 ? round(($onTime / $total) * 100) : 0;
    $lwp = $total > 0 ? round(($late / $total) * 100) : 0;
    $b .= '<div class="progress-bar">
        <div style="width:' . $gwp . '%;background:linear-gradient(90deg,#0D9668,#34d399)"></div>
        <div style="width:' . $lwp . '%;background:linear-gradient(90deg,#C78B06,#fbbf24)"></div>
        <div style="width:' . $aPct . '%;background:linear-gradient(90deg,#D42B2B,#f87171)"></div>
    </div>
    <div style="display:flex;justify-content:center;gap:16px;font-size:11px;color:#7A8B9F;margin-bottom:16px">
        <span>🟢 في الموعد (' . $onTime . ')</span>
        <span>🟡 متأخرون (' . $late . ')</span>
        <span>🔴 غائبون (' . $absent . ')</span>
    </div>';
    
    if ($pPct >= 90) $b .= '<div class="info-box ok"><span>🌟</span><span><strong>أداء ممتاز!</strong> نسبة الحضور ' . $pPct . '%</span></div>';
    elseif ($pPct >= 70) $b .= '<div class="info-box info"><span>👍</span><span>أداء جيد. نسبة الحضور ' . $pPct . '% — يوجد مجال للتحسين.</span></div>';
    else $b .= '<div class="info-box warn"><span>⚠️</span><span><strong>يحتاج متابعة!</strong> نسبة الحضور ' . $pPct . '% فقط.</span></div>';
    
    return getBaseTemplate($title, 'ملخص الحضور', $b, 'summary', $periodLabel);
}

/**
 * تقرير الرواتب
 */
function buildPayrollReport(array $data, string $title, string $periodLabel = ''): string {
    $count = count($data);
    if (!$count) {
        return getBaseTemplate($title, 'تقرير الرواتب', '<div class="empty"><div class="icon">📭</div><div class="text">لا توجد بيانات رواتب</div></div>', 'payroll', $periodLabel);
    }
    
    $b = '<div class="section-hdr"><span style="font-size:20px">💰</span><h3>بيانات الرواتب</h3><span class="count">' . $count . '</span></div>';
    $b .= '<div class="tbl-wrap"><table><thead><tr><th>#</th><th>الموظف</th><th>أيام الحضور</th><th>أيام الغياب</th><th>تأخير (د)</th><th>إضافي (د)</th></tr></thead><tbody>';
    
    $i = 1;
    foreach ($data as $r) {
        $b .= '<tr>' . rowNum($i++) . '
            <td><strong>' . e($r['name'] ?? '') . '</strong></td>
            <td style="text-align:center;font-weight:600">' . ($r['days_attended'] ?? 0) . '</td>
            <td style="text-align:center;color:' . (($r['absent_days'] ?? 0) > 0 ? '#D42B2B' : '#0D9668') . ';font-weight:600">' . ($r['absent_days'] ?? 0) . '</td>
            <td style="text-align:center">' . ($r['total_late_minutes'] ?? 0) . '</td>
            <td style="text-align:center">' . ($r['total_overtime'] ?? 0) . '</td>
        </tr>';
    }
    $b .= '</tbody></table></div>';
    
    return getBaseTemplate($title, 'تقرير الرواتب الشهري', $b, 'payroll', $periodLabel);
}
