<?php
// =============================================================
// migrations/migrate_v14_approval_workflow.php
// =============================================================
// Creates approval_requests table for overtime, leaves, transfers
// Run: php migrations/migrate_v14_approval_workflow.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = db();

$queries = [
    'tbl_approvals' => "CREATE TABLE IF NOT EXISTS approval_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('overtime', 'leave', 'transfer', 'attendance_correction', 'document') NOT NULL,
        reference_id INT NULL COMMENT 'ID of the related record (leave_id, overtime_id, etc)',
        employee_id INT NOT NULL,
        requested_by INT NOT NULL COMMENT 'admin_id who created the request',
        approved_by INT NULL COMMENT 'admin_id who approved/rejected',
        status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
        priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        metadata JSON NULL COMMENT 'type-specific data',
        notes TEXT NULL COMMENT 'approver notes',
        requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        decided_at DATETIME NULL,
        expires_at DATETIME NULL COMMENT 'auto-reject after this time',
        INDEX idx_apr_status (status),
        INDEX idx_apr_employee (employee_id),
        INDEX idx_apr_type_status (type, status),
        INDEX idx_apr_requested_by (requested_by),
        INDEX idx_apr_expires (expires_at, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'tbl_approval_history' => "CREATE TABLE IF NOT EXISTS approval_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        approval_id INT NOT NULL,
        action ENUM('created', 'approved', 'rejected', 'cancelled', 'escalated', 'commented') NOT NULL,
        performed_by INT NOT NULL,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ah_approval (approval_id),
        FOREIGN KEY (approval_id) REFERENCES approval_requests(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

echo "Running migration v14: Approval Workflow\n";
echo str_repeat('=', 50) . "\n";

foreach ($queries as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  ✅ {$name}\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'already exists')) {
            echo "  ⏭️  {$name} (already exists)\n";
        } else {
            echo "  ❌ {$name}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Migration v14 complete.\n";
