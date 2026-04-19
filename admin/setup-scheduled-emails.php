<?php
// =============================================================
// admin/setup-scheduled-emails.php - تثبيت نظام المراسلات المجدولة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();

$output = [];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = db();
        
        // Read and execute the migration file
        $sqlFile = __DIR__ . '/../migrations/007_scheduled_emails.sql';
        
        if (!file_exists($sqlFile)) {
            $errors[] = "ملف الSQL غير موجود: $sqlFile";
        } else {
            $sql = file_get_contents($sqlFile);
            
            // Remove comments and split by semicolon
            $sql = preg_replace('/--.*$/m', '', $sql);  // Remove single-line comments
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);  // Remove multi-line comments
            
            // Split into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) { return !empty($stmt); }
            );
            
            $output[] = "Found " . count($statements) . " SQL statements";
            
            // Execute each statement
            foreach ($statements as $i => $statement) {
                try {
                    $db->exec($statement);
                    $output[] = "✅ Statement " . ($i + 1) . " executed successfully";
                } catch (PDOException $e) {
                    // Ignore "table already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'Duplicate') === false) {
                        $errors[] = "Statement " . ($i + 1) . " failed: " . $e->getMessage();
                    } else {
                        $output[] = "⚠️  Statement " . ($i + 1) . " skipped (already exists)";
                    }
                }
            }
            
            // Verify tables were created
            $tables = $db->query("SHOW TABLES LIKE 'scheduled_emails'")->fetchAll();
            if (count($tables) > 0) {
                $output[] = "✅ scheduled_emails table verified";
                $success = true;
            } else {
                $errors[] = "❌ scheduled_emails table was not created";
            }
            
            $tables = $db->query("SHOW TABLES LIKE 'email_send_log'")->fetchAll();
            if (count($tables) > 0) {
                $output[] = "✅ email_send_log table verified";
            } else {
                $errors[] = "❌ email_send_log table was not created";
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "خطأ: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت نظام المراسلات المجدولة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; margin-bottom: 1.5rem; font-size: 1.75rem; }
        .info-box { background: #dbeafe; border: 1px solid #3b82f6; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; }
        .info-box h3 { color: #1e40af; margin-bottom: 0.5rem; }
        .info-box ul { margin-right: 1.5rem; color: #1e3a8a; }
        .info-box li { margin-bottom: 0.25rem; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; text-decoration: none; transition: background 0.2s; }
        .btn:hover { background: #2563eb; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
        .output-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; font-family: monospace; font-size: 0.9rem; max-height: 400px; overflow-y: auto; }
        .output-line { margin-bottom: 0.5rem; }
        .error-box { background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; padding: 1rem; margin-top: 1rem; color: #991b1b; }
        .success-box { background: #d1fae5; border: 1px solid #10b981; border-radius: 8px; padding: 1rem; margin-top: 1rem; color: #065f46; }
        .actions { display: flex; gap: 1rem; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 تثبيت نظام المراسلات المجدولة</h1>
        
        <div class="info-box">
            <h3>📋 ما سيتم تثبيته:</h3>
            <ul>
                <li>جدول <code>scheduled_emails</code> - لتخزين إعدادات المراسلات المجدولة</li>
                <li>جدول <code>email_send_log</code> - لتسجيل جميع عمليات الإرسال</li>
                <li>بيانات تجريبية (اختياري)</li>
            </ul>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <h3 style="margin-bottom: 0.75rem;">❌ حدثت أخطاء:</h3>
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-box">
                <h3 style="margin-bottom: 0.75rem;">✅ تم التثبيت بنجاح!</h3>
                <p>يمكنك الآن استخدام نظام المراسلات المجدولة</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($output)): ?>
            <div class="output-box">
                <?php foreach ($output as $line): ?>
                    <div class="output-line"><?= htmlspecialchars($line) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="actions">
            <?php if (!$success): ?>
                <form method="POST" style="display: inline;">
                    <button type="submit" class="btn">🚀 بدء التثبيت</button>
                </form>
            <?php else: ?>
                <a href="scheduled-emails.php" class="btn">📧 فتح صفحة المراسلات المجدولة</a>
            <?php endif; ?>
            <a href="test-scheduled-db.php" class="btn btn-secondary">🔍 فحص قاعدة البيانات</a>
            <a href="dashboard.php" class="btn btn-secondary">🏠 العودة للوحة التحكم</a>
        </div>
    </div>
</body>
</html>
