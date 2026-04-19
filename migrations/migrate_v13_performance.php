<?php
// =============================================================
// migrations/migrate_v13_performance.php
// =============================================================
// Performance optimizations: composite indexes for report queries,
// login_attempts cleanup index, generated_reports table
// Run: php migrations/migrate_v13_performance.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = db();

$queries = [
    // ── Composite index for late report queries ──
    'idx_att_late' => "ALTER TABLE attendances ADD INDEX idx_att_late (type, late_minutes, attendance_date)",

    // ── Composite index for absence detection (NOT IN subquery) ──
    'idx_att_checkin_emp' => "ALTER TABLE attendances ADD INDEX idx_att_checkin_emp (attendance_date, type, employee_id)",

    // ── Login attempts auto-cleanup index ──
    'idx_login_ip_time' => "ALTER TABLE login_attempts ADD INDEX idx_login_ip_time (ip_address, attempted_at)",

    // ── Shift assignment lookup ──
    'idx_bs_branch_shift' => "ALTER TABLE branch_shifts ADD INDEX idx_bs_branch_shift (branch_id, shift_number, is_active)",

    // ── Generated reports table (for async report jobs) ──
    'tbl_gen_reports' => "CREATE TABLE IF NOT EXISTS generated_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        report_type VARCHAR(50) NOT NULL,
        filters JSON NULL,
        requested_by INT NOT NULL,
        file_path VARCHAR(512) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_gr_requested (requested_by),
        INDEX idx_gr_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // ── Audit log index for date range queries ──
    'idx_audit_created' => "ALTER TABLE audit_log ADD INDEX idx_audit_created (created_at)",
];

echo "Running migration v13: Performance Optimizations\n";
echo str_repeat('=', 50) . "\n";

foreach ($queries as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  ✅ {$name}\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate key name')
            || str_contains($e->getMessage(), 'Duplicate column name')
            || str_contains($e->getMessage(), 'already exists')) {
            echo "  ⏭️  {$name} (already exists)\n";
        } else {
            echo "  ❌ {$name}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Migration v13 complete.\n";
