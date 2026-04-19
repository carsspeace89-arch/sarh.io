<?php
// =============================================================
// admin/employees-archive.php - أرشيف الموظفين المحذوفين
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'أرشيف الموظفين';
$activePage = 'employees';

// =================== استعادة / حذف نهائي ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'طلب غير صالح';
        header('Location: employees-archive.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $empId  = (int)($_POST['emp_id'] ?? 0);

    if ($action === 'restore' && $empId) {
        db()->prepare("UPDATE employees SET deleted_at = NULL, is_active = 1 WHERE id = ?")->execute([$empId]);
        auditLog('restore_employee', "استعادة موظف ID={$empId}", $empId);
        $_SESSION['flash_success'] = 'تم استعادة الموظف بنجاح';
    }

    if ($action === 'permanent_delete' && $empId) {
        // حذف نهائي - حذف البيانات المرتبطة أولاً
        db()->prepare("DELETE FROM attendances WHERE employee_id = ?")->execute([$empId]);
        db()->prepare("DELETE FROM leaves WHERE employee_id = ?")->execute([$empId]);
        try { db()->prepare("DELETE FROM employee_transfers WHERE employee_id = ?")->execute([$empId]); } catch (PDOException $e) {}
        try { db()->prepare("DELETE FROM leave_balances WHERE employee_id = ?")->execute([$empId]); } catch (PDOException $e) {}
        
        // حذف الوثائق
        $groups = db()->prepare("SELECT id FROM emp_document_groups WHERE employee_id = ?");
        $groups->execute([$empId]);
        foreach ($groups->fetchAll() as $g) {
            $files = db()->prepare("SELECT file_path FROM emp_document_files WHERE group_id = ?");
            $files->execute([$g['id']]);
            foreach ($files->fetchAll() as $f) {
                $path = realpath(__DIR__ . '/../' . $f['file_path']);
                $baseDir = realpath(__DIR__ . '/../');
                if ($path && $baseDir && strpos($path, $baseDir) === 0 && file_exists($path)) {
                    @unlink($path);
                }
            }
            db()->prepare("DELETE FROM emp_document_files WHERE group_id = ?")->execute([$g['id']]);
        }
        db()->prepare("DELETE FROM emp_document_groups WHERE employee_id = ?")->execute([$empId]);
        
        // حذف الموظف نهائياً
        db()->prepare("DELETE FROM employees WHERE id = ?")->execute([$empId]);
        auditLog('permanent_delete', "حذف نهائي لموظف ID={$empId}", $empId);
        $_SESSION['flash_success'] = 'تم الحذف النهائي للموظف';
    }

    header('Location: employees-archive.php');
    exit;
}

// =================== جلب المؤرشفين ===================
$archived = db()->query("
    SELECT e.*, b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE e.deleted_at IS NOT NULL
    ORDER BY e.deleted_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <a href="employees.php" class="btn btn-secondary" style="padding:8px 16px">← العودة لإدارة الموظفين</a>
    <span style="font-size:.85rem;color:var(--text3)"><?= count($archived) ?> موظف مؤرشف</span>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface2,#F8FAFC)">
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">الموظف</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">الوظيفة</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">الفرع</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">تاريخ الأرشفة</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($archived)): ?>
                    <tr><td colspan="5" style="padding:40px;text-align:center;color:var(--text3)">لا يوجد موظفون مؤرشفون</td></tr>
                <?php endif; ?>
                <?php foreach ($archived as $emp): ?>
                <tr style="border-bottom:1px solid var(--border-color,#E2E8F0)">
                    <td style="padding:12px 14px">
                        <strong style="font-size:.9rem"><?= htmlspecialchars($emp['name']) ?></strong>
                        <div style="font-size:.75rem;color:var(--text3)">PIN: <?= htmlspecialchars($emp['pin']) ?></div>
                    </td>
                    <td style="padding:12px 14px;font-size:.88rem"><?= htmlspecialchars($emp['job_title']) ?></td>
                    <td style="padding:12px 14px;font-size:.88rem"><?= htmlspecialchars($emp['branch_name'] ?? '—') ?></td>
                    <td style="padding:12px 14px;font-size:.82rem;color:var(--text3);direction:ltr;text-align:right"><?= date('Y-m-d', strtotime($emp['deleted_at'])) ?></td>
                    <td style="padding:12px 14px;text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <form method="POST" style="margin:0" onsubmit="return confirm('استعادة هذا الموظف؟')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="action" value="restore">
                                <input type="hidden" name="emp_id" value="<?= $emp['id'] ?>">
                                <button type="submit" style="background:#10B981;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:.82rem;cursor:pointer">♻️ استعادة</button>
                            </form>
                            <form method="POST" style="margin:0" onsubmit="return confirm('⚠️ حذف نهائي! سيتم حذف جميع بيانات الموظف بما فيها الحضور والإجازات والوثائق. متأكد؟')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="action" value="permanent_delete">
                                <input type="hidden" name="emp_id" value="<?= $emp['id'] ?>">
                                <button type="submit" style="background:#EF4444;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:.82rem;cursor:pointer">🗑️ حذف نهائي</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
</div></div>
</body></html>
