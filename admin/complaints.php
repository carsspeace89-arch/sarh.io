<?php
// =============================================================
// admin/complaints.php - إدارة شكاوى الموظفين
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'شكاوى الموظفين';
$activePage = 'complaints';

// إنشاء الجدول إذا لم يكن موجوداً
db()->exec("CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT UNSIGNED NOT NULL,
    `complaint_type` ENUM('person','branch','group','other') NOT NULL DEFAULT 'other',
    `target_name` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(500) NOT NULL,
    `body` TEXT NOT NULL,
    `attachments` TEXT DEFAULT NULL,
    `status` ENUM('pending','reviewing','resolved','rejected') NOT NULL DEFAULT 'pending',
    `admin_reply` TEXT DEFAULT NULL,
    `admin_id` INT UNSIGNED DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// إضافة عمود المرفقات إذا لم يكن موجوداً
try {
    db()->exec("ALTER TABLE complaints ADD COLUMN `attachments` TEXT DEFAULT NULL AFTER `body`");
} catch (Exception $e) { /* العمود موجود */ }

// ── معالجة POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
        exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $reply  = trim($_POST['reply'] ?? '');
        $validStatuses = ['pending','reviewing','resolved','rejected'];

        if ($id <= 0 || !in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
            exit;
        }

        $resolvedAt = in_array($status, ['resolved','rejected']) ? date('Y-m-d H:i:s') : null;
        $stmt = db()->prepare("UPDATE complaints SET status = ?, admin_reply = ?, admin_id = ?, resolved_at = ? WHERE id = ?");
        $stmt->execute([$status, $reply ?: null, $_SESSION['admin_id'], $resolvedAt, $id]);

        $statusLabels = ['pending'=>'قيد الانتظار','reviewing'=>'قيد المراجعة','resolved'=>'تم الحل','rejected'=>'مرفوضة'];
        auditLog('complaint_update', "تحديث شكوى #{$id} إلى: {$statusLabels[$status]}");
        echo json_encode(['success' => true, 'message' => 'تم تحديث حالة الشكوى']);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'معرف غير صالح']); exit; }
        db()->prepare("DELETE FROM complaints WHERE id = ?")->execute([$id]);
        auditLog('complaint_delete', "حذف شكوى #{$id}");
        echo json_encode(['success' => true, 'message' => 'تم حذف الشكوى']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

// ── فلاتر ──
$filterStatus = $_GET['status'] ?? '';
$filterType   = $_GET['type']   ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($filterStatus && in_array($filterStatus, ['pending','reviewing','resolved','rejected'])) {
    $where[] = 'c.status = ?';
    $params[] = $filterStatus;
}
if ($filterType && in_array($filterType, ['person','branch','group','other'])) {
    $where[] = 'c.complaint_type = ?';
    $params[] = $filterType;
}
if ($search !== '') {
    $where[] = '(c.subject LIKE ? OR c.body LIKE ? OR c.target_name LIKE ? OR e.name LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$whereStr = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM complaints c JOIN employees e ON c.employee_id = e.id WHERE {$whereStr}");
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$dataParams = array_merge($params, [$perPage, $offset]);
$rows = db()->prepare("
    SELECT c.*, e.name AS employee_name, e.job_title, e.profile_photo
    FROM complaints c
    JOIN employees e ON c.employee_id = e.id
    WHERE {$whereStr}
    ORDER BY FIELD(c.status, 'pending','reviewing','resolved','rejected'), c.created_at DESC
    LIMIT ? OFFSET ?
");
$rows->execute($dataParams);
$complaints = $rows->fetchAll();

// إحصائيات
$statsAll = db()->query("
    SELECT status, COUNT(*) AS cnt FROM complaints GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$statusConfig = [
    'pending'   => ['label'=>'قيد الانتظار','icon'=>'⏳','color'=>'#F59E0B','bg'=>'#FFFBEB'],
    'reviewing' => ['label'=>'قيد المراجعة','icon'=>'🔍','color'=>'#3B82F6','bg'=>'#EFF6FF'],
    'resolved'  => ['label'=>'تم الحل',     'icon'=>'✅','color'=>'#10B981','bg'=>'#ECFDF5'],
    'rejected'  => ['label'=>'مرفوضة',      'icon'=>'❌','color'=>'#EF4444','bg'=>'#FEF2F2'],
];
$typeConfig = [
    'person' => ['label'=>'شخص',     'icon'=>'👤'],
    'branch' => ['label'=>'فرع',     'icon'=>'🏢'],
    'group'  => ['label'=>'مجموعة',  'icon'=>'👥'],
    'other'  => ['label'=>'أخرى',    'icon'=>'📋'],
];

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header">
    <div class="page-title">
        <h1>📢 شكاوى الموظفين</h1>
        <p>إدارة ومتابعة شكاوى الموظفين المقدمة للإدارة العامة</p>
    </div>
</div>

<!-- إحصائيات -->
<div class="stats-grid" style="margin-bottom:24px">
    <?php foreach ($statusConfig as $st => $cfg): ?>
    <div class="stat-card" style="border-right:4px solid <?= $cfg['color'] ?>;cursor:pointer"
         onclick="window.location.href='?status=<?= $st ?>'">
        <div class="stat-value" style="color:<?= $cfg['color'] ?>"><?= number_format($statsAll[$st] ?? 0) ?></div>
        <div class="stat-label"><?= $cfg['icon'] ?> <?= $cfg['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- فلاتر -->
<div class="card" style="margin-bottom:20px">
    <div class="card-body" style="padding:16px">
        <form method="get" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:140px">
                <label class="form-label" style="font-size:.78rem">الحالة</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">الكل</option>
                    <?php foreach ($statusConfig as $st => $cfg): ?>
                    <option value="<?= $st ?>" <?= $filterStatus===$st?'selected':'' ?>><?= $cfg['icon'] ?> <?= $cfg['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:140px">
                <label class="form-label" style="font-size:.78rem">النوع</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">الكل</option>
                    <?php foreach ($typeConfig as $tp => $cfg): ?>
                    <option value="<?= $tp ?>" <?= $filterType===$tp?'selected':'' ?>><?= $cfg['icon'] ?> <?= $cfg['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;flex:2;min-width:180px">
                <label class="form-label" style="font-size:.78rem">بحث</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="بحث بالاسم أو الموضوع..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">🔍 بحث</button>
            <?php if ($filterStatus || $filterType || $search): ?>
            <a href="complaints.php" class="btn btn-outline btn-sm">✕ مسح</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- جدول الشكاوى -->
<div class="card">
    <div class="card-body" style="padding:0">
        <?php if (empty($complaints)): ?>
        <div style="text-align:center;padding:60px 20px;color:#94A3B8">
            <div style="font-size:3rem;margin-bottom:12px">📭</div>
            <div style="font-weight:700">لا توجد شكاوى</div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الموظف</th>
                        <th>النوع</th>
                        <th>الجهة</th>
                        <th>الموضوع</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $c):
                    $sc = $statusConfig[$c['status']] ?? $statusConfig['pending'];
                    $tc = $typeConfig[$c['complaint_type']] ?? $typeConfig['other'];
                ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($c['employee_name']) ?></strong>
                        <?php if ($c['job_title']): ?>
                        <br><small style="color:#94A3B8"><?= htmlspecialchars($c['job_title']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $tc['icon'] ?> <?= $tc['label'] ?></td>
                    <td><?= $c['target_name'] ? htmlspecialchars($c['target_name']) : '<span style="color:#CBD5E1">—</span>' ?></td>
                    <td style="max-width:200px">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                             title="<?= htmlspecialchars($c['subject']) ?>">
                            <?= htmlspecialchars($c['subject']) ?>
                        </div>
                    </td>
                    <td>
                        <span style="padding:3px 10px;border-radius:6px;font-size:.75rem;font-weight:700;background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
                            <?= $sc['icon'] ?> <?= $sc['label'] ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;font-size:.82rem"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="viewComplaint(<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE)) ?>)">
                            👁 عرض
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ترقيم الصفحات -->
        <?php if ($totalPages > 1): ?>
        <div style="padding:16px;display:flex;justify-content:center;gap:4px;flex-wrap:wrap">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
               class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal عرض الشكوى -->
<div id="complaintModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:16px;max-width:600px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3)">
        <div style="padding:20px 24px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:1.1rem">📢 تفاصيل الشكوى #<span id="modalId"></span></h3>
            <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#94A3B8">✕</button>
        </div>
        <div style="padding:24px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <div style="font-size:.75rem;color:#94A3B8;margin-bottom:2px">الموظف</div>
                    <div style="font-weight:700" id="modalEmployee"></div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94A3B8;margin-bottom:2px">النوع</div>
                    <div id="modalType"></div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94A3B8;margin-bottom:2px">الجهة</div>
                    <div id="modalTarget"></div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#94A3B8;margin-bottom:2px">التاريخ</div>
                    <div id="modalDate"></div>
                </div>
            </div>

            <div style="margin-bottom:16px">
                <div style="font-size:.75rem;color:#94A3B8;margin-bottom:4px">الموضوع</div>
                <div style="font-weight:700;font-size:1rem" id="modalSubject"></div>
            </div>

            <div style="margin-bottom:20px">
                <div style="font-size:.75rem;color:#94A3B8;margin-bottom:4px">التفاصيل</div>
                <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:14px;line-height:1.8;white-space:pre-wrap" id="modalBody"></div>
            </div>

            <!-- المرفقات -->
            <div id="modalAttachments" style="margin-bottom:20px;display:none">
                <div style="font-size:.75rem;color:#94A3B8;margin-bottom:6px">📎 المرفقات</div>
                <div id="modalAttachGrid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px"></div>
            </div>

            <hr style="border:none;border-top:1px solid #E2E8F0;margin:20px 0">

            <div style="margin-bottom:12px">
                <label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:6px">تغيير الحالة</label>
                <select id="modalStatus" class="form-control form-control-sm">
                    <option value="pending">⏳ قيد الانتظار</option>
                    <option value="reviewing">🔍 قيد المراجعة</option>
                    <option value="resolved">✅ تم الحل</option>
                    <option value="rejected">❌ مرفوضة</option>
                </select>
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:.82rem;font-weight:700;display:block;margin-bottom:6px">رد الإدارة (اختياري)</label>
                <textarea id="modalReply" class="form-control" rows="3" placeholder="اكتب رد الإدارة هنا..."></textarea>
            </div>

            <div style="display:flex;gap:10px">
                <button class="btn btn-primary" onclick="saveComplaint()" id="saveBtn" style="flex:2">
                    💾 حفظ التحديث
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteComplaint()" style="flex:1">
                    🗑 حذف
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentComplaintId = 0;
const CSRF = <?= json_encode(generateCsrfToken()) ?>;

const SITE_URL = <?= json_encode(SITE_URL) ?>;
const typeLabels = {person:'👤 شخص', branch:'🏢 فرع', group:'👥 مجموعة', other:'📋 أخرى'};

function viewComplaint(c) {
    currentComplaintId = c.id;
    document.getElementById('modalId').textContent = c.id;
    document.getElementById('modalEmployee').textContent = c.employee_name;
    document.getElementById('modalType').textContent = typeLabels[c.complaint_type] || c.complaint_type;
    document.getElementById('modalTarget').textContent = c.target_name || '—';
    document.getElementById('modalDate').textContent = c.created_at;
    document.getElementById('modalSubject').textContent = c.subject;
    document.getElementById('modalBody').textContent = c.body;
    document.getElementById('modalStatus').value = c.status;
    document.getElementById('modalReply').value = c.admin_reply || '';

    // عرض المرفقات
    const attDiv = document.getElementById('modalAttachments');
    const attGrid = document.getElementById('modalAttachGrid');
    attGrid.innerHTML = '';
    let attachments = [];
    try { attachments = c.attachments ? JSON.parse(c.attachments) : []; } catch(e) {}
    if (attachments.length > 0) {
        attDiv.style.display = 'block';
        attachments.forEach(path => {
            const a = document.createElement('a');
            a.href = SITE_URL + '/storage/uploads/' + path;
            a.target = '_blank';
            a.style.cssText = 'border-radius:8px;overflow:hidden;aspect-ratio:1;display:block;background:#F1F5F9;border:1px solid #E2E8F0';
            const img = document.createElement('img');
            img.src = SITE_URL + '/storage/uploads/' + path;
            img.style.cssText = 'width:100%;height:100%;object-fit:cover';
            img.alt = 'مرفق';
            a.appendChild(img);
            attGrid.appendChild(a);
        });
    } else {
        attDiv.style.display = 'none';
    }

    document.getElementById('complaintModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('complaintModal').style.display = 'none';
}

async function saveComplaint() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.textContent = '⏳ جاري الحفظ...';

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('action', 'update_status');
    fd.append('id', currentComplaintId);
    fd.append('status', document.getElementById('modalStatus').value);
    fd.append('reply', document.getElementById('modalReply').value);

    try {
        const res = await fetch(window.location.pathname, {method:'POST', body:fd});
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'خطأ');
        }
    } catch(e) {
        alert('خطأ في الاتصال');
    } finally {
        btn.disabled = false;
        btn.textContent = '💾 حفظ التحديث';
    }
}

async function deleteComplaint() {
    if (!confirm('هل أنت متأكد من حذف هذه الشكوى؟')) return;

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('action', 'delete');
    fd.append('id', currentComplaintId);

    try {
        const res = await fetch(window.location.pathname, {method:'POST', body:fd});
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'خطأ');
        }
    } catch(e) {
        alert('خطأ في الاتصال');
    }
}

// Close modal on background click
document.getElementById('complaintModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
