<?php
/**
 * ================================================================
 * Migration v9: Employee Inbox System
 * ================================================================
 * نظام صندوق الوارد للموظفين - مخالفات، خصومات، مكافآت، إشعارات
 *
 * SAFE TO RE-RUN: كل خطوة تتحقق قبل التنفيذ
 * ================================================================
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== Migration v9: Employee Inbox System ===\n\n";
$results = [];

// ── 1. جدول رسائل صندوق الوارد ──
try {
    $t = db()->query("SHOW TABLES LIKE 'employee_inbox'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE employee_inbox (
                id              INT AUTO_INCREMENT PRIMARY KEY,
                employee_id     INT NOT NULL,
                admin_id        INT NULL COMMENT 'المدير المرسِل',
                msg_type        ENUM('violation','deduction','reward','warning','info') NOT NULL DEFAULT 'info'
                                    COMMENT 'نوع الرسالة: مخالفة/خصم/مكافأة/تحذير/معلومة',
                title           VARCHAR(255) NOT NULL,
                body            TEXT NOT NULL,
                amount          DECIMAL(10,2) NULL COMMENT 'المبلغ (للخصم/المكافأة)',
                currency        VARCHAR(10) NULL DEFAULT 'ريال',
                reference_date  DATE NULL COMMENT 'تاريخ مرجعي (تاريخ المخالفة/المكافأة)',
                is_read         TINYINT(1) NOT NULL DEFAULT 0,
                read_at         DATETIME NULL,
                created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_emp      (employee_id),
                INDEX idx_type     (msg_type),
                INDEX idx_unread   (employee_id, is_read),
                INDEX idx_created  (created_at),
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created employee_inbox table";
    } else {
        $results[] = "⏭️ employee_inbox table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ employee_inbox: " . $e->getMessage();
}

// ── 2. إعداد: عدد الرسائل الظاهرة في صندوق الوارد ──
$inboxSettings = [
    ['inbox_enabled', '1',   'تفعيل نظام صندوق الوارد'],
    ['inbox_notify_employee', '1', 'إشعار الموظف عبر الواتساب عند استلام رسالة'],
];

foreach ($inboxSettings as [$key, $val, $desc]) {
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

// ── 3. عمود unread_inbox_count في جدول الموظفين (cache للأداء) ──
try {
    $col = db()->query("SHOW COLUMNS FROM employees LIKE 'unread_inbox_count'")->fetch();
    if (!$col) {
        db()->exec("ALTER TABLE employees ADD COLUMN unread_inbox_count INT NOT NULL DEFAULT 0 AFTER stars_balance");
        $results[] = "✅ Added unread_inbox_count column to employees";
    } else {
        $results[] = "⏭️ unread_inbox_count column already exists";
    }
} catch (Exception $e) {
    // قد لا يوجد stars_balance — جرب بعد salary
    try {
        $col2 = db()->query("SHOW COLUMNS FROM employees LIKE 'unread_inbox_count'")->fetch();
        if (!$col2) {
            db()->exec("ALTER TABLE employees ADD COLUMN unread_inbox_count INT NOT NULL DEFAULT 0");
            $results[] = "✅ Added unread_inbox_count column to employees (fallback)";
        }
    } catch (Exception $e2) {
        $results[] = "❌ unread_inbox_count: " . $e2->getMessage();
    }
}

// ── 4. طباعة النتائج ──
echo implode("\n", $results) . "\n\n";
echo "=== Migration v9 Complete ===\n";
echo "Total: " . count($results) . " steps processed\n";
