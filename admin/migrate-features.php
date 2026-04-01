<?php
// =============================================================
// admin/migrate-features.php - ترقية قاعدة البيانات للمزايا الجديدة
// =============================================================
// يُنفَّذ مرة واحدة فقط عبر المتصفح أو CLI

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// السماح من CLI أو المشرف فقط
if (php_sapi_name() !== 'cli') {
    requireAdminLogin();
}

$pdo = db();
$results = [];

function runMigration(PDO $pdo, string $label, string $sql): array {
    try {
        $pdo->exec($sql);
        return ['label' => $label, 'status' => 'OK'];
    } catch (PDOException $e) {
        // تجاهل Duplicate column / Table exists
        if (strpos($e->getMessage(), '1060') !== false || strpos($e->getMessage(), '1050') !== false) {
            return ['label' => $label, 'status' => 'SKIP (exists)'];
        }
        return ['label' => $label, 'status' => 'ERROR: ' . $e->getMessage()];
    }
}

// =================== 1. جدول الإشعارات ===================
$results[] = runMigration($pdo, 'notifications table', "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info','warning','success','danger') DEFAULT 'info',
        category VARCHAR(50) DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        link VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_admin_read (admin_id, is_read),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// =================== 2. جدول نقل الموظفين ===================
$results[] = runMigration($pdo, 'employee_transfers table', "
    CREATE TABLE IF NOT EXISTS employee_transfers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        from_branch_id INT NULL,
        to_branch_id INT NOT NULL,
        transfer_date DATE NOT NULL,
        reason TEXT NULL,
        transferred_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_employee (employee_id),
        INDEX idx_date (transfer_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// =================== 3. جدول أرصدة الإجازات ===================
$results[] = runMigration($pdo, 'leave_balances table', "
    CREATE TABLE IF NOT EXISTS leave_balances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        year INT NOT NULL,
        annual_total INT DEFAULT 30,
        annual_used INT DEFAULT 0,
        sick_total INT DEFAULT 15,
        sick_used INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_emp_year (employee_id, year),
        INDEX idx_employee (employee_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// =================== 4. إضافة عمود الملاحظات للإجازات ===================
$results[] = runMigration($pdo, 'leaves.admin_notes column', "
    ALTER TABLE leaves ADD COLUMN admin_notes TEXT NULL AFTER reason
");

// =================== 5. إضافة أعمدة الصلاحيات لجدول المشرفين ===================
$results[] = runMigration($pdo, 'admins.role column', "
    ALTER TABLE admins ADD COLUMN role ENUM('super_admin','branch_manager','viewer') DEFAULT 'super_admin' AFTER full_name
");

$results[] = runMigration($pdo, 'admins.branch_id column', "
    ALTER TABLE admins ADD COLUMN branch_id INT NULL AFTER role
");

$results[] = runMigration($pdo, 'admins.email column', "
    ALTER TABLE admins ADD COLUMN email VARCHAR(255) NULL AFTER full_name
");

// =================== 6. جدول النسخ الاحتياطية ===================
$results[] = runMigration($pdo, 'backups table', "
    CREATE TABLE IF NOT EXISTS backups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        file_size BIGINT DEFAULT 0,
        backup_type ENUM('manual','auto') DEFAULT 'manual',
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// =================== 7. إنشاء أرصدة إجازات للسنة الحالية ===================
$year = (int)date('Y');
try {
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO leave_balances (employee_id, year, annual_total, annual_used, sick_total, sick_used)
        SELECT id, ?, 30, 0, 15, 0 FROM employees WHERE is_active = 1 AND deleted_at IS NULL
    ");
    $stmt->execute([$year]);
    $inserted = $stmt->rowCount();
    $results[] = ['label' => "leave_balances init ({$year})", 'status' => "OK ({$inserted} rows)"];
} catch (PDOException $e) {
    $results[] = ['label' => "leave_balances init ({$year})", 'status' => 'ERROR: ' . $e->getMessage()];
}

// =================== 8. تحديث أرصدة الإجازات المستخدمة ===================
try {
    $pdo->exec("
        UPDATE leave_balances lb SET
            annual_used = (SELECT COALESCE(SUM(DATEDIFF(end_date, start_date)+1), 0) FROM leaves WHERE employee_id = lb.employee_id AND leave_type = 'annual' AND status = 'approved' AND YEAR(start_date) = lb.year),
            sick_used   = (SELECT COALESCE(SUM(DATEDIFF(end_date, start_date)+1), 0) FROM leaves WHERE employee_id = lb.employee_id AND leave_type = 'sick'   AND status = 'approved' AND YEAR(start_date) = lb.year)
        WHERE year = {$year}
    ");
    $results[] = ['label' => 'leave_balances sync', 'status' => 'OK'];
} catch (PDOException $e) {
    $results[] = ['label' => 'leave_balances sync', 'status' => 'ERROR: ' . $e->getMessage()];
}

// =================== عرض النتائج ===================
if (php_sapi_name() === 'cli') {
    foreach ($results as $r) {
        echo "[{$r['status']}] {$r['label']}\n";
    }
    echo "\nDone.\n";
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html dir="rtl"><head><meta charset="utf-8"><title>Migration</title>';
    echo '<style>body{font-family:Tajawal,sans-serif;max-width:700px;margin:40px auto;padding:20px;background:#0F172A;color:#E2E8F0}';
    echo '.ok{color:#10B981}.skip{color:#F59E0B}.err{color:#EF4444}table{width:100%;border-collapse:collapse;margin-top:20px}td,th{padding:10px 14px;border-bottom:1px solid #334155;text-align:right}th{background:#1E293B}</style></head><body>';
    echo '<h2>🔧 ترقية قاعدة البيانات</h2>';
    echo '<table><tr><th>العملية</th><th>النتيجة</th></tr>';
    foreach ($results as $r) {
        $cls = strpos($r['status'], 'OK') === 0 ? 'ok' : (strpos($r['status'], 'SKIP') === 0 ? 'skip' : 'err');
        echo "<tr><td>{$r['label']}</td><td class='{$cls}'>{$r['status']}</td></tr>";
    }
    echo '</table><p style="margin-top:20px;color:#94A3B8">يمكنك حذف هذا الملف الآن.</p>';
    echo '<a href="dashboard.php" style="color:#F97316;text-decoration:none">← العودة للوحة التحكم</a>';
    echo '</body></html>';
}
