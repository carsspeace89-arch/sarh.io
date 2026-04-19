<?php
/**
 * ================================================================
 * Migration v7: Stars / Rewards System
 * ================================================================
 * نظام النجوم والمكافآت - يعتمد على التبكير والتأخير
 * مع إمكانية الإضافة والخصم اليدوي
 * ================================================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');
$results = [];

// ── 1. جدول سجل النجوم ──
try {
    $t = db()->query("SHOW TABLES LIKE 'employee_stars'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE employee_stars (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                stars INT NOT NULL DEFAULT 0 COMMENT 'positive=add, negative=deduct',
                reason VARCHAR(500) NOT NULL,
                source ENUM('auto_early','auto_late','manual','reset') NOT NULL DEFAULT 'manual',
                reference_date DATE NULL COMMENT 'related attendance date if auto',
                admin_id INT NULL COMMENT 'who performed the action',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_emp (employee_id),
                INDEX idx_source (source),
                INDEX idx_created (created_at),
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created employee_stars table";
    } else {
        $results[] = "⏭️ employee_stars table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ employee_stars: " . $e->getMessage();
}

// ── 2. إعدادات النجوم ──
$starSettings = [
    ['stars_per_early_day', '1', 'عدد النجوم لكل يوم تبكير'],
    ['stars_deduct_per_late_day', '1', 'عدد النجوم المخصومة لكل يوم تأخير'],
    ['stars_early_min_minutes', '5', 'الحد الأدنى لدقائق التبكير لاحتساب نجمة'],
    ['stars_late_min_minutes', '5', 'الحد الأدنى لدقائق التأخير لخصم نجمة'],
    ['stars_bonus_threshold', '50', 'عدد النجوم للحصول على مكافأة'],
    ['stars_auto_enabled', '1', 'تفعيل الحساب التلقائي للنجوم'],
];

foreach ($starSettings as [$key, $val, $desc]) {
    try {
        $exists = db()->query("SELECT 1 FROM settings WHERE setting_key = " . db()->quote($key))->fetch();
        if (!$exists) {
            db()->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)")
                ->execute([$key, $val, $desc]);
            $results[] = "✅ Added setting: {$key} = {$val}";
        } else {
            $results[] = "⏭️ Setting {$key} already exists";
        }
    } catch (Exception $e) {
        $results[] = "❌ Setting {$key}: " . $e->getMessage();
    }
}

// ── 3. عمود الرصيد في جدول الموظفين (cache) ──
try {
    $col = db()->query("SHOW COLUMNS FROM employees LIKE 'stars_balance'")->fetch();
    if (!$col) {
        db()->exec("ALTER TABLE employees ADD COLUMN stars_balance INT NOT NULL DEFAULT 0 AFTER salary");
        $results[] = "✅ Added stars_balance column to employees";
    } else {
        $results[] = "⏭️ stars_balance column already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ stars_balance: " . $e->getMessage();
}

// ── Output ──
echo "=== Migration v7: Stars / Rewards System ===\n\n";
foreach ($results as $r) echo $r . "\n";
echo "\nDone! " . count($results) . " operations.\n";
