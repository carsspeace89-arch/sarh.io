<?php
// =============================================================
// migrations/001_refactor_v5.php - Database Migration for Architecture Refactor
// =============================================================
// Run once: php migrations/001_refactor_v5.php
// Or via cron: php migrations/001_refactor_v5.php --secret=YOUR_CRON_SECRET
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Access control
if (php_sapi_name() !== 'cli') {
    $cronSecret = $_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '';
    if (empty($cronSecret) || !isset($_GET['secret']) || !hash_equals($cronSecret, $_GET['secret'])) {
        http_response_code(403);
        exit('Access denied');
    }
}

$pdo = db();
$results = [];

function runMigration(PDO $pdo, string $name, string $sql, array &$results): void
{
    try {
        $pdo->exec($sql);
        $results[] = "[OK] {$name}";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate')) {
            $results[] = "[SKIP] {$name} (already exists)";
        } else {
            $results[] = "[FAIL] {$name}: " . $e->getMessage();
        }
    }
}

// ── 1. Jobs queue table ──
runMigration($pdo, 'Create jobs table', "
    CREATE TABLE IF NOT EXISTS jobs (
        id VARCHAR(64) NOT NULL PRIMARY KEY,
        queue VARCHAR(64) NOT NULL DEFAULT 'default',
        class VARCHAR(255) NOT NULL,
        payload TEXT NOT NULL,
        attempts INT UNSIGNED NOT NULL DEFAULT 0,
        max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
        status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
        available_at INT UNSIGNED NOT NULL,
        created_at INT UNSIGNED NOT NULL,
        started_at INT UNSIGNED NULL,
        completed_at INT UNSIGNED NULL,
        failed_at INT UNSIGNED NULL,
        last_error TEXT NULL,
        INDEX idx_jobs_status_available (status, available_at),
        INDEX idx_jobs_queue (queue)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $results);

// ── 2. Generated reports table ──
runMigration($pdo, 'Create generated_reports table', "
    CREATE TABLE IF NOT EXISTS generated_reports (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(64) NOT NULL,
        parameters JSON NULL,
        file_path VARCHAR(500) NULL,
        status ENUM('pending','generating','completed','failed') NOT NULL DEFAULT 'pending',
        generated_by INT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME NULL,
        error_message TEXT NULL,
        INDEX idx_reports_status (status),
        INDEX idx_reports_generated_by (generated_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $results);

// ── 3. Tampering log table (for risk scoring) ──
runMigration($pdo, 'Create tampering_log table', "
    CREATE TABLE IF NOT EXISTS tampering_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_id INT UNSIGNED NOT NULL,
        action VARCHAR(20) NOT NULL DEFAULT 'checkin',
        risk_score DECIMAL(5,2) NOT NULL DEFAULT 0,
        risk_factors JSON NULL,
        latitude DECIMAL(10,8) NULL,
        longitude DECIMAL(11,8) NULL,
        accuracy DECIMAL(8,2) NULL,
        ip_address VARCHAR(45) NULL,
        device_fingerprint VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tampering_employee (employee_id),
        INDEX idx_tampering_score (risk_score),
        INDEX idx_tampering_date (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $results);

// ── 4. Add tenant_id to major tables (multi-tenant prep) ──
$tenantTables = ['employees', 'branches', 'attendances', 'settings', 'leave_requests'];
foreach ($tenantTables as $table) {
    // Check if column exists first
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'tenant_id'
    ");
    $stmt->execute([DB_NAME, $table]);
    if ((int)$stmt->fetchColumn() === 0) {
        runMigration($pdo, "Add tenant_id to {$table}", "
            ALTER TABLE `{$table}` ADD COLUMN tenant_id INT UNSIGNED NULL DEFAULT NULL AFTER id
        ", $results);
        runMigration($pdo, "Add tenant_id index on {$table}", "
            ALTER TABLE `{$table}` ADD INDEX idx_{$table}_tenant (tenant_id)
        ", $results);
    } else {
        $results[] = "[SKIP] tenant_id already exists on {$table}";
    }
}

// ── 5. Performance indexes on attendances ──
$indexes = [
    ['attendances', 'idx_att_emp_date', '(employee_id, attendance_date)'],
    ['attendances', 'idx_att_date_type', '(attendance_date, type)'],
    ['attendances', 'idx_att_branch_date', '(branch_id, attendance_date)'],
    ['employees', 'idx_emp_branch', '(branch_id)'],
    ['employees', 'idx_emp_token', '(unique_token)'],
    ['employees', 'idx_emp_pin', '(pin)'],
    ['employees', 'idx_emp_fingerprint', '(device_fingerprint)'],
];

foreach ($indexes as [$table, $indexName, $columns]) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?
    ");
    $stmt->execute([DB_NAME, $table, $indexName]);
    if ((int)$stmt->fetchColumn() === 0) {
        runMigration($pdo, "Add index {$indexName} on {$table}", "
            ALTER TABLE `{$table}` ADD INDEX {$indexName} {$columns}
        ", $results);
    } else {
        $results[] = "[SKIP] Index {$indexName} already exists on {$table}";
    }
}

// ── 6. Add risk_score column to attendances ──
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'risk_score'
");
$stmt->execute([DB_NAME]);
if ((int)$stmt->fetchColumn() === 0) {
    runMigration($pdo, 'Add risk_score to attendances', "
        ALTER TABLE attendances ADD COLUMN risk_score DECIMAL(5,2) NULL DEFAULT NULL
    ", $results);
}

// ── 7. Add ip_address column to attendances if missing ──
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'attendances' AND COLUMN_NAME = 'ip_address'
");
$stmt->execute([DB_NAME]);
if ((int)$stmt->fetchColumn() === 0) {
    runMigration($pdo, 'Add ip_address to attendances', "
        ALTER TABLE attendances ADD COLUMN ip_address VARCHAR(45) NULL DEFAULT NULL
    ", $results);
}

// ── 8. Migrations tracking table ──
runMigration($pdo, 'Create migrations table', "
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_migration (migration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $results);

// Record this migration
try {
    $pdo->prepare("INSERT IGNORE INTO migrations (migration) VALUES (?)")
        ->execute(['001_refactor_v5']);
} catch (PDOException $e) {
    // ignore
}

// ── Output results ──
$output = "=== Migration 001_refactor_v5 ===\n" . implode("\n", $results) . "\n";

if (php_sapi_name() === 'cli') {
    echo $output;
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo $output;
}
