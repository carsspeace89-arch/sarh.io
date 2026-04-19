<?php
// =============================================================
// migrations/migrate_v12_push_subscriptions.php
// =============================================================
// Creates push_subscriptions table for Web Push notifications
// Run: php migrations/migrate_v12_push_subscriptions.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = db();

echo "Running migration v12: Push Subscriptions\n";
echo str_repeat('=', 50) . "\n";

$queries = [
    'create_push_subscriptions' => "
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            endpoint    TEXT NOT NULL,
            p256dh      VARCHAR(255) NOT NULL,
            auth_key    VARCHAR(255) NOT NULL,
            created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY idx_endpoint (endpoint(500)),
            KEY idx_employee (employee_id),
            CONSTRAINT push_subscriptions_ibfk_1 FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",

    'create_notifications' => "
        CREATE TABLE IF NOT EXISTS notifications (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NULL,
            admin_id    INT NULL,
            type        VARCHAR(50) NOT NULL,
            title       VARCHAR(255) NOT NULL,
            message     TEXT NULL,
            data_json   TEXT NULL,
            is_read     TINYINT(1) NOT NULL DEFAULT 0,
            read_at     DATETIME NULL,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_employee (employee_id),
            KEY idx_unread (is_read, created_at),
            KEY idx_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
];

foreach ($queries as $name => $sql) {
    try {
        $db->exec($sql);
        echo "  ✅ {$name}\n";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S01') {
            echo "  ⏩ {$name} — already exists\n";
        } else {
            echo "  ❌ {$name} — " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Migration v12 complete.\n";
