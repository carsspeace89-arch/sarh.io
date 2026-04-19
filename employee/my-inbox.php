<?php
// =============================================================
// employee/my-inbox.php — صندوق الوارد للموظف
// مخالفات · خصومات · مكافآت · تحذيرات · إشعارات
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// ── التحقق من التوكن ──
$token = trim($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(403);
    exit('<div style="font-family:Tajawal,sans-serif;text-align:center;padding:60px;color:#666">رابط غير صالح</div>');
}
$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    http_response_code(403);
    exit('<div style="font-family:Tajawal,sans-serif;text-align:center;padding:60px;color:#666">الحساب غير موجود أو مُعطَّل</div>');
}
$empId = (int)$employee['id'];

// ── معالجة تعليم كمقروء (AJAX) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $msgId = (int)($_POST['id'] ?? 0);
        if ($msgId > 0) {
            $stmt = db()->prepare("SELECT id, employee_id, is_read FROM employee_inbox WHERE id = ? AND employee_id = ?");
            $stmt->execute([$msgId, $empId]);
            $msg = $stmt->fetch();
            if ($msg && !$msg['is_read']) {
                db()->prepare("UPDATE employee_inbox SET is_read = 1, read_at = NOW() WHERE id = ?")
                    ->execute([$msgId]);
                db()->prepare("UPDATE employees SET unread_inbox_count = GREATEST(0, unread_inbox_count - 1) WHERE id = ?")
                    ->execute([$empId]);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'mark_all_read') {
        db()->prepare("UPDATE employee_inbox SET is_read = 1, read_at = NOW() WHERE employee_id = ? AND is_read = 0")
            ->execute([$empId]);
        db()->prepare("UPDATE employees SET unread_inbox_count = 0 WHERE id = ?")
            ->execute([$empId]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// ── جلب الرسائل ──
$filterType = $_GET['type'] ?? '';
$validTypes = ['violation','deduction','reward','warning','info'];
$condition  = 'employee_id = ?';
$queryParams = [$empId];
if (in_array($filterType, $validTypes)) {
    $condition .= ' AND msg_type = ?';
    $queryParams[] = $filterType;
}

$messages = db()->prepare("
    SELECT * FROM employee_inbox
    WHERE {$condition}
    ORDER BY is_read ASC, created_at DESC
    LIMIT 200
");
$messages->execute($queryParams);
$messages = $messages->fetchAll();

// ── إحصائيات ──
$statsStmt = db()->prepare("
    SELECT msg_type,
           COUNT(*) AS total,
           SUM(CASE WHEN is_read=0 THEN 1 ELSE 0 END) AS unread
    FROM employee_inbox
    WHERE employee_id = ?
    GROUP BY msg_type
");
$statsStmt->execute([$empId]);
$stats = [];
foreach ($statsStmt->fetchAll() as $s) $stats[$s['msg_type']] = $s;

$unreadTotal = (int)array_sum(array_column($stats, 'unread'));

$profilePhotoUrl = !empty($employee['profile_photo'])
    ? SITE_URL . '/api/serve-file.php?f=' . urlencode($employee['profile_photo']) . '&t=' . urlencode($token)
    : '';
$initials = mb_substr($employee['name'] ?? '?', 0, 1);

$typeConfig = [
    'violation' => ['label'=>'مخالفة',    'icon'=>'🚫','color'=>'#EF4444','bg'=>'#FEF2F2','light'=>'#FEE2E2'],
    'deduction' => ['label'=>'خصم',       'icon'=>'💸','color'=>'#F59E0B','bg'=>'#FFFBEB','light'=>'#FEF3C7'],
    'reward'    => ['label'=>'مكافأة',    'icon'=>'🏆','color'=>'#10B981','bg'=>'#ECFDF5','light'=>'#D1FAE5'],
    'warning'   => ['label'=>'تحذير',     'icon'=>'⚠️','color'=>'#F97316','bg'=>'#FFF7ED','light'=>'#FFEDD5'],
    'info'      => ['label'=>'إشعار',     'icon'=>'ℹ️','color'=>'#3B82F6','bg'=>'#EFF6FF','light'=>'#DBEAFE'],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>صندوق الوارد — <?= htmlspecialchars($employee['name']) ?></title>
<link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/loogo.png">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/tajawal.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
    --primary:#F97316;--primary-d:#C2410C;--primary-l:#FFF7ED;
    --surface:#F8FAFC;--surface2:#F1F5F9;--border:#E2E8F0;
    --text:#1E293B;--text2:#475569;--text3:#94A3B8;
    --red:#EF4444;--amber:#F59E0B;--green:#10B981;--blue:#3B82F6;
    --radius:14px;--shadow:0 1px 6px rgba(0,0,0,.08);
}
body{font-family:'Tajawal',sans-serif;background:var(--surface);color:var(--text);min-height:100vh;padding-bottom:60px}

/* Hero */
.hero{background:linear-gradient(135deg,var(--primary) 0%,var(--primary-d) 100%);padding:28px 20px 80px;text-align:center;position:relative}
.hero-photo{width:80px;height:80px;border-radius:50%;border:3px solid rgba(255,255,255,.5);object-fit:cover;margin-bottom:12px}
.hero-init{width:80px;height:80px;border-radius:50%;border:3px solid rgba(255,255,255,.5);background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 12px}
.hero-name{color:#fff;font-size:1.15rem;font-weight:800}
.hero-sub{color:rgba(255,255,255,.75);font-size:.82rem;margin-top:2px}
.hero-badge{display:inline-block;background:rgba(255,255,255,.2);color:#fff;border-radius:20px;padding:4px 16px;font-size:.8rem;font-weight:700;margin-top:8px}

/* Stats strip */
.stats-strip{background:#fff;border-radius:var(--radius);max-width:540px;margin:-48px auto 20px;padding:16px 12px;box-shadow:var(--shadow);display:flex;justify-content:space-around;gap:6px;flex-wrap:wrap}
.strip-item{text-align:center;flex:1;min-width:70px;cursor:pointer;padding:6px 4px;border-radius:10px;transition:background .15s}
.strip-item:hover{background:var(--surface2)}
.strip-item.active{background:var(--primary-l)}
.strip-num{font-size:1.3rem;font-weight:800}
.strip-lbl{font-size:.68rem;color:var(--text3)}

/* Content */
.wrap{max-width:540px;margin:0 auto;padding:0 14px}

/* Filter tabs */
.filter-tabs{display:flex;gap:6px;overflow-x:auto;padding-bottom:4px;margin-bottom:16px;scrollbar-width:none}
.filter-tabs::-webkit-scrollbar{display:none}
.ftab{flex-shrink:0;padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;font-family:'Tajawal',sans-serif}
.ftab.active{background:var(--primary);color:#fff}
.ftab:not(.active){background:#F1F5F9;color:var(--text2)}

/* Message Card */
.msg-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:12px;overflow:hidden;border:1.5px solid var(--border);transition:all .2s;cursor:pointer}
.msg-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.1);transform:translateY(-1px)}
.msg-card.unread{border-right:4px solid var(--primary)}
.msg-card-head{display:flex;align-items:center;gap:12px;padding:14px 16px}
.msg-type-badge{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.msg-meta{flex:1;min-width:0}
.msg-title{font-weight:700;font-size:.92rem;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.msg-date{font-size:.72rem;color:var(--text3);margin-top:2px}
.msg-unread-dot{width:10px;height:10px;border-radius:50%;background:var(--red);flex-shrink:0}
.msg-body-expand{padding:0 16px 14px;display:none;border-top:1px solid var(--border);margin-top:0;padding-top:14px}
.msg-body-expand.open{display:block}
.msg-body-text{font-size:.88rem;color:var(--text2);line-height:1.7;white-space:pre-wrap}
.msg-amount{margin-top:10px;padding:8px 14px;border-radius:10px;font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:6px}
.msg-ref-date{font-size:.72rem;color:var(--text3);margin-top:8px;display:flex;align-items:center;gap:4px}

/* empty */
.empty-state{text-align:center;padding:60px 20px;color:var(--text3)}
.empty-icon{font-size:3.5rem;margin-bottom:16px}
.empty-title{font-size:1rem;font-weight:700;color:var(--text2);margin-bottom:8px}

/* Bottom bar */
.bottom-bar{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--border);padding:10px 20px;display:flex;justify-content:space-between;gap:10px;z-index:100;max-width:540px;margin:0 auto}
.bottom-btn{flex:1;padding:10px;border-radius:10px;border:none;font-family:'Tajawal',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .15s}
.bottom-btn-primary{background:var(--primary);color:#fff}
.bottom-btn-secondary{background:var(--surface2);color:var(--text2)}

/* Toast */
.toast-wrap{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:#1E293B;color:#fff;padding:10px 20px;border-radius:12px;font-size:.85rem;font-weight:600;opacity:0;animation:toastIn .3s ease forwards}
.toast.success{background:#10B981}
.toast.error{background:#EF4444}
@keyframes toastIn{0%{opacity:0;transform:translateY(-10px)}100%{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<!-- Hero -->
<div class="hero">
    <?php if ($profilePhotoUrl): ?>
        <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" class="hero-photo" alt="">
    <?php else: ?>
        <div class="hero-init"><?= htmlspecialchars($initials) ?></div>
    <?php endif; ?>
    <div class="hero-name"><?= htmlspecialchars($employee['name']) ?></div>
    <div class="hero-sub"><?= htmlspecialchars($employee['job_title'] ?? 'موظف') ?></div>
    <?php if ($unreadTotal > 0): ?>
    <div class="hero-badge">📬 <?= $unreadTotal ?> رسالة جديدة</div>
    <?php else: ?>
    <div class="hero-badge">✅ صندوقك فارغ من الجديد</div>
    <?php endif; ?>
</div>

<!-- Stats Strip -->
<div class="stats-strip">
    <?php foreach ($typeConfig as $type => $cfg):
        $s = $stats[$type] ?? ['total'=>0,'unread'=>0];
    ?>
    <div class="strip-item <?= $filterType===$type?'active':'' ?>"
         onclick="filterByType('<?= $type ?>')">
        <div class="strip-num" style="color:<?= $cfg['color'] ?>"><?= $s['total'] ?></div>
        <div class="strip-lbl"><?= $cfg['icon'] ?> <?= $cfg['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="wrap">

    <!-- Filter tabs -->
    <div class="filter-tabs">
        <button class="ftab <?= $filterType===''?'active':'' ?>"
                onclick="filterByType('')">الكل (<?= count($messages) ?>)</button>
        <?php foreach ($typeConfig as $type => $cfg):
            $s = $stats[$type] ?? ['total'=>0,'unread'=>0];
            if ($s['total'] == 0) continue;
        ?>
        <button class="ftab <?= $filterType===$type?'active':'' ?>"
                onclick="filterByType('<?= $type ?>')">
            <?= $cfg['icon'] ?> <?= $cfg['label'] ?>
            <?php if ($s['unread'] > 0): ?>
            <span style="background:#EF4444;color:#fff;font-size:.6rem;padding:1px 5px;border-radius:8px;margin-right:2px"><?= $s['unread'] ?></span>
            <?php endif; ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- قائمة الرسائل -->
    <?php if (empty($messages)): ?>
    <div class="empty-state">
        <div class="empty-icon">📭</div>
        <div class="empty-title">لا توجد رسائل</div>
        <div style="font-size:.82rem">لم يتم إرسال أي رسائل في هذه الفئة حتى الآن</div>
    </div>
    <?php else: ?>
    <div id="msgList">
    <?php foreach ($messages as $msg):
        $cfg = $typeConfig[$msg['msg_type']] ?? $typeConfig['info'];
        $isUnread = !$msg['is_read'];
        $hasAmount = $msg['amount'] !== null;
        $amountSign = $msg['msg_type'] === 'reward' ? '+' : '-';
        $amountColor = $msg['msg_type'] === 'reward' ? '#10B981' : '#EF4444';
    ?>
    <div class="msg-card <?= $isUnread ? 'unread' : '' ?>"
         id="mc_<?= $msg['id'] ?>"
         onclick="toggleMsg(<?= $msg['id'] ?>, <?= $isUnread ? 'true' : 'false' ?>)">
        <div class="msg-card-head">
            <div class="msg-type-badge" style="background:<?= $cfg['light'] ?>">
                <?= $cfg['icon'] ?>
            </div>
            <div class="msg-meta">
                <div class="msg-title"><?= htmlspecialchars($msg['title']) ?></div>
                <div class="msg-date">
                    <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                    &nbsp;·&nbsp;
                    <span style="color:<?= $cfg['color'] ?>;font-weight:600"><?= $cfg['label'] ?></span>
                </div>
            </div>
            <?php if ($isUnread): ?>
            <div class="msg-unread-dot" id="dot_<?= $msg['id'] ?>"></div>
            <?php endif; ?>
            <svg id="arrow_<?= $msg['id'] ?>" width="16" height="16" viewBox="0 0 24 24" fill="#94A3B8"
                 style="flex-shrink:0;transition:transform .2s">
                <path d="M7 10l5 5 5-5z"/>
            </svg>
        </div>
        <div class="msg-body-expand" id="body_<?= $msg['id'] ?>">
            <div class="msg-body-text"><?= htmlspecialchars($msg['body']) ?></div>
            <?php if ($hasAmount): ?>
            <div class="msg-amount" style="background:<?= $cfg['light'] ?>;color:<?= $amountColor ?>">
                <?= $amountSign ?><?= number_format((float)$msg['amount'], 2) ?> <?= htmlspecialchars($msg['currency'] ?? 'ريال') ?>
            </div>
            <?php endif; ?>
            <?php if ($msg['reference_date']): ?>
            <div class="msg-ref-date">
                📅 التاريخ المرجعي: <?= date('d/m/Y', strtotime($msg['reference_date'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Bottom Bar -->
<div class="bottom-bar" style="left:0;right:0;width:100%">
    <button class="bottom-btn bottom-btn-secondary"
            onclick="window.location.href='<?= SITE_URL ?>/employee/attendance.php?token=<?= urlencode($token) ?>'">
        ← رجوع
    </button>
    <?php if ($unreadTotal > 0): ?>
    <button class="bottom-btn bottom-btn-primary" onclick="markAllRead()">
        ✓ تعليم الكل كمقروء
    </button>
    <?php endif; ?>
</div>

<!-- Toast Container -->
<div class="toast-wrap" id="toastWrap"></div>

<script>
const TOKEN = <?= json_encode($token) ?>;

function filterByType(type) {
    const url = new URL(window.location.href);
    if (type) url.searchParams.set('type', type);
    else url.searchParams.delete('type');
    window.location.href = url.toString();
}

function toggleMsg(id, isUnread) {
    const body  = document.getElementById('body_' + id);
    const arrow = document.getElementById('arrow_' + id);
    const isOpen = body.classList.contains('open');

    body.classList.toggle('open', !isOpen);
    if (arrow) arrow.style.transform = isOpen ? '' : 'rotate(180deg)';

    if (isUnread && !isOpen) {
        markOneRead(id);
    }
}

async function markOneRead(id) {
    const fd = new FormData();
    fd.append('action', 'mark_read');
    fd.append('id', id);
    try {
        await fetch(window.location.pathname + '?token=' + encodeURIComponent(TOKEN), {method:'POST', body:fd});
        const card = document.getElementById('mc_' + id);
        if (card) card.classList.remove('unread');
        const dot = document.getElementById('dot_' + id);
        if (dot) dot.remove();
    } catch(e) {}
}

async function markAllRead() {
    const fd = new FormData();
    fd.append('action', 'mark_all_read');
    try {
        await fetch(window.location.pathname + '?token=' + encodeURIComponent(TOKEN), {method:'POST', body:fd});
        showToast('تم تعليم جميع الرسائل كمقروءة', 'success');
        document.querySelectorAll('.msg-card.unread').forEach(c => c.classList.remove('unread'));
        document.querySelectorAll('.msg-unread-dot').forEach(d => d.remove());
        document.querySelector('.bottom-btn-primary')?.remove();
        setTimeout(() => window.location.reload(), 800);
    } catch(e) {
        showToast('حدث خطأ، حاول مجدداً', 'error');
    }
}

function showToast(msg, type) {
    const wrap = document.getElementById('toastWrap');
    const el = document.createElement('div');
    el.className = 'toast ' + (type || '');
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}
</script>
</body>
</html>
