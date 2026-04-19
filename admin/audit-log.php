<?php
// =============================================================
// admin/audit-log.php - سجل المراجعة (Audit Log)
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'سجل المراجعة';
$activePage = 'audit-log';

// =================== فلاتر ===================
$actionFilter = $_GET['action'] ?? '';
$dateFrom     = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo       = $_GET['date_to'] ?? date('Y-m-d');
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 30;
$offset       = ($page - 1) * $perPage;

$where  = "DATE(al.created_at) BETWEEN ? AND ?";
$params = [$dateFrom, $dateTo];

if ($actionFilter) {
    $where .= " AND al.action = ?";
    $params[] = $actionFilter;
}
if ($search) {
    $where .= " AND (al.details LIKE ? OR a.username LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// عدد السجلات
$countStmt = db()->prepare("
    SELECT COUNT(*) FROM audit_log al
    LEFT JOIN admins a ON al.admin_id = a.id
    WHERE {$where}
");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));

// جلب السجلات
$fetchParams = $params;
$fetchParams[] = $perPage;
$fetchParams[] = $offset;

$logStmt = db()->prepare("
    SELECT al.*, a.username AS admin_name, a.full_name AS admin_full_name
    FROM audit_log al
    LEFT JOIN admins a ON al.admin_id = a.id
    WHERE {$where}
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
");
$logStmt->execute($fetchParams);
$logs = $logStmt->fetchAll();

// أنواع العمليات
$actionLabels = [
    'login' => ['label' => 'تسجيل دخول', 'icon' => '🔑', 'color' => '#2563EB'],
    'add_employee' => ['label' => 'إضافة موظف', 'icon' => '➕', 'color' => '#10B981'],
    'edit_employee' => ['label' => 'تعديل موظف', 'icon' => '✏️', 'color' => '#F59E0B'],
    'delete_employee' => ['label' => 'حذف موظف', 'icon' => '🗑️', 'color' => '#EF4444'],
    'restore_employee' => ['label' => 'استعادة موظف', 'icon' => '♻️', 'color' => '#10B981'],
    'change_pin' => ['label' => 'تغيير PIN', 'icon' => '🔢', 'color' => '#8B5CF6'],
    'update_settings' => ['label' => 'تحديث إعدادات', 'icon' => '⚙️', 'color' => '#6366F1'],
    'change_password' => ['label' => 'تغيير كلمة مرور', 'icon' => '🔒', 'color' => '#DC2626'],
    'transfer_employee' => ['label' => 'نقل موظف', 'icon' => '🔄', 'color' => '#0891B2'],
    'approve_leave' => ['label' => 'قبول إجازة', 'icon' => '✅', 'color' => '#10B981'],
    'reject_leave' => ['label' => 'رفض إجازة', 'icon' => '❌', 'color' => '#EF4444'],
    'backup' => ['label' => 'نسخ احتياطي', 'icon' => '💾', 'color' => '#0D9488'],
    'edit_attendance' => ['label' => 'تعديل حضور', 'icon' => '📝', 'color' => '#F59E0B'],
    'delete_attendance' => ['label' => 'حذف حضور', 'icon' => '🗑️', 'color' => '#EF4444'],
];

// إحصائيات سريعة
$todayActions = (int)db()->query("SELECT COUNT(*) FROM audit_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// جلب قائمة العمليات الفريدة
$actions = db()->query("SELECT DISTINCT action FROM audit_log ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- إحصائيات -->
<div class="stats-grid" style="margin-bottom:16px">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#DBEAFE;color:#2563EB">📋</div>
        <div><div class="stat-value"><?= $totalRecords ?></div><div class="stat-label">سجلات في الفترة</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#D1FAE5;color:#065F46">📊</div>
        <div><div class="stat-value"><?= $todayActions ?></div><div class="stat-label">عمليات اليوم</div></div>
    </div>
</div>

<!-- أدوات البحث -->
<div class="card" style="margin-bottom:16px;padding:14px">
    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">من</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                   style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">إلى</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                   style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">نوع العملية</label>
            <select name="action" style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary)">
                <option value="">كل العمليات</option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= $actionFilter === $a ? 'selected' : '' ?>>
                        <?= $actionLabels[$a]['label'] ?? $a ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:.78rem;color:var(--text3);display:block;margin-bottom:3px">بحث</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ابحث في التفاصيل..."
                   style="padding:8px 12px;border:1px solid var(--border-color,#E2E8F0);border-radius:8px;font-size:.88rem;background:var(--surface2,#F8FAFC);color:var(--text-primary);width:180px">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:8px 20px">بحث</button>
    </form>
</div>

<!-- الجدول -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:var(--surface2,#F8FAFC)">
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">#</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">المشرف</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">العملية</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">التفاصيل</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">IP</th>
                    <th style="padding:12px 14px;text-align:right;font-size:.82rem;color:var(--text3);font-weight:600">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" style="padding:40px;text-align:center;color:var(--text3)">لا توجد سجلات</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $log):
                    $al = $actionLabels[$log['action']] ?? ['label' => $log['action'], 'icon' => '📌', 'color' => '#64748B'];
                ?>
                <tr style="border-bottom:1px solid var(--border-color,#E2E8F0)">
                    <td style="padding:10px 14px;font-size:.82rem;color:var(--text3)"><?= $log['id'] ?></td>
                    <td style="padding:10px 14px;font-size:.88rem;font-weight:600"><?= htmlspecialchars($log['admin_full_name'] ?? $log['admin_name'] ?? 'نظام') ?></td>
                    <td style="padding:10px 14px">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:8px;font-size:.8rem;font-weight:600;background:<?= $al['color'] ?>15;color:<?= $al['color'] ?>">
                            <?= $al['icon'] ?> <?= $al['label'] ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;font-size:.84rem;color:var(--text2);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                        <?= htmlspecialchars(mb_substr($log['details'] ?? '—', 0, 80)) ?>
                    </td>
                    <td style="padding:10px 14px;font-size:.78rem;color:var(--text3);direction:ltr;text-align:right;font-family:monospace"><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                    <td style="padding:10px 14px;font-size:.8rem;color:var(--text3);direction:ltr;text-align:right;white-space:nowrap"><?= date('Y-m-d h:i A', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap">
    <?php for ($p = 1; $p <= $totalPages; $p++):
        $qp = array_merge($_GET, ['page' => $p]);
    ?>
        <a href="?<?= http_build_query($qp) ?>"
           style="padding:6px 14px;border-radius:8px;font-size:.85rem;text-decoration:none;
                  <?= $p == $page ? 'background:var(--accent-gold,#D4A841);color:#0F172A;font-weight:700' : 'background:var(--surface2,#F8FAFC);color:var(--text-primary)' ?>">
            <?= $p ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
</div></div>
</body></html>
