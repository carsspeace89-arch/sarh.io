<?php
// =============================================================
// admin/backups.php - النسخ الاحتياطي
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'النسخ الاحتياطي';
$activePage = 'backups';

$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0750, true);
    @file_put_contents($backupDir . '/.htaccess', "Deny from all\n");
}

// =================== إجراءات POST ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'طلب غير صالح';
        header('Location: backups.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create_backup') {
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // تصدير PHP (آمن على الاستضافة المشتركة بدون exec)
        try {
            $tables = db()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $sql = "-- Backup: {$filename}\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Database: " . DB_NAME . "\n-- Method: PHP Export\n\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

            foreach ($tables as $table) {
                // CREATE TABLE
                $createStmt = db()->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createStmt['Create Table'] . ";\n\n";

                // INSERT DATA (بدفعات لتقليل استهلاك الذاكرة)
                $countRow = db()->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                if ($countRow > 0) {
                    $batchSize = 500;
                    for ($batchOffset = 0; $batchOffset < $countRow; $batchOffset += $batchSize) {
                        $rows = db()->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$batchOffset}")->fetchAll(PDO::FETCH_ASSOC);
                        if (empty($rows)) break;
                        $cols = array_keys($rows[0]);
                        $colsList = '`' . implode('`, `', $cols) . '`';
                        foreach (array_chunk($rows, 100) as $chunk) {
                            $sql .= "INSERT INTO `{$table}` ({$colsList}) VALUES\n";
                            $vals = [];
                            foreach ($chunk as $row) {
                                $escaped = array_map(function($v) {
                                    if ($v === null) return 'NULL';
                                    return db()->quote($v);
                                }, array_values($row));
                                $vals[] = '(' . implode(', ', $escaped) . ')';
                            }
                            $sql .= implode(",\n", $vals) . ";\n";
                        }
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($filepath, $sql);
            $size = filesize($filepath);

            try {
                $stmt = db()->prepare("INSERT INTO backups (filename, file_size, backup_type, created_by) VALUES (?, ?, 'manual', ?)");
                $stmt->execute([$filename, $size, $_SESSION['admin_id']]);
            } catch (PDOException $e) {}

            auditLog('backup', "نسخ احتياطي: {$filename} (" . round($size / 1024) . " KB)");
            $_SESSION['flash_success'] = "تم إنشاء النسخة الاحتياطية بنجاح: {$filename}";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'فشل إنشاء النسخة: ' . $e->getMessage();
        }

        header('Location: backups.php');
        exit;
    }

    if ($action === 'delete_backup') {
        $id = (int)($_POST['backup_id'] ?? 0);
        if ($id) {
            $stmt = db()->prepare("SELECT filename FROM backups WHERE id = ?");
            $stmt->execute([$id]);
            $bk = $stmt->fetch();
            if ($bk) {
                $file = $backupDir . '/' . $bk['filename'];
                if (file_exists($file)) @unlink($file);
                db()->prepare("DELETE FROM backups WHERE id = ?")->execute([$id]);
                $_SESSION['flash_success'] = 'تم حذف النسخة';
            }
        }
        header('Location: backups.php');
        exit;
    }

    if ($action === 'download_backup') {
        $id = (int)($_POST['backup_id'] ?? 0);
        if ($id) {
            $stmt = db()->prepare("SELECT filename FROM backups WHERE id = ?");
            $stmt->execute([$id]);
            $bk = $stmt->fetch();
            if ($bk) {
                $file = $backupDir . '/' . $bk['filename'];
                if (file_exists($file)) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $bk['filename'] . '"');
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    exit;
                }
            }
        }
        $_SESSION['flash_error'] = 'الملف غير موجود';
        header('Location: backups.php');
        exit;
    }
}

// =================== جلب النسخ ===================
$backups = [];
try {
    $backups = db()->query("
        SELECT b.*, a.full_name AS admin_name
        FROM backups b
        LEFT JOIN admins a ON b.created_by = a.id
        ORDER BY b.created_at DESC
        LIMIT 50
    ")->fetchAll();
} catch (PDOException $e) {
    // جدول غير موجود بعد
}

// حجم مجلد النسخ
$totalSize = 0;
foreach ($backups as $b) {
    $totalSize += $b['file_size'];
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- إحصائيات -->
<div class="stats-grid" style="margin-bottom:16px">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#DBEAFE;color:#2563EB">💾</div>
        <div><div class="stat-value"><?= count($backups) ?></div><div class="stat-label">النسخ المحفوظة</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#D1FAE5;color:#065F46">📦</div>
        <div><div class="stat-value"><?= round($totalSize / 1024 / 1024, 2) ?> MB</div><div class="stat-label">الحجم الإجمالي</div></div>
    </div>
</div>

<!-- إنشاء نسخة -->
<div class="card" style="margin-bottom:16px;padding:14px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div>
        <strong style="font-size:.95rem">إنشاء نسخة احتياطية جديدة</strong>
        <p style="font-size:.82rem;color:var(--text3);margin:4px 0 0">يتم حفظ جميع الجداول والبيانات</p>
    </div>
    <form method="POST" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='جاري الإنشاء...'">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="action" value="create_backup">
        <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:.9rem">💾 إنشاء نسخة الآن</button>
    </form>
</div>

<!-- قائمة النسخ -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface2,#F8FAFC)">
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">الملف</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">الحجم</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">النوع</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">بواسطة</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">التاريخ</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                    <tr><td colspan="6" style="padding:40px;text-align:center;color:var(--text3)">لا توجد نسخ احتياطية بعد</td></tr>
                <?php endif; ?>
                <?php foreach ($backups as $b): ?>
                <tr style="border-bottom:1px solid var(--border-color,#E2E8F0)">
                    <td style="padding:10px 14px;font-size:.85rem;font-family:monospace;direction:ltr;text-align:right"><?= htmlspecialchars($b['filename']) ?></td>
                    <td style="padding:10px 14px;text-align:center;font-size:.85rem"><?= round($b['file_size'] / 1024) ?> KB</td>
                    <td style="padding:10px 14px;text-align:center">
                        <span class="badge <?= $b['backup_type'] === 'auto' ? 'badge-success' : 'badge-warning' ?>" style="padding:3px 10px;border-radius:12px;font-size:.75rem">
                            <?= $b['backup_type'] === 'auto' ? 'تلقائي' : 'يدوي' ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;font-size:.85rem"><?= htmlspecialchars($b['admin_name'] ?? 'نظام') ?></td>
                    <td style="padding:10px 14px;font-size:.82rem;color:var(--text3);direction:ltr;text-align:right"><?= date('Y-m-d h:i A', strtotime($b['created_at'])) ?></td>
                    <td style="padding:10px 14px;text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <form method="POST" style="margin:0">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="action" value="download_backup">
                                <input type="hidden" name="backup_id" value="<?= $b['id'] ?>">
                                <button type="submit" style="background:#2563EB;color:#fff;border:none;padding:4px 12px;border-radius:6px;font-size:.78rem;cursor:pointer">⬇️ تحميل</button>
                            </form>
                            <form method="POST" style="margin:0" onsubmit="return confirm('حذف هذه النسخة؟')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="action" value="delete_backup">
                                <input type="hidden" name="backup_id" value="<?= $b['id'] ?>">
                                <button type="submit" style="background:#EF4444;color:#fff;border:none;padding:4px 12px;border-radius:6px;font-size:.78rem;cursor:pointer">🗑️ حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.badge-warning { background: #FEF3C7; color: #92400E; }
.badge-success { background: #D1FAE5; color: #065F46; }
html.dark .badge-warning { background: rgba(245,158,11,.15); color: #FBBF24; }
html.dark .badge-success { background: rgba(16,185,129,.15); color: #34D399; }
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
</div></div>
</body></html>
