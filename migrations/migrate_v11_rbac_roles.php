<?php
// =============================================================
// migrations/migrate_v11_rbac_roles.php
// =============================================================
// Adds RBAC role support to admins table
// Run: php migrations/migrate_v11_rbac_roles.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = db();

echo "Running migration v11: RBAC roles\n";
echo str_repeat('=', 50) . "\n";

try {
    $db->exec("ALTER TABLE admins ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'super_admin' AFTER username");
    echo "  ✅ Added role column to admins\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column name')) {
        echo "  ⏭️  role column already exists\n";
    } else {
        echo "  ❌ Failed adding role column: " . $e->getMessage() . "\n";
    }
}

try {
    $db->exec("ALTER TABLE admins ADD INDEX idx_admin_role (role)");
    echo "  ✅ Added role index\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate key name')) {
        echo "  ⏭️  role index already exists\n";
    } else {
        echo "  ❌ Failed adding role index: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Migration v11 complete.\n";
