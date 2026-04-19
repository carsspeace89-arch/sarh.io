<?php
/**
 * سكريبت ترحيل البيانات من قاعدة البيانات القديمة u307296675_whats
 * إلى قاعدة البيانات الجديدة u307296675_xml5
 * 
 * احذف هذا الملف فور الانتهاء من الترحيل!
 */

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

// --- إعدادات الاتصال ---
$src_host = 'localhost';
$src_db   = 'u307296675_whats';
$src_user = 'u307296675_whats';
$src_pass = 'Goolbx512@@@'; // 3 علامات @

$dst_host = 'localhost';
$dst_db   = 'u307296675_xml5';
$dst_user = 'u307296675_xml5';
$dst_pass = 'Goolbx512@@@@'; // 4 علامات @

$dry_run = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
$confirm = isset($_GET['confirm']) && $_GET['confirm'] == '1';

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<title>ترحيل البيانات</title>
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; direction: rtl; padding: 20px; background: #f5f5f5; }
.card { background: #fff; border-radius: 8px; padding: 20px; margin: 15px 0; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
h1 { color: #333; }
h2 { color: #555; border-bottom: 2px solid #eee; padding-bottom: 8px; }
.success { color: #28a745; font-weight: bold; }
.error   { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
.info    { color: #17a2b8; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: right; }
th { background: #f8f9fa; }
.btn { display: inline-block; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 1rem; margin: 5px; cursor: pointer; border: none; }
.btn-primary { background: #007bff; color: #fff; }
.btn-danger  { background: #dc3545; color: #fff; }
.btn-warning { background: #ffc107; color: #333; }
.log { font-family: monospace; font-size: 0.85rem; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 6px; max-height: 500px; overflow-y: auto; }
.log .ok   { color: #4ec9b0; }
.log .err  { color: #f44747; }
.log .skip { color: #dcdcaa; }
.log .head { color: #569cd6; font-weight: bold; }
</style>
</head>
<body>
<h1>🔄 ترحيل البيانات من قاعدة البيانات القديمة</h1>

<?php
// --- اتصال بقاعدة البيانات المصدر ---
$src = new mysqli($src_host, $src_user, $src_pass, $src_db);
if ($src->connect_error) {
    echo '<div class="card"><p class="error">❌ فشل الاتصال بقاعدة البيانات القديمة: ' . htmlspecialchars($src->connect_error) . '</p></div>';
    echo '</body></html>'; exit;
}
$src->set_charset('utf8mb4');

// --- اتصال بقاعدة البيانات الهدف ---
$dst = new mysqli($dst_host, $dst_user, $dst_pass, $dst_db);
if ($dst->connect_error) {
    echo '<div class="card"><p class="error">❌ فشل الاتصال بقاعدة البيانات الجديدة: ' . htmlspecialchars($dst->connect_error) . '</p></div>';
    $src->close();
    echo '</body></html>'; exit;
}
$dst->set_charset('utf8mb4');

echo '<div class="card"><p class="success">✅ تم الاتصال بكلتا قاعدتَي البيانات بنجاح</p></div>';

// --- حساب سريع للأرقام الحالية ---
$counts = [];
foreach (['branches','employees','attendances','admins','leaves','settings'] as $t) {
    $r_src = $src->query("SELECT COUNT(*) c FROM `$t`");
    $r_dst = $dst->query("SELECT COUNT(*) c FROM `$t`");
    $counts[$t] = [
        'src' => $r_src ? (int)$r_src->fetch_assoc()['c'] : '?',
        'dst' => $r_dst ? (int)$r_dst->fetch_assoc()['c'] : '?',
    ];
}

echo '<div class="card"><h2>📊 إحصاءات قبل الترحيل</h2>
<table>
<tr><th>الجدول</th><th>القديمة (المصدر)</th><th>الجديدة (الهدف)</th></tr>';
foreach ($counts as $t => $c) {
    echo "<tr><td>$t</td><td>{$c['src']}</td><td>{$c['dst']}</td></tr>";
}
echo '</table>';

if (!$confirm) {
    echo '<p class="warning">⚠️ ملاحظة: سيتم دمج البيانات (INSERT IGNORE) — السجلات الموجودة لن تُحذف.</p>';
    echo '<a href="?confirm=1" class="btn btn-danger">🚀 تأكيد الترحيل الكامل</a> ';
    echo '<a href="?dry_run=1&confirm=1" class="btn btn-warning">🔍 معاينة فقط (Dry Run)</a>';
    echo '</div></body></html>';
    $src->close(); $dst->close(); exit;
}

echo '</div>';

if ($dry_run) {
    echo '<div class="card"><p class="warning">⚠️ وضع المعاينة — لن يتم تعديل أي بيانات</p></div>';
}

$log = [];
$total_inserted = 0;
$total_skipped  = 0;
$total_errors   = 0;

function log_msg(string $type, string $msg): void {
    global $log;
    $log[] = ['type' => $type, 'msg' => $msg];
}

function migrate_table(
    mysqli $src,
    mysqli $dst,
    string $table,
    array  $select_cols,   // الأعمدة من بيانات المصدر
    array  $insert_cols,   // الأعمدة في الهدف
    bool   $dry_run,
    array  $col_map = [],  // تحويلات القيم ['col' => fn($v) => ...]
    string $where = ''
): array {
    global $total_inserted, $total_skipped, $total_errors;

    $cols_sql = implode(',', array_map(fn($c) => "`$c`", $select_cols));
    $where_sql = $where ? "WHERE $where" : '';
    $res = $src->query("SELECT $cols_sql FROM `$table` $where_sql");

    if (!$res) {
        log_msg('err', "خطأ في قراءة $table: " . $src->error);
        $total_errors++;
        return ['inserted' => 0, 'skipped' => 0, 'errors' => 1];
    }

    $inserted = $skipped = $errors = 0;
    $ins_cols_sql = implode(',', array_map(fn($c) => "`$c`", $insert_cols));

    while ($row = $res->fetch_assoc()) {
        $values = [];
        foreach ($select_cols as $i => $col) {
            $val = $row[$col] ?? null;
            $insert_col = $insert_cols[$i];
            if (isset($col_map[$insert_col])) {
                $val = ($col_map[$insert_col])($val);
            }
            if ($val === null) {
                $values[] = 'NULL';
            } else {
                $values[] = "'" . $dst->real_escape_string($val) . "'";
            }
        }
        $vals_sql = implode(',', $values);
        $sql = "INSERT IGNORE INTO `$table` ($ins_cols_sql) VALUES ($vals_sql)";

        if (!$dry_run) {
            if ($dst->query($sql)) {
                if ($dst->affected_rows > 0) { $inserted++; $total_inserted++; }
                else { $skipped++; $total_skipped++; }
            } else {
                log_msg('err', "خطأ في إدراج سجل في $table: " . $dst->error . " | SQL: " . substr($sql, 0, 200));
                $errors++; $total_errors++;
            }
        } else {
            $inserted++; $total_inserted++;
        }
    }

    return ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors];
}

// =====================================================================
// 1. الفروع (branches)
// =====================================================================
log_msg('head', '═══ 1. جدول الفروع ═══');

// الأعمدة المتاحة في المصدر القديم
$src_branch_cols = ['id','name','location','manager_name','phone','created_at'];
// الأعمدة في الهدف الجديد (الأعمدة الزمنية ستأخذ قيم افتراضية عبر DEFAULT في MySQL)
// نضيف الأعمدة الإضافية يدوياً
$dst_branch_cols = ['id','name','location','manager_name','phone','created_at'];

// أولاً نحقق وجود الجدول والأعمدة في المصدر
$chk = $src->query("SHOW COLUMNS FROM `branches`");
$src_cols_exist = [];
while ($c = $chk->fetch_assoc()) $src_cols_exist[] = $c['Field'];

$avail = array_intersect($src_branch_cols, $src_cols_exist);
$avail = array_values($avail); // re-index

$res_b = migrate_table($src, $dst, 'branches', $avail, $avail, $dry_run);
log_msg('ok', "الفروع — مُضاف: {$res_b['inserted']}, مُتجاهل: {$res_b['skipped']}, خطأ: {$res_b['errors']}");

// =====================================================================
// 2. الموظفون (employees)
// =====================================================================
log_msg('head', '═══ 2. جدول الموظفين ═══');

$chk = $src->query("SHOW COLUMNS FROM `employees`");
$src_emp_cols_all = [];
while ($c = $chk->fetch_assoc()) $src_emp_cols_all[] = $c['Field'];

// الأعمدة المشتركة المطلوبة (بدون profile_photo)
$wanted_emp = ['id','name','employee_number','branch_id','email','phone','national_id',
               'position','hire_date','is_active','password','device_id','created_at','updated_at'];
$avail_emp = array_values(array_intersect($wanted_emp, $src_emp_cols_all));

$chk2 = $dst->query("SHOW COLUMNS FROM `employees`");
$dst_emp_cols_all = [];
while ($c = $chk2->fetch_assoc()) $dst_emp_cols_all[] = $c['Field'];

$avail_emp = array_values(array_intersect($avail_emp, $dst_emp_cols_all));

$res_e = migrate_table($src, $dst, 'employees', $avail_emp, $avail_emp, $dry_run);
log_msg('ok', "الموظفون — مُضاف: {$res_e['inserted']}, مُتجاهل: {$res_e['skipped']}, خطأ: {$res_e['errors']}");

// =====================================================================
// 3. الحضور (attendances)
// =====================================================================
log_msg('head', '═══ 3. جدول الحضور ═══');

$chk = $src->query("SHOW COLUMNS FROM `attendances`");
$src_att_cols_all = [];
while ($c = $chk->fetch_assoc()) $src_att_cols_all[] = $c['Field'];

$wanted_att = ['id','employee_id','date','time','type','branch_id',
               'latitude','longitude','photo','notes','created_at'];
$avail_att = array_values(array_intersect($wanted_att, $src_att_cols_all));

// تحويل overtime → overtime-start
$col_map_att = [
    'type' => fn($v) => ($v === 'overtime') ? 'overtime-start' : $v
];

$res_a = migrate_table($src, $dst, 'attendances', $avail_att, $avail_att, $dry_run, $col_map_att);
log_msg('ok', "الحضور — مُضاف: {$res_a['inserted']}, مُتجاهل: {$res_a['skipped']}, خطأ: {$res_a['errors']}");

// =====================================================================
// 4. المسؤولون (admins)
// =====================================================================
if ($dst->query("SELECT 1 FROM `admins` LIMIT 1") !== false) {
    log_msg('head', '═══ 4. جدول المسؤولين ═══');

    $chk = $src->query("SHOW COLUMNS FROM `admins`");
    if ($chk) {
        $src_adm_all = [];
        while ($c = $chk->fetch_assoc()) $src_adm_all[] = $c['Field'];
        $chk2 = $dst->query("SHOW COLUMNS FROM `admins`");
        $dst_adm_all = [];
        while ($c = $chk2->fetch_assoc()) $dst_adm_all[] = $c['Field'];
        $avail_adm = array_values(array_intersect($src_adm_all, $dst_adm_all));
        if (!empty($avail_adm)) {
            $res_adm = migrate_table($src, $dst, 'admins', $avail_adm, $avail_adm, $dry_run);
            log_msg('ok', "المسؤولون — مُضاف: {$res_adm['inserted']}, مُتجاهل: {$res_adm['skipped']}, خطأ: {$res_adm['errors']}");
        }
    } else {
        log_msg('skip', 'لا يوجد جدول admins في المصدر');
    }
}

// =====================================================================
// 5. الإجازات (leaves)  
// =====================================================================
if ($src->query("SELECT 1 FROM `leaves` LIMIT 1") !== false &&
    $dst->query("SELECT 1 FROM `leaves` LIMIT 1") !== false) {
    log_msg('head', '═══ 5. جدول الإجازات ═══');

    $chk  = $src->query("SHOW COLUMNS FROM `leaves`");
    $src_lv = [];
    while ($c = $chk->fetch_assoc()) $src_lv[] = $c['Field'];
    $chk2 = $dst->query("SHOW COLUMNS FROM `leaves`");
    $dst_lv = [];
    while ($c = $chk2->fetch_assoc()) $dst_lv[] = $c['Field'];
    $avail_lv = array_values(array_intersect($src_lv, $dst_lv));
    if (!empty($avail_lv)) {
        $res_lv = migrate_table($src, $dst, 'leaves', $avail_lv, $avail_lv, $dry_run);
        log_msg('ok', "الإجازات — مُضاف: {$res_lv['inserted']}, مُتجاهل: {$res_lv['skipped']}, خطأ: {$res_lv['errors']}");
    }
} else {
    log_msg('skip', 'جدول leaves غير موجود في أحد قاعدتَي البيانات — تم التخطي');
}

// =====================================================================
// 6. الإعدادات (settings)
// =====================================================================
if ($src->query("SELECT 1 FROM `settings` LIMIT 1") !== false &&
    $dst->query("SELECT 1 FROM `settings` LIMIT 1") !== false) {
    log_msg('head', '═══ 6. جدول الإعدادات ═══');

    $chk  = $src->query("SHOW COLUMNS FROM `settings`");
    $src_st = [];
    while ($c = $chk->fetch_assoc()) $src_st[] = $c['Field'];
    $chk2 = $dst->query("SHOW COLUMNS FROM `settings`");
    $dst_st = [];
    while ($c = $chk2->fetch_assoc()) $dst_st[] = $c['Field'];
    $avail_st = array_values(array_intersect($src_st, $dst_st));
    if (!empty($avail_st)) {
        $res_st = migrate_table($src, $dst, 'settings', $avail_st, $avail_st, $dry_run);
        log_msg('ok', "الإعدادات — مُضاف: {$res_st['inserted']}, مُتجاهل: {$res_st['skipped']}, خطأ: {$res_st['errors']}");
    }
} else {
    log_msg('skip', 'جدول settings غير موجود في أحد قاعدتَي البيانات — تم التخطي');
}

// =====================================================================
// 7. الأجهزة المعروفة (known_devices)
// =====================================================================
if ($src->query("SELECT 1 FROM `known_devices` LIMIT 1") !== false &&
    $dst->query("SELECT 1 FROM `known_devices` LIMIT 1") !== false) {
    log_msg('head', '═══ 7. جدول الأجهزة ═══');

    $chk  = $src->query("SHOW COLUMNS FROM `known_devices`");
    $src_kd = [];
    while ($c = $chk->fetch_assoc()) $src_kd[] = $c['Field'];
    $chk2 = $dst->query("SHOW COLUMNS FROM `known_devices`");
    $dst_kd = [];
    while ($c = $chk2->fetch_assoc()) $dst_kd[] = $c['Field'];
    $avail_kd = array_values(array_intersect($src_kd, $dst_kd));
    if (!empty($avail_kd)) {
        $res_kd = migrate_table($src, $dst, 'known_devices', $avail_kd, $avail_kd, $dry_run);
        log_msg('ok', "الأجهزة — مُضاف: {$res_kd['inserted']}, مُتجاهل: {$res_kd['skipped']}, خطأ: {$res_kd['errors']}");
    }
} else {
    log_msg('skip', 'جدول known_devices غير موجود في أحد قاعدتَي البيانات — تم التخطي');
}

$src->close();
$dst->close();

// --- عرض السجل ---
echo '<div class="card"><h2>📋 سجل العمليات</h2><div class="log">';
foreach ($log as $entry) {
    $cls = $entry['type'];
    echo '<div class="' . htmlspecialchars($cls) . '">' . htmlspecialchars($entry['msg']) . '</div>';
}
echo '</div></div>';

// --- ملخص ---
echo '<div class="card"><h2>📊 ملخص النتائج</h2>';
echo '<table>';
echo '<tr><th>البيانات المُضافة</th><th>السجلات المُتجاهلة (موجودة)</th><th>الأخطاء</th></tr>';
echo "<tr><td class='success'>$total_inserted</td><td class='info'>$total_skipped</td><td class='error'>$total_errors</td></tr>";
echo '</table>';

if ($dry_run) {
    echo '<p class="warning">⚠️ هذه معاينة فقط — لم يتم إدخال أي بيانات فعلية</p>';
    echo '<a href="?confirm=1" class="btn btn-danger">🚀 تنفيذ الترحيل الفعلي</a>';
} else if ($total_errors === 0) {
    echo '<p class="success">✅ اكتمل الترحيل بنجاح!</p>';
    echo '<p class="error"><strong>⚠️ مهم جداً: احذف هذا الملف فوراً لأسباب أمنية!</strong></p>';
    echo '<form method="POST" action="?delete_self=1"><button type="submit" class="btn btn-danger">🗑️ حذف هذا الملف الآن</button></form>';
} else {
    echo '<p class="error">⚠️ اكتمل الترحيل مع ' . $total_errors . ' خطأ — راجع السجل أعلاه</p>';
}

echo '</div>';

// حذف الملف بعد الترحيل
if (isset($_POST) && isset($_GET['delete_self'])) {
    if (@unlink(__FILE__)) {
        echo '<div class="card"><p class="success">✅ تم حذف ملف الترحيل بنجاح</p></div>';
    } else {
        echo '<div class="card"><p class="error">❌ فشل حذف الملف تلقائياً — يرجى حذفه يدوياً</p></div>';
    }
}
?>
</body>
</html>
