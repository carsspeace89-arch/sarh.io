<?php
// Quick test to check if scheduled_emails tables exist
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

echo "<h2>🔍 Database Tables Check</h2>";
echo "<pre>";

try {
    // Test if scheduled_emails table exists
    $stmt = db()->query("SHOW TABLES LIKE 'scheduled_emails'");
    if ($stmt->rowCount() > 0) {
        echo "✅ scheduled_emails table EXISTS\n";
        
        // Count records
        $count = db()->query("SELECT COUNT(*) FROM scheduled_emails")->fetchColumn();
        echo "   Records: $count\n\n";
        
        // Show columns
        echo "   Columns:\n";
        $cols = db()->query("SHOW COLUMNS FROM scheduled_emails")->fetchAll();
        foreach ($cols as $col) {
            echo "   - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "❌ scheduled_emails table DOES NOT EXIST\n";
        echo "   Run: /home/u307296675/domains/sarh.io/public_html/migrations/007_scheduled_emails.sql\n";
    }
    
    echo "\n";
    
    // Test if email_send_log table exists
    $stmt = db()->query("SHOW TABLES LIKE 'email_send_log'");
    if ($stmt->rowCount() > 0) {
        echo "✅ email_send_log table EXISTS\n";
        $count = db()->query("SELECT COUNT(*) FROM email_send_log")->fetchColumn();
        echo "   Records: $count\n";
    } else {
        echo "❌ email_send_log table DOES NOT EXIST\n";
    }
    
    echo "\n";
    echo "✅ Database connection OK\n";
    echo "   Database: " . DB_NAME . "\n";
    echo "   Host: " . DB_HOST . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If tables don't exist, run the SQL migration file</li>";
echo "<li>You can run it via phpMyAdmin or mysql command line</li>";
echo "<li>File location: <code>/home/u307296675/domains/sarh.io/public_html/migrations/007_scheduled_emails.sql</code></li>";
echo "</ol>";

echo "<br><a href='scheduled-emails.php' style='display:inline-block;padding:10px 20px;background:#3b82f6;color:white;text-decoration:none;border-radius:6px;'>Try Opening Scheduled Emails Page</a>";
echo " ";
echo "<a href='dashboard.php' style='display:inline-block;padding:10px 20px;background:#6b7280;color:white;text-decoration:none;border-radius:6px;'>Back to Dashboard</a>";
?>
