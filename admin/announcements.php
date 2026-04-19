<?php
// =============================================================
// admin/announcements.php - إدارة الإعلانات والأخبار
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'إدارة الإعلانات';
$activePage = 'announcements';
$message    = '';
$msgType    = '';

// =================== إجراءات POST ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'طلب غير صالح';
        $msgType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        // إضافة إعلان
        if ($action === 'add') {
            $title = sanitize($_POST['title'] ?? '');
            $content = sanitize($_POST['content'] ?? '');
            $type = sanitize($_POST['type'] ?? 'announcement');
            $priority = sanitize($_POST['priority'] ?? 'normal');
            $icon = sanitize($_POST['icon'] ?? '📢');
            $color = sanitize($_POST['color'] ?? '#F97316');
            $showInTicker = (int)($_POST['show_in_ticker'] ?? 1);
            $targetAudience = sanitize($_POST['target_audience'] ?? 'all');
            $branchId = (int)($_POST['branch_id'] ?? 0) ?: null;

            if ($title && $content) {
                try {
                    $stmt = db()->prepare("
                        INSERT INTO announcements 
                        (title, content, type, priority, icon, color, show_in_ticker, target_audience, branch_id, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title, $content, $type, $priority, $icon, $color, 
                        $showInTicker, $targetAudience, $branchId,
                        $_SESSION['admin_id'] ?? null
                    ]);
                    auditLog('add_announcement', "إضافة إعلان: {$title}");

                    // إرسال Push Notification لجميع الموظفين المستهدفين
                    try {
                        $empQuery = "SELECT id FROM employees WHERE is_active = 1";
                        $empParams = [];
                        if ($branchId) {
                            $empQuery .= " AND branch_id = ?";
                            $empParams[] = $branchId;
                        }
                        $empIds = db()->prepare($empQuery);
                        $empIds->execute($empParams);
                        $targetIds = $empIds->fetchAll(PDO::FETCH_COLUMN);
                        if (!empty($targetIds)) {
                            sendPushNotification($targetIds, $icon . ' ' . $title, mb_substr($content, 0, 120), [
                                'tag' => 'announcement-' . time(),
                                'url' => '/employee/attendance.php'
                            ]);
                        }
                    } catch (Exception $e) { /* Push is best-effort */ }

                    $message = "تم إضافة الإعلان بنجاح";
                    $msgType = 'success';
                } catch (PDOException $e) {
                    $message = 'خطأ في الإضافة: ' . $e->getMessage();
                    $msgType = 'error';
                }
            } else {
                $message = 'أدخل العنوان والمحتوى';
                $msgType = 'error';
            }
        }

        // تعديل إعلان
        if ($action === 'edit') {
            $id = (int)($_POST['announcement_id'] ?? 0);
            $title = sanitize($_POST['title'] ?? '');
            $content = sanitize($_POST['content'] ?? '');
            $type = sanitize($_POST['type'] ?? 'announcement');
            $priority = sanitize($_POST['priority'] ?? 'normal');
            $icon = sanitize($_POST['icon'] ?? '📢');
            $color = sanitize($_POST['color'] ?? '#F97316');
            $showInTicker = (int)($_POST['show_in_ticker'] ?? 1);
            $isActive = (int)($_POST['is_active'] ?? 1);
            $targetAudience = sanitize($_POST['target_audience'] ?? 'all');
            $branchId = (int)($_POST['branch_id'] ?? 0) ?: null;

            if ($id && $title && $content) {
                $stmt = db()->prepare("
                    UPDATE announcements 
                    SET title=?, content=?, type=?, priority=?, icon=?, color=?, 
                        show_in_ticker=?, is_active=?, target_audience=?, branch_id=?
                    WHERE id=?
                ");
                $stmt->execute([
                    $title, $content, $type, $priority, $icon, $color,
                    $showInTicker, $isActive, $targetAudience, $branchId, $id
                ]);
                auditLog('edit_announcement', "تعديل إعلان: {$title}", $id);
                $message = "تم تحديث الإعلان بنجاح";
                $msgType = 'success';
            }
        }

        // حذف إعلان
        if ($action === 'delete') {
            $id = (int)($_POST['announcement_id'] ?? 0);
            if ($id) {
                db()->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
                auditLog('delete_announcement', "حذف إعلان ID={$id}", $id);
                $message = "تم حذف الإعلان";
                $msgType = 'success';
            }
        }

        // تبديل الحالة النشطة
        if ($action === 'toggle') {
            $id = (int)($_POST['announcement_id'] ?? 0);
            if ($id) {
                db()->prepare("UPDATE announcements SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
                $message = "تم تغيير حالة الإعلان";
                $msgType = 'success';
            }
        }
    }
    header('Location: announcements.php?msg=' . urlencode($message) . '&t=' . $msgType);
    exit;
}

// عرض الرسالة من redirect
if (!empty($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $msgType = $_GET['t'] ?? 'success';
}

// =================== جلب الإعلانات ===================
$search = trim($_GET['search'] ?? '');
$filterType = trim($_GET['type'] ?? '');
$filterPriority = trim($_GET['priority'] ?? '');

$whereClause = '';
$params = [];
$conditions = [];
if ($search) {
    $conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
if ($filterType) {
    $conditions[] = "type = ?";
    $params[] = $filterType;
}
if ($filterPriority) {
    $conditions[] = "priority = ?";
    $params[] = $filterPriority;
}
if ($conditions) {
    $whereClause = "WHERE " . implode(' AND ', $conditions);
}

$totalStmt = db()->prepare("SELECT COUNT(*) FROM announcements $whereClause");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$stmt = db()->prepare("
    SELECT a.*, b.name AS branch_name,
           CASE 
               WHEN a.is_active = 1 THEN 'active'
               ELSE 'inactive'
           END AS status
    FROM announcements a
    LEFT JOIN branches b ON a.branch_id = b.id
    $whereClause
    ORDER BY 
        FIELD(priority, 'urgent', 'high', 'normal', 'low'),
        created_at DESC
");
$stmt->execute($params);
$announcements = $stmt->fetchAll();

// جلب الفروع للقوائم المنسدلة
$allBranches = db()->query("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name")->fetchAll();

$csrf = generateCsrfToken();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
.announcement-card {
    border: 1px solid var(--border, #E2E8F0);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px;
    background: var(--card-bg, #fff);
    transition: all 0.2s;
    border-left: 4px solid;
}
.announcement-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.announcement-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}
.announcement-icon {
    font-size: 1.8rem;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
    flex-shrink: 0;
}
.announcement-info {
    flex: 1;
    min-width: 0;
}
.announcement-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.announcement-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 0.77rem;
    color: var(--text-secondary);
}
.announcement-content {
    font-size: 0.88rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 12px 0;
    padding: 10px;
    background: var(--bg-light, #F8FAFC);
    border-radius: 8px;
}
.announcement-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.badge-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge-urgent { background: #FEE2E2; color: #DC2626; }
.badge-high { background: #FED7AA; color: #EA580C; }
.badge-normal { background: #DBEAFE; color: #2563EB; }
.badge-low { background: #E0E7FF; color: #6366F1; }
.badge-active { background: #D1FAE5; color: #059669; }
.badge-inactive { background: #F3F4F6; color: #6B7280; }
.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-edit {
    background: #3B82F6;
    color: #fff;
}
.btn-edit:hover {
    background: #2563EB;
}
.btn-delete {
    background: #EF4444;
    color: #fff;
}
.btn-delete:hover {
    background: #DC2626;
}
.btn-toggle {
    background: #10B981;
    color: #fff;
}
.btn-toggle:hover {
    background: #059669;
}
.icon-selector {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
    gap: 6px;
    max-height: 200px;
    overflow-y: auto;
    padding: 8px;
    background: var(--bg-light, #F8FAFC);
    border-radius: 8px;
}
.icon-option {
    font-size: 1.5rem;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.icon-option:hover, .icon-option.selected {
    border-color: var(--primary);
    background: rgba(249, 115, 22, 0.1);
    transform: scale(1.1);
}
.color-picker-list {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.color-option {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.2s;
}
.color-option:hover, .color-option.selected {
    border-color: var(--text-primary);
    transform: scale(1.15);
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: linear-gradient(135deg, var(--stat-color, #3B82F6), var(--stat-color-dark, #2563EB));
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.stat-value {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
}
</style>

<?php if ($message): ?>
    <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- إحصائيات -->
<div class="stats-grid">
    <div class="stat-card" style="--stat-color:#10B981;--stat-color-dark:#059669">
        <div class="stat-value" id="statActive">
            <?= count(array_filter($announcements, fn($a) => $a['status'] === 'active')) ?>
        </div>
        <div class="stat-label">📢 إعلانات نشطة</div>
    </div>
    <div class="stat-card" style="--stat-color:#EF4444;--stat-color-dark:#DC2626">
        <div class="stat-value" id="statUrgent">
            <?= count(array_filter($announcements, fn($a) => $a['priority'] === 'urgent')) ?>
        </div>
        <div class="stat-label">🚨 إعلانات عاجلة</div>
    </div>
    <div class="stat-card" style="--stat-color:#3B82F6;--stat-color-dark:#2563EB">
        <div class="stat-value" id="statTotal"><?= $total ?></div>
        <div class="stat-label">📊 إجمالي الإعلانات</div>
    </div>
</div>

<!-- البحث والتصفية -->
<div class="card" style="margin-bottom:18px;padding:14px">
    <div class="top-actions" style="display:flex;gap:12px;flex-wrap:wrap;justify-content:space-between;align-items:flex-end">
        <form method="GET" class="filter-bar" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;flex:1;min-width:200px">
            <input class="form-control" name="search" placeholder="بحث في العنوان أو المحتوى..."
                value="<?= htmlspecialchars($search) ?>" style="max-width:240px">
            <select class="form-control" name="type" style="max-width:150px">
                <option value="">— كل الأنواع —</option>
                <option value="news" <?= $filterType === 'news' ? 'selected' : '' ?>>خبر</option>
                <option value="announcement" <?= $filterType === 'announcement' ? 'selected' : '' ?>>إعلان</option>
                <option value="circular" <?= $filterType === 'circular' ? 'selected' : '' ?>>تعميم</option>
                <option value="alert" <?= $filterType === 'alert' ? 'selected' : '' ?>>تنبيه</option>
                <option value="event" <?= $filterType === 'event' ? 'selected' : '' ?>>حدث</option>
            </select>
            <select class="form-control" name="priority" style="max-width:150px">
                <option value="">— كل الأولويات —</option>
                <option value="urgent" <?= $filterPriority === 'urgent' ? 'selected' : '' ?>>عاجل</option>
                <option value="high" <?= $filterPriority === 'high' ? 'selected' : '' ?>>مرتفع</option>
                <option value="normal" <?= $filterPriority === 'normal' ? 'selected' : '' ?>>عادي</option>
                <option value="low" <?= $filterPriority === 'low' ? 'selected' : '' ?>>منخفض</option>
            </select>
            <button type="submit" class="btn btn-secondary">بحث</button>
            <?php if ($search || $filterType || $filterPriority): ?>
                <a href="announcements.php" class="btn btn-secondary">إلغاء</a>
            <?php endif; ?>
        </form>
        <button class="btn btn-primary" onclick="openModal('addModal')">+ إضافة إعلان</button>
    </div>
</div>

<!-- قائمة الإعلانات -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><span class="card-title-bar"></span> قائمة الإعلانات (<?= $total ?>)</span>
    </div>
    <div style="padding:16px">
        <?php if (empty($announcements)): ?>
            <div style="text-align:center;padding:40px 20px;color:var(--text-secondary)">
                <div style="font-size:3rem;margin-bottom:12px">📭</div>
                <div style="font-size:1.1rem;font-weight:600">لا توجد إعلانات</div>
                <div style="font-size:0.85rem;margin-top:6px">ابدأ بإضافة إعلان جديد</div>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
                <div class="announcement-card" style="border-left-color:<?= htmlspecialchars($ann['color']) ?>">
                    <div class="announcement-header">
                        <div class="announcement-icon" style="background:<?= htmlspecialchars($ann['color']) ?>20">
                            <?= htmlspecialchars($ann['icon']) ?>
                        </div>
                        <div class="announcement-info">
                            <div class="announcement-title"><?= htmlspecialchars($ann['title']) ?></div>
                            <div class="announcement-meta">
                                <span class="badge-pill badge-<?= $ann['priority'] ?>"><?= 
                                    ['urgent' => '🚨 عاجل', 'high' => '⚠️ مرتفع', 'normal' => 'ℹ️ عادي', 'low' => '📌 منخفض'][$ann['priority']] ?? 'عادي' 
                                ?></span>
                                <span class="badge-pill badge-<?= $ann['status'] ?>"><?= 
                                    ['active' => '✅ نشط', 'inactive' => '⏸️ معطل'][$ann['status']] ?? 'نشط'
                                ?></span>
                                <?php if ($ann['show_in_ticker']): ?>
                                    <span class="badge-pill" style="background:#F0F9FF;color:#0EA5E9">📰 في الشريط</span>
                                <?php endif; ?>
                                <?php if ($ann['branch_name']): ?>
                                    <span class="badge-pill" style="background:#FEF3C7;color:#D97706">🏢 <?= htmlspecialchars($ann['branch_name']) ?></span>
                                <?php endif; ?>
                                <span style="flex:1"></span>
                                <span style="font-size:0.72rem">🕒 <?= date('Y-m-d H:i', strtotime($ann['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-content">
                        <?= nl2br(htmlspecialchars($ann['content'])) ?>
                    </div>
                    <div class="announcement-actions">
                        <button class="btn-sm btn-edit" onclick='openEditModal(<?= json_encode($ann, JSON_UNESCAPED_UNICODE) ?>)'>
                            ✏️ تعديل
                        </button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('تبديل حالة الإعلان؟')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                            <button type="submit" class="btn-sm btn-toggle">
                                <?= $ann['is_active'] ? '⏸️ تعطيل' : '▶️ تفعيل' ?>
                            </button>
                        </form>
                        <form method="POST" style="display:inline" onsubmit="return confirm('حذف الإعلان نهائياً؟')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                            <button type="submit" class="btn-sm btn-delete">🗑️ حذف</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- مودال الإضافة -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width:600px">
        <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        <h2 style="margin:0 0 20px 0;font-size:1.3rem;color:var(--text-primary)">📢 إضافة إعلان جديد</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="icon" id="add_icon" value="📢">
            <input type="hidden" name="color" id="add_color" value="#F97316">
            
            <div class="form-group">
                <label>عنوان الإعلان *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>محتوى الإعلان *</label>
                <textarea name="content" class="form-control" rows="4" required></textarea>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>نوع الإعلان</label>
                    <select name="type" class="form-control">
                        <option value="announcement">إعلان</option>
                        <option value="news">خبر</option>
                        <option value="circular">تعميم</option>
                        <option value="alert">تنبيه</option>
                        <option value="event">حدث</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>الأولوية</label>
                    <select name="priority" class="form-control">
                        <option value="normal">عادي</option>
                        <option value="low">منخفض</option>
                        <option value="high">مرتفع</option>
                        <option value="urgent">عاجل</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>الأيقونة</label>
                <div id="add_icon_display" style="cursor:pointer;padding:10px;background:var(--bg-light);border-radius:8px;text-align:center;font-size:2rem" onclick="toggleIconSelector('add')">
                    📢
                </div>
                <div id="add_iconSelector" class="icon-selector" style="display:none;margin-top:8px"></div>
            </div>
            
            <div class="form-group">
                <label>اللون</label>
                <div class="color-picker-list" id="add_colorPicker"></div>
            </div>
            
            <div class="form-group">
                <label>الجمهور المستهدف</label>
                <select name="target_audience" class="form-control" onchange="toggleBranchSelect(this, 'add_branch')">
                    <option value="all">الجميع</option>
                    <option value="employees">الموظفين فقط</option>
                    <option value="specific_branch">فرع محدد</option>
                </select>
            </div>
            
            <div class="form-group" id="add_branch_group" style="display:none">
                <label>اختر الفرع</label>
                <select name="branch_id" id="add_branch" class="form-control">
                    <option value="">-- اختر الفرع --</option>
                    <?php foreach ($allBranches as $branch): ?>
                        <option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="show_in_ticker" value="1" checked>
                    <span>عرض في الشريط المتحرك</span>
                </label>
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">إلغاء</button>
                <button type="submit" class="btn btn-primary">✅ إضافة</button>
            </div>
        </form>
    </div>
</div>

<!-- مودال التعديل -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width:600px">
        <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        <h2 style="margin:0 0 20px 0;font-size:1.3rem;color:var(--text-primary)">✏️ تعديل الإعلان</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="announcement_id" id="edit_id">
            <input type="hidden" name="icon" id="edit_icon" value="📢">
            <input type="hidden" name="color" id="edit_color" value="#F97316">
            
            <div class="form-group">
                <label>عنوان الإعلان *</label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>محتوى الإعلان *</label>
                <textarea name="content" id="edit_content" class="form-control" rows="4" required></textarea>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>نوع الإعلان</label>
                    <select name="type" id="edit_type" class="form-control">
                        <option value="announcement">إعلان</option>
                        <option value="news">خبر</option>
                        <option value="circular">تعميم</option>
                        <option value="alert">تنبيه</option>
                        <option value="event">حدث</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>الأولوية</label>
                    <select name="priority" id="edit_priority" class="form-control">
                        <option value="normal">عادي</option>
                        <option value="low">منخفض</option>
                        <option value="high">مرتفع</option>
                        <option value="urgent">عاجل</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>الأيقونة</label>
                <div id="edit_icon_display" style="cursor:pointer;padding:10px;background:var(--bg-light);border-radius:8px;text-align:center;font-size:2rem" onclick="toggleIconSelector('edit')">
                    📢
                </div>
                <div id="edit_iconSelector" class="icon-selector" style="display:none;margin-top:8px"></div>
            </div>
            
            <div class="form-group">
                <label>اللون</label>
                <div class="color-picker-list" id="edit_colorPicker"></div>
            </div>
            
            <div class="form-group">
                <label>الجمهور المستهدف</label>
                <select name="target_audience" id="edit_target_audience" class="form-control" onchange="toggleBranchSelect(this, 'edit_branch')">
                    <option value="all">الجميع</option>
                    <option value="employees">الموظفين فقط</option>
                    <option value="specific_branch">فرع محدد</option>
                </select>
            </div>
            
            <div class="form-group" id="edit_branch_group" style="display:none">
                <label>اختر الفرع</label>
                <select name="branch_id" id="edit_branch" class="form-control">
                    <option value="">-- اختر الفرع --</option>
                    <?php foreach ($allBranches as $branch): ?>
                        <option value="<?= $branch['id'] ?>"><?= htmlspecialchars($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="show_in_ticker" id="edit_show_in_ticker" value="1">
                    <span>عرض في الشريط المتحرك</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <span>نشط</span>
                </label>
            </div>
            
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
                <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>

<script>
// أيقونات متاحة
const availableIcons = ['📢', '📰', '📣', '🎯', '⚠️', '🚨', '📌', 'ℹ️', '✅', '❗', '💡', '🎉', '🏆', '📅', '🔔', '⭐', '📝', '🎊', '🎁', '📚', '🔧', '⚙️', '📊', '📈', '💼', '🏢', '🎓', '🏥', '🚗'];

// ألوان متاحة
const availableColors = ['#F97316', '#EF4444', '#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#EC4899', '#06B6D4', '#14B8A6', '#84CC16', '#6366F1', '#EAB308'];

// تهيئة محددات الأيقونة واللون
function initializePickers(prefix) {
    const iconSelector = document.getElementById(`${prefix}_iconSelector`);
    const colorPicker = document.getElementById(`${prefix}_colorPicker`);
    
    // إنشاء محدد الأيقونات
    iconSelector.innerHTML = availableIcons.map(icon => 
        `<div class="icon-option" onclick="selectIcon('${prefix}', '${icon}')">${icon}</div>`
    ).join('');
    
    // إنشاء محدد الألوان
    colorPicker.innerHTML = availableColors.map(color => 
        `<div class="color-option" style="background:${color}" onclick="selectColor('${prefix}', '${color}')"></div>`
    ).join('');
}

function toggleIconSelector(prefix) {
    const selector = document.getElementById(`${prefix}_iconSelector`);
    selector.style.display = selector.style.display === 'none' ? 'grid' : 'none';
}

function selectIcon(prefix, icon) {
    document.getElementById(`${prefix}_icon`).value = icon;
    document.getElementById(`${prefix}_icon_display`).textContent = icon;
    document.getElementById(`${prefix}_iconSelector`).style.display = 'none';
}

function selectColor(prefix, color) {
    document.getElementById(`${prefix}_color`).value = color;
    // تمييز اللون المختار
    document.querySelectorAll(`#${prefix}_colorPicker .color-option`).forEach(el => el.classList.remove('selected'));
    event.target.classList.add('selected');
}

function toggleBranchSelect(select, branchId) {
    const branchGroup = document.getElementById(branchId + '_group');
    branchGroup.style.display = select.value === 'specific_branch' ? 'block' : 'none';
}

function openEditModal(announcement) {
    document.getElementById('edit_id').value = announcement.id;
    document.getElementById('edit_title').value = announcement.title;
    document.getElementById('edit_content').value = announcement.content;
    document.getElementById('edit_type').value = announcement.type;
    document.getElementById('edit_priority').value = announcement.priority;
    document.getElementById('edit_icon').value = announcement.icon;
    document.getElementById('edit_color').value = announcement.color;
    document.getElementById('edit_icon_display').textContent = announcement.icon;
    document.getElementById('edit_show_in_ticker').checked = announcement.show_in_ticker == 1;
    document.getElementById('edit_is_active').checked = announcement.is_active == 1;
    document.getElementById('edit_target_audience').value = announcement.target_audience;
    document.getElementById('edit_branch').value = announcement.branch_id || '';
    document.getElementById('edit_branch_group').style.display = announcement.target_audience === 'specific_branch' ? 'block' : 'none';
    
    // تمييز اللون المختار
    document.querySelectorAll('#edit_colorPicker .color-option').forEach(el => {
        el.classList.toggle('selected', el.style.background === announcement.color);
    });
    
    openModal('editModal');
}

// تهيئة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initializePickers('add');
    initializePickers('edit');
});
</script>
