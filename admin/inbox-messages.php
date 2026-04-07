<?php
// =============================================================
// admin/inbox-messages.php - سجل رسائل صندوق الوارد المرسلة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'سجل رسائل الموظفين';
$activePage = 'inbox-messages';

// ── فلاتر ──
$filterType   = $_GET['type']   ?? '';
$filterEmpId  = !empty($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$filterDate   = $_GET['date']   ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

// ── معالجة POST (حذف) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
        exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_message') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'معرف غير صالح']); exit; }

        $msg = db()->prepare("SELECT id, employee_id, is_read FROM employee_inbox WHERE id = ?");
        $msg->execute([$id]);
        $msg = $msg->fetch();
        if (!$msg) { echo json_encode(['success' => false, 'message' => 'الرسالة غير موجودة']); exit; }

        db()->prepare("DELETE FROM employee_inbox WHERE id = ?")->execute([$id]);
        if (!$msg['is_read']) {
            db()->prepare("UPDATE employees SET unread_inbox_count = GREATEST(0, unread_inbox_count - 1) WHERE id = ?")
                ->execute([$msg['employee_id']]);
        }
        auditLog('inbox_delete', "حذف رسالة #{$id}");
        echo json_encode(['success' => true, 'message' => 'تم حذف الرسالة']);
        exit;
    }

    if ($action === 'delete_all_employee') {
        $eid = (int)($_POST['employee_id'] ?? 0);
        if ($eid <= 0) { echo json_encode(['success' => false, 'message' => 'موظف غير صالح']); exit; }
        $cnt = db()->prepare("SELECT COUNT(*) FROM employee_inbox WHERE employee_id = ?");
        $cnt->execute([$eid]);
        $n = (int)$cnt->fetchColumn();
        db()->prepare("DELETE FROM employee_inbox WHERE employee_id = ?")->execute([$eid]);
        db()->prepare("UPDATE employees SET unread_inbox_count = 0 WHERE id = ?")->execute([$eid]);
        auditLog('inbox_delete_all', "حذف جميع رسائل الموظف #{$eid} ({$n} رسالة)");
        echo json_encode(['success' => true, 'message' => "تم حذف {$n} رسالة"]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

// ── بناء الاستعلام ──
$where  = ['1=1'];
$params = [];

if ($filterType && in_array($filterType, ['violation','deduction','reward','warning','info'])) {
    $where[] = 'i.msg_type = ?';
    $params[] = $filterType;
}
if ($filterEmpId > 0) {
    $where[] = 'i.employee_id = ?';
    $params[] = $filterEmpId;
}
if ($filterDate) {
    $where[] = 'DATE(i.created_at) = ?';
    $params[] = $filterDate;
}
if ($search !== '') {
    $where[] = '(i.title LIKE ? OR i.body LIKE ? OR e.name LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like]);
}

$whereStr = implode(' AND ', $where);

$countStmt = db()->prepare("
    SELECT COUNT(*) FROM employee_inbox i
    JOIN employees e ON i.employee_id = e.id
    WHERE {$whereStr}
");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$dataStmt = db()->prepare("
    SELECT i.*,
           e.name AS emp_name, e.job_title, b.name AS branch_name,
           a.full_name AS admin_name
    FROM employee_inbox i
    JOIN employees e ON i.employee_id = e.id
    LEFT JOIN branches b ON e.branch_id = b.id
    LEFT JOIN admins a ON i.admin_id = a.id
    WHERE {$whereStr}
    ORDER BY i.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
");
$dataStmt->execute($params);
$messages = $dataStmt->fetchAll();

// ── ملخص الإحصائيات ──
$statsStmt = db()->query("
    SELECT msg_type, COUNT(*) AS cnt,
           SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) AS unread_cnt
    FROM employee_inbox
    GROUP BY msg_type
");
$stats = [];
foreach ($statsStmt->fetchAll() as $s) {
    $stats[$s['msg_type']] = $s;
}

$employees = db()->query("
    SELECT e.id, e.name FROM employees e
    WHERE e.is_active=1 AND e.deleted_at IS NULL ORDER BY e.name
")->fetchAll();

$typeConfig = [
    'violation' => ['label'=>'مخالفة',    'icon'=>'🚫','color'=>'#EF4444','bg'=>'#FEF2F2'],
    'deduction' => ['label'=>'خصم',       'icon'=>'💸','color'=>'#F59E0B','bg'=>'#FFFBEB'],
    'reward'    => ['label'=>'مكافأة',    'icon'=>'🏆','color'=>'#10B981','bg'=>'#ECFDF5'],
    'warning'   => ['label'=>'تحذير',     'icon'=>'⚠️','color'=>'#F97316','bg'=>'#FFF7ED'],
    'info'      => ['label'=>'إشعار عام', 'icon'=>'ℹ️','color'=>'#3B82F6','bg'=>'#EFF6FF'],
];

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header">
    <div class="page-title">
        <h1>📬 سجل رسائل الموظفين</h1>
        <p>عرض وإدارة جميع الرسائل المرسلة لصناديق الوارد</p>
    </div>
    <div class="page-actions">
        <a href="inbox-send.php" class="btn btn-primary">
            <?= svgIcon('bell', 16) ?> إرسال رسالة جديدة
        </a>
    </div>
</div>

<!-- إحصائيات سريعة -->
<div class="stats-grid" style="margin-bottom:24px">
    <?php foreach ($typeConfig as $type => $cfg): $s = $stats[$type] ?? ['cnt'=>0,'unread_cnt'=>0]; ?>
    <div class="stat-card" style="border-right:4px solid <?= $cfg['color'] ?>;cursor:pointer"
         onclick="window.location.href='?type=<?= $type ?>'">
        <div class="stat-value" style="color:<?= $cfg['color'] ?>"><?= number_format($s['cnt']) ?></div>
        <div class="stat-label"><?= $cfg['icon'] ?> إجمالي <?= $cfg['label'] ?></div>
        <?php if ($s['unread_cnt'] > 0): ?>
        <div style="font-size:.72rem;color:#EF4444;font-weight:700;margin-top:4px">
            <?= $s['unread_cnt'] ?> غير مقروءة
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- فلاتر -->
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px">
        <form method="get" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
                <label class="form-label" style="font-size:.78rem">نوع الرسالة</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">الكل</option>
                    <?php foreach ($typeConfig as $type => $cfg): ?>
                    <option value="<?= $type ?>" <?= $filterType===$type?'selected':'' ?>><?= $cfg['icon'] ?> <?= $cfg['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:160px">
                <label class="form-label" style="font-size:.78rem">الموظف</label>
                <select name="emp_id" class="form-control form-control-sm">
                    <option value="">الكل</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= $filterEmpId===$emp['id']?'selected':'' ?>><?= htmlspecialchars($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:140px">
                <label class="form-label" style="font-size:.78rem">التاريخ</label>
                <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($filterDate) ?>">
            </div>
            <div class="form-group" style="margin-bottom:0;flex:2;min-width:180px">
                <label class="form-label" style="font-size:.78rem">بحث</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="اسم الموظف أو محتوى الرسالة..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">بحث</button>
            <a href="inbox-messages.php" class="btn btn-secondary btn-sm">مسح</a>
        </form>
    </div>
</div>

<!-- الجدول -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            الرسائل
            <span style="font-size:.8rem;font-weight:400;color:#94A3B8;margin-right:8px">
                (<?= number_format($totalRows) ?> رسالة)
            </span>
        </h3>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($messages)): ?>
        <div style="text-align:center;padding:60px 20px;color:#94A3B8">
            <div style="font-size:3rem;margin-bottom:12px">📭</div>
            <div style="font-size:1rem;font-weight:600">لا توجد رسائل</div>
            <div style="font-size:.85rem;margin-top:6px">جرب تغيير الفلاتر أو <a href="inbox-send.php" style="color:#F97316">أرسل رسالة جديدة</a></div>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>النوع</th>
                    <th>الموظف</th>
                    <th>العنوان</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>المرسِل</th>
                    <th style="width:80px">إجراء</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $i => $msg):
                $cfg = $typeConfig[$msg['msg_type']] ?? $typeConfig['info'];
            ?>
            <tr id="row_<?= $msg['id'] ?>" style="<?= !$msg['is_read'] ? 'background:#FFFBEB' : '' ?>">
                <td style="color:#94A3B8;font-size:.75rem"><?= $offset + $i + 1 ?></td>
                <td>
                    <span style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;white-space:nowrap">
                        <?= $cfg['icon'] ?> <?= $cfg['label'] ?>
                    </span>
                </td>
                <td>
                    <div style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($msg['emp_name']) ?></div>
                    <?php if ($msg['branch_name']): ?>
                    <div style="font-size:.72rem;color:#94A3B8"><?= htmlspecialchars($msg['branch_name']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600;font-size:.88rem;max-width:200px"
                         title="<?= htmlspecialchars($msg['body']) ?>">
                        <?= htmlspecialchars($msg['title']) ?>
                        <?php if (!$msg['is_read']): ?>
                        <span style="background:#EF4444;color:#fff;font-size:.6rem;padding:1px 6px;border-radius:10px;vertical-align:middle;margin-right:4px">جديد</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.72rem;color:#94A3B8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars(mb_substr($msg['body'], 0, 80)) ?>...
                    </div>
                </td>
                <td style="text-align:center">
                    <?php if ($msg['amount'] !== null): ?>
                    <span style="font-weight:700;color:<?= $msg['msg_type']==='reward'?'#10B981':'#EF4444' ?>">
                        <?= $msg['msg_type']==='reward' ? '+' : '-' ?><?= number_format($msg['amount'], 2) ?> <?= htmlspecialchars($msg['currency']) ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#CBD5E1">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($msg['is_read']): ?>
                    <span style="color:#10B981;font-size:.78rem;font-weight:600">✔ مقروءة</span>
                    <?php if ($msg['read_at']): ?>
                    <div style="font-size:.68rem;color:#94A3B8"><?= date('d/m H:i', strtotime($msg['read_at'])) ?></div>
                    <?php endif; ?>
                    <?php else: ?>
                    <span style="color:#F59E0B;font-size:.78rem;font-weight:600">● لم تُقرأ</span>
                    <?php endif; ?>
                </td>
                <td style="white-space:nowrap;font-size:.8rem">
                    <?= date('d/m/Y', strtotime($msg['created_at'])) ?><br>
                    <span style="color:#94A3B8;font-size:.72rem"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                </td>
                <td style="font-size:.78rem;color:#64748B">
                    <?= htmlspecialchars($msg['admin_name'] ?? 'نظام') ?>
                </td>
                <td>
                    <button class="btn btn-sm" style="color:#EF4444;background:none;border:1px solid #FEE2E2;padding:4px 10px"
                            onclick="deleteMsg(<?= $msg['id'] ?>)">حذف</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- ترقيم الصفحات -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:6px;padding:16px;flex-wrap:wrap">
            <?php
            $baseUrl = '?' . http_build_query(array_filter([
                'type'   => $filterType,
                'emp_id' => $filterEmpId ?: null,
                'date'   => $filterDate,
                'search' => $search,
            ]));
            for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= $baseUrl ?>&page=<?= $p ?>"
               style="padding:6px 12px;border-radius:8px;font-size:.8rem;font-weight:600;
                      background:<?= $p===$page?'#F97316':'#F1F5F9' ?>;
                      color:<?= $p===$page?'#fff':'#475569' ?>;text-decoration:none">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const _csrf = <?= json_encode(generateCsrfToken()) ?>;

async function deleteMsg(id) {
    if (!confirm('هل تريد حذف هذه الرسالة؟')) return;
    const fd = new FormData();
    fd.append('action', 'delete_message');
    fd.append('id', id);
    fd.append('csrf_token', _csrf);
    const res = await fetch('inbox-messages.php', {method:'POST', body:fd});
    const data = await res.json();
    if (data.success) {
        Toast.success(data.message);
        const row = document.getElementById('row_' + id);
        if (row) row.remove();
    } else {
        Toast.error(data.message);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
