<?php
// =============================================================
// migrations/migrate_v10_indexes_and_token_expiry.php
// =============================================================
// Adds performance indexes and token_expires_at column
// Run: php migrations/migrate_v10_indexes_and_token_expiry.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = db();

$queries = [
    // ── Performance indexes for attendances table ──
    'idx_att_emp_date' => "ALTER TABLE attendances ADD INDEX idx_att_emp_date (employee_id, attendance_date)",
    'idx_att_date_type' => "ALTER TABLE attendances ADD INDEX idx_att_date_type (attendance_date, type)",
    'idx_att_timestamp' => "ALTER TABLE attendances ADD INDEX idx_att_timestamp (timestamp)",
    'idx_att_status' => "ALTER TABLE attendances ADD INDEX idx_att_status (status)",

    // ── Employee lookup indexes ──
    'idx_emp_pin' => "ALTER TABLE employees ADD INDEX idx_emp_pin (pin)",
    'idx_emp_token' => "ALTER TABLE employees ADD INDEX idx_emp_token (unique_token)",
    'idx_emp_branch' => "ALTER TABLE employees ADD INDEX idx_emp_branch (branch_id)",
    'idx_emp_active' => "ALTER TABLE employees ADD INDEX idx_emp_active (is_active, deleted_at)",
    'idx_emp_fingerprint' => "ALTER TABLE employees ADD INDEX idx_emp_fingerprint (device_fingerprint)",

    // ── Branch shifts index ──
    'idx_bs_branch_active' => "ALTER TABLE branch_shifts ADD INDEX idx_bs_branch_active (branch_id, is_active)",

    // ── Jobs table indexes (if exists) ──
    'idx_jobs_status_avail' => "ALTER TABLE jobs ADD INDEX idx_jobs_status_avail (status, available_at)",

    // ── Token expiration column ──
    'col_token_expires' => "ALTER TABLE employees ADD COLUMN token_expires_at DATETIME NULL DEFAULT NULL AFTER unique_token",
];

echo "Running migration v10: Indexes + Token Expiry\n";
echo str_repeat('=', 50) . "\n";

foreach ($queries as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  ✅ {$name}\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate key name') || str_contains($e->getMessage(), 'Duplicate column name')) {
            echo "  ⏭️  {$name} (already exists)\n";
        } else {
            echo "  ❌ {$name}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Migration v10 complete.\n";
