<?php
// =============================================================
// admin/employee-transfer.php - نقل موظف بين الفروع
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'نقل الموظفين';
$activePage = 'employees';

// =================== إجراء النقل ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_error'] = 'طلب غير صالح';
        header('Location: employee-transfer.php');
        exit;
    }

    $empId      = (int)($_POST['employee_id'] ?? 0);
    $toBranchId = (int)($_POST['to_branch_id'] ?? 0);
    $reason     = trim($_POST['reason'] ?? '');

    if ($empId && $toBranchId) {
        // جلب الفرع الحالي
        $stmt = db()->prepare("SELECT branch_id, name FROM employees WHERE id = ? AND is_active = 1 AND deleted_at IS NULL");
        $stmt->execute([$empId]);
        $emp = $stmt->fetch();

        if ($emp) {
            $fromBranchId = $emp['branch_id'];

            if ($fromBranchId == $toBranchId) {
                $_SESSION['flash_error'] = 'الموظف بالفعل في هذا الفرع';
            } else {
                // تسجيل النقل
                try {
                    $stmt = db()->prepare("INSERT INTO employee_transfers (employee_id, from_branch_id, to_branch_id, transfer_date, reason, transferred_by) VALUES (?, ?, ?, CURDATE(), ?, ?)");
                    $stmt->execute([$empId, $fromBranchId, $toBranchId, $reason ?: null, $_SESSION['admin_id']]);
                } catch (PDOException $e) {
                    // جدول غير موجود - تجاهل
                }

                // تحديث الفرع
                db()->prepare("UPDATE employees SET branch_id = ? WHERE id = ?")->execute([$toBranchId, $empId]);

                // إشعار
                try {
                    $stmtFrom = db()->prepare("SELECT name FROM branches WHERE id = ?");
                    $stmtFrom->execute([$fromBranchId]);
                    $brFromName = $stmtFrom->fetchColumn() ?: 'بدون';

                    $stmtTo = db()->prepare("SELECT name FROM branches WHERE id = ?");
                    $stmtTo->execute([$toBranchId]);
                    $brToName = $stmtTo->fetchColumn() ?: 'غير محدد';

                    db()->prepare("INSERT INTO notifications (title, message, type, category, link) VALUES (?, ?, 'info', 'transfer', ?)")->execute([
                        'نقل: ' . $emp['name'],
                        'تم نقل ' . $emp['name'] . ' من ' . $brFromName . ' إلى ' . $brToName,
                        'employee-profile.php?id=' . $empId
                    ]);
                } catch (PDOException $e) {}

                auditLog('transfer_employee', "نقل {$emp['name']} من فرع {$fromBranchId} إلى {$toBranchId}", $empId);
                $_SESSION['flash_success'] = "تم نقل {$emp['name']} بنجاح";
            }
        } else {
            $_SESSION['flash_error'] = 'الموظف غير موجود';
        }
    } else {
        $_SESSION['flash_error'] = 'اختر الموظف والفرع';
    }

    header('Location: employee-transfer.php');
    exit;
}

// =================== البيانات ===================
$employees = db()->query("
    SELECT e.id, e.name, e.job_title, e.branch_id, b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE e.is_active = 1 AND e.deleted_at IS NULL
    ORDER BY e.name
")->fetchAll();

$branches = db()->query("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name")->fetchAll();

// سجل النقل
$transfers = [];
try {
    $transfers = db()->query("
        SELECT t.*, e.name AS emp_name, e.job_title,
               fb.name AS from_branch, tb.name AS to_branch,
               a.full_name AS admin_name
        FROM employee_transfers t
        JOIN employees e ON t.employee_id = e.id
        LEFT JOIN branches fb ON t.from_branch_id = fb.id
        LEFT JOIN branches tb ON t.to_branch_id = tb.id
        LEFT JOIN admins a ON t.transferred_by = a.id
        ORDER BY t.created_at DESC
        LIMIT 50
    ")->fetchAll();
} catch (PDOException $e) {}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- نموذج النقل -->
<div class="card" style="margin-bottom:20px;padding:20px">
    <h3 style="font-size:1rem;margin-bottom:16px"><span class="card-title-bar"></span> نقل موظف إلى فرع آخر</h3>
    <form method="POST" style="display:grid;gap:14px;max-width:500px">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <div>
            <label style="font-size:.82rem;color:var(--text3);display:block;margin-bottom:4px">الموظف</label>
            <select name="employee_id" required id="empSelect"
                    style="width:100%;padding:10px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.9rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="">اختر الموظف</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" data-branch="<?= $emp['branch_id'] ?>"><?= htmlspecialchars($emp['name']) ?> — <?= htmlspecialchars($emp['branch_name'] ?? 'بدون فرع') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:.82rem;color:var(--text3);display:block;margin-bottom:4px">نقل إلى فرع</label>
            <select name="to_branch_id" required
                    style="width:100%;padding:10px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.9rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="">اختر الفرع الجديد</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:.82rem;color:var(--text3);display:block;margin-bottom:4px">سبب النقل (اختياري)</label>
            <textarea name="reason" rows="2" style="width:100%;padding:10px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.9rem;resize:vertical;background:var(--surface2,#F8FAFC);color:var(--text-primary)"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 24px;width:fit-content" onclick="return confirm('تأكيد نقل الموظف؟')">🔄 تنفيذ النقل</button>
    </form>
</div>

<!-- سجل النقل -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 16px;border-bottom:1px solid var(--border-color,#E2E8F0)">
        <strong style="font-size:.95rem"><span class="card-title-bar"></span> سجل التنقلات</strong>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface2,#F8FAFC)">
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">الموظف</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">من</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">→</th>
                    <th style="padding:12px 14px;text-align:center;font-size:.82rem;color:var(--text3);font-weight:600">إلى</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">التاريخ</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">بواسطة</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">السبب</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transfers)): ?>
                    <tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text3)">لا توجد تنقلات مسجلة</td></tr>
                <?php endif; ?>
                <?php foreach ($transfers as $t): ?>
                <tr style="border-bottom:1px solid var(--border-color,#E2E8F0)">
                    <td style="padding:10px 14px">
                        <strong style="font-size:.88rem"><?= htmlspecialchars($t['emp_name']) ?></strong>
                        <div style="font-size:.75rem;color:var(--text3)"><?= htmlspecialchars($t['job_title'] ?? '') ?></div>
                    </td>
                    <td style="padding:10px 14px;text-align:center;font-size:.85rem;color:#EF4444"><?= htmlspecialchars($t['from_branch'] ?? 'بدون') ?></td>
                    <td style="padding:10px 14px;text-align:center;font-size:1.2rem">→</td>
                    <td style="padding:10px 14px;text-align:center;font-size:.85rem;color:#10B981;font-weight:600"><?= htmlspecialchars($t['to_branch'] ?? '—') ?></td>
                    <td style="padding:10px 14px;font-size:.82rem;color:var(--text3);direction:ltr;text-align:right"><?= $t['transfer_date'] ?></td>
                    <td style="padding:10px 14px;font-size:.85rem"><?= htmlspecialchars($t['admin_name'] ?? 'نظام') ?></td>
                    <td style="padding:10px 14px;font-size:.82rem;color:var(--text3)"><?= htmlspecialchars($t['reason'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
</div></div>
</body></html>
