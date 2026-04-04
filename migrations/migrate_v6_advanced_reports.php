<?php
/**
 * Migration v6 - Advanced Reports System:
 * 1. departments table
 * 2. holidays table
 * 3. leave_balances table
 * 4. saved_reports table
 * 5. employees: department_id, salary, hourly_rate columns
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');
$results = [];

// 1. departments table
try {
    $t = db()->query("SHOW TABLES LIKE 'departments'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE departments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created departments table";
    } else {
        $results[] = "⏭️ departments table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ departments: " . $e->getMessage();
}

// 2. holidays table
try {
    $t = db()->query("SHOW TABLES LIKE 'holidays'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE holidays (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(150) NOT NULL,
                holiday_date DATE NOT NULL,
                is_recurring TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_date (holiday_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created holidays table";
    } else {
        $results[] = "⏭️ holidays table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ holidays: " . $e->getMessage();
}

// 3. leave_balances table
try {
    $t = db()->query("SHOW TABLES LIKE 'leave_balances'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE leave_balances (
                id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                year INT NOT NULL,
                annual_quota INT DEFAULT 21,
                used_days INT DEFAULT 0,
                sick_used INT DEFAULT 0,
                unpaid_used INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_emp_year (employee_id, year),
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created leave_balances table";
    } else {
        $results[] = "⏭️ leave_balances table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ leave_balances: " . $e->getMessage();
}

// 4. saved_reports table
try {
    $t = db()->query("SHOW TABLES LIKE 'saved_reports'")->fetch();
    if (!$t) {
        db()->exec("
            CREATE TABLE saved_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                report_name VARCHAR(150) NOT NULL,
                report_type VARCHAR(50) NOT NULL,
                filters_json TEXT NOT NULL,
                columns_json TEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = "✅ Created saved_reports table";
    } else {
        $results[] = "⏭️ saved_reports table already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ saved_reports: " . $e->getMessage();
}

// 5. Add department_id to employees
try {
    $col = db()->query("SHOW COLUMNS FROM employees LIKE 'department_id'")->fetch();
    if (!$col) {
        db()->exec("ALTER TABLE employees ADD COLUMN department_id INT DEFAULT NULL AFTER branch_id");
        $results[] = "✅ Added department_id column to employees";
    } else {
        $results[] = "⏭️ department_id column already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ department_id: " . $e->getMessage();
}

// 6. Add salary to employees
try {
    $col = db()->query("SHOW COLUMNS FROM employees LIKE 'salary'")->fetch();
    if (!$col) {
        db()->exec("ALTER TABLE employees ADD COLUMN salary DECIMAL(10,2) DEFAULT 0 AFTER department_id");
        $results[] = "✅ Added salary column to employees";
    } else {
        $results[] = "⏭️ salary column already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ salary: " . $e->getMessage();
}

// 7. Add hourly_rate to employees
try {
    $col = db()->query("SHOW COLUMNS FROM employees LIKE 'hourly_rate'")->fetch();
    if (!$col) {
        db()->exec("ALTER TABLE employees ADD COLUMN hourly_rate DECIMAL(8,2) DEFAULT 0 AFTER salary");
        $results[] = "✅ Added hourly_rate column to employees";
    } else {
        $results[] = "⏭️ hourly_rate column already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ hourly_rate: " . $e->getMessage();
}

// 8. weekend_days setting (default: Friday)
try {
    $exists = db()->query("SELECT 1 FROM settings WHERE setting_key = 'weekend_days'")->fetch();
    if (!$exists) {
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute(['weekend_days', '5']);
        $results[] = "✅ Added weekend_days setting (default: 5=Friday)";
    } else {
        $results[] = "⏭️ weekend_days setting already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ weekend_days: " . $e->getMessage();
}

// 9. late_deduction_per_minute setting
try {
    $exists = db()->query("SELECT 1 FROM settings WHERE setting_key = 'late_deduction_per_minute'")->fetch();
    if (!$exists) {
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute(['late_deduction_per_minute', '0']);
        $results[] = "✅ Added late_deduction_per_minute setting (default: 0)";
    } else {
        $results[] = "⏭️ late_deduction_per_minute setting already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ late_deduction_per_minute: " . $e->getMessage();
}

// 10. absence_deduction_per_day setting
try {
    $exists = db()->query("SELECT 1 FROM settings WHERE setting_key = 'absence_deduction_per_day'")->fetch();
    if (!$exists) {
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute(['absence_deduction_per_day', '0']);
        $results[] = "✅ Added absence_deduction_per_day setting (default: 0)";
    } else {
        $results[] = "⏭️ absence_deduction_per_day setting already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ absence_deduction_per_day: " . $e->getMessage();
}

// 11. overtime_rate_multiplier setting
try {
    $exists = db()->query("SELECT 1 FROM settings WHERE setting_key = 'overtime_rate_multiplier'")->fetch();
    if (!$exists) {
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")->execute(['overtime_rate_multiplier', '1.5']);
        $results[] = "✅ Added overtime_rate_multiplier setting (default: 1.5)";
    } else {
        $results[] = "⏭️ overtime_rate_multiplier setting already exists";
    }
} catch (Exception $e) {
    $results[] = "❌ overtime_rate_multiplier: " . $e->getMessage();
}

echo "=== Migration v6: Advanced Reports ===\n";
foreach ($results as $r) echo $r . "\n";
echo "\nDone! " . count($results) . " operations.\n";
