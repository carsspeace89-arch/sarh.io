<?php
// =============================================================
// employee/complaints.php — صفحة الشكاوى للموظف
// رفع شكوى على شخص أو فرع أو مجموعة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

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

$profilePhotoUrl = !empty($employee['profile_photo'])
    ? SITE_URL . '/api/serve-file.php?f=' . urlencode($employee['profile_photo']) . '&t=' . urlencode($token)
    : '';
$initials = mb_substr($employee['name'] ?? '?', 0, 1);

// جلب الشكاوى السابقة
try {
    $stmt = db()->prepare("SELECT id, complaint_type, target_name, subject, body, status, admin_reply, attachments, created_at FROM complaints WHERE employee_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([(int)$employee['id']]);
    $myComplaints = $stmt->fetchAll();
} catch (Exception $e) {
    $myComplaints = [];
}

$statusConfig = [
    'pending'   => ['label' => 'قيد الانتظار', 'color' => '#F59E0B', 'bg' => '#FFFBEB', 'icon' => '⏳'],
    'reviewing' => ['label' => 'قيد المراجعة', 'color' => '#3B82F6', 'bg' => '#EFF6FF', 'icon' => '🔍'],
    'resolved'  => ['label' => 'تم الحل',      'color' => '#10B981', 'bg' => '#ECFDF5', 'icon' => '✅'],
    'rejected'  => ['label' => 'مرفوضة',       'color' => '#EF4444', 'bg' => '#FEF2F2', 'icon' => '❌'],
];
$typeLabels = [
    'person' => '👤 شخص',
    'branch' => '🏢 فرع',
    'group'  => '👥 مجموعة',
    'other'  => '📋 أخرى',
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>رفع شكوى — <?= htmlspecialchars($employee['name']) ?></title>
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
body{font-family:'Tajawal',sans-serif;background:var(--surface);color:var(--text);min-height:100vh;padding-bottom:100px}

.hero{background:linear-gradient(135deg,#DC2626 0%,#991B1B 100%);padding:28px 20px 70px;text-align:center}
.hero-photo{width:70px;height:70px;border-radius:50%;border:3px solid rgba(255,255,255,.5);object-fit:cover;margin-bottom:10px}
.hero-init{width:70px;height:70px;border-radius:50%;border:3px solid rgba(255,255,255,.5);background:rgba(255,255,255,.2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;margin:0 auto 10px}
.hero-name{color:#fff;font-size:1.1rem;font-weight:800}
.hero-sub{color:rgba(255,255,255,.75);font-size:.82rem;margin-top:2px}
.hero-title{color:#fff;font-size:1.3rem;font-weight:900;margin-top:10px}

.wrap{max-width:540px;margin:-40px auto 0;padding:0 14px}

/* Tabs */
.tab-bar{display:flex;gap:0;background:#fff;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);margin-bottom:20px}
.tab-btn{flex:1;padding:14px 10px;border:none;background:none;font-family:'Tajawal',sans-serif;font-size:.88rem;font-weight:700;cursor:pointer;color:var(--text3);transition:all .2s;border-bottom:3px solid transparent}
.tab-btn.active{color:#DC2626;border-bottom-color:#DC2626;background:#FEF2F2}

.tab-content{display:none}
.tab-content.active{display:block}

/* Form */
.form-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:20px 18px;margin-bottom:16px}
.form-title{font-size:1rem;font-weight:800;margin-bottom:16px;color:var(--text);display:flex;align-items:center;gap:8px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:.82rem;font-weight:700;color:var(--text2);margin-bottom:6px}
.form-select,.form-input,.form-textarea{width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'Tajawal',sans-serif;font-size:.9rem;color:var(--text);background:#fff;transition:border-color .2s;-webkit-appearance:none}
.form-select:focus,.form-input:focus,.form-textarea:focus{outline:none;border-color:#DC2626;box-shadow:0 0 0 3px rgba(220,38,38,.1)}
.form-textarea{min-height:120px;resize:vertical;line-height:1.7}

/* Type selector */
.type-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.type-option{padding:14px 12px;border:2px solid var(--border);border-radius:12px;text-align:center;cursor:pointer;transition:all .2s;background:#fff}
.type-option:hover{border-color:#DC2626;background:#FEF2F2}
.type-option.selected{border-color:#DC2626;background:#FEF2F2}
.type-option-icon{font-size:1.5rem;margin-bottom:4px}
.type-option-label{font-size:.82rem;font-weight:700;color:var(--text)}

/* Submit */
.submit-btn{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,#DC2626,#991B1B);color:#fff;font-family:'Tajawal',sans-serif;font-size:1rem;font-weight:800;cursor:pointer;transition:all .2s}
.submit-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(220,38,38,.3)}
.submit-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* Camera / Attachments */
.attach-section{margin-bottom:16px}
.camera-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;border:2px dashed #DC2626;border-radius:12px;background:#FEF2F2;color:#DC2626;font-family:'Tajawal',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .2s}
.camera-btn:hover{background:#FEE2E2;border-style:solid}
.camera-btn svg{width:24px;height:24px}
.previews{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:10px}
.preview-item{position:relative;border-radius:10px;overflow:hidden;aspect-ratio:1;background:#F1F5F9}
.preview-item img{width:100%;height:100%;object-fit:cover}
.preview-remove{position:absolute;top:4px;left:4px;width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,.9);color:#fff;border:none;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1}
.attach-count{font-size:.75rem;color:var(--text3);margin-top:6px;text-align:center}

/* Complaint cards */
.complaint-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:12px;overflow:hidden;border:1.5px solid var(--border)}
.complaint-head{display:flex;align-items:center;gap:12px;padding:14px 16px;cursor:pointer}
.complaint-status{padding:4px 10px;border-radius:8px;font-size:.72rem;font-weight:700;flex-shrink:0}
.complaint-meta{flex:1;min-width:0}
.complaint-subject{font-weight:700;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.complaint-date{font-size:.72rem;color:var(--text3);margin-top:2px}
.complaint-body{padding:0 16px 14px;display:none;border-top:1px solid var(--border);padding-top:14px}
.complaint-body.open{display:block}
.complaint-detail{font-size:.85rem;color:var(--text2);line-height:1.7;white-space:pre-wrap}
.complaint-reply{margin-top:12px;padding:12px;border-radius:10px;background:#EFF6FF;border:1px solid #BFDBFE}
.complaint-reply-title{font-size:.78rem;font-weight:700;color:#1D4ED8;margin-bottom:4px}
.complaint-reply-text{font-size:.85rem;color:var(--text);line-height:1.6}
.complaint-type-badge{font-size:.72rem;color:var(--text3);margin-top:2px}

.empty-state{text-align:center;padding:50px 20px;color:var(--text3)}
.empty-icon{font-size:3rem;margin-bottom:12px}
.empty-title{font-size:.95rem;font-weight:700;color:var(--text2);margin-bottom:6px}

/* Bottom bar - always visible */
.bottom-bar{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--border);padding:10px 20px;display:flex;justify-content:space-between;gap:10px;z-index:100;max-width:540px;margin:0 auto}
.bottom-btn{flex:1;padding:12px;border-radius:10px;border:none;font-family:'Tajawal',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;transition:all .15s;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;gap:6px}
.bottom-btn-back{background:var(--surface2);color:var(--text2)}
.bottom-btn-new{background:linear-gradient(135deg,#DC2626,#991B1B);color:#fff}

/* Toast */
.toast-wrap{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:#1E293B;color:#fff;padding:10px 20px;border-radius:12px;font-size:.85rem;font-weight:600;opacity:0;animation:toastIn .3s ease forwards}
.toast.success{background:#10B981}
.toast.error{background:#EF4444}
@keyframes toastIn{0%{opacity:0;transform:translateY(-10px)}100%{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<div class="hero">
    <?php if ($profilePhotoUrl): ?>
        <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" class="hero-photo" alt="">
    <?php else: ?>
        <div class="hero-init"><?= htmlspecialchars($initials) ?></div>
    <?php endif; ?>
    <div class="hero-name"><?= htmlspecialchars($employee['name']) ?></div>
    <div class="hero-sub"><?= htmlspecialchars($employee['job_title'] ?? 'موظف') ?></div>
    <div class="hero-title">📢 رفع شكوى للإدارة</div>
</div>

<div class="wrap">

    <!-- Tab Bar -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('new')">✏️ شكوى جديدة</button>
        <button class="tab-btn" onclick="switchTab('history')">📋 شكاواي (<?= count($myComplaints) ?>)</button>
    </div>

    <!-- Tab: New Complaint -->
    <div class="tab-content active" id="tab-new">
        <div class="form-card">
            <div class="form-title">📝 نموذج الشكوى</div>

            <div class="form-group">
                <div class="form-label">نوع الشكوى *</div>
                <div class="type-grid">
                    <div class="type-option" data-type="person" onclick="selectType(this)">
                        <div class="type-option-icon">👤</div>
                        <div class="type-option-label">شخص</div>
                    </div>
                    <div class="type-option" data-type="branch" onclick="selectType(this)">
                        <div class="type-option-icon">🏢</div>
                        <div class="type-option-label">فرع</div>
                    </div>
                    <div class="type-option" data-type="group" onclick="selectType(this)">
                        <div class="type-option-icon">👥</div>
                        <div class="type-option-label">مجموعة</div>
                    </div>
                    <div class="type-option" data-type="other" onclick="selectType(this)">
                        <div class="type-option-icon">📋</div>
                        <div class="type-option-label">أخرى</div>
                    </div>
                </div>
            </div>

            <div class="form-group" id="targetGroup" style="display:none">
                <label class="form-label" id="targetLabel">الجهة المعنية</label>
                <input type="text" class="form-input" id="targetName" placeholder="اكتب الاسم هنا..." maxlength="255">
            </div>

            <div class="form-group">
                <label class="form-label">عنوان الشكوى *</label>
                <input type="text" class="form-input" id="subject" placeholder="عنوان مختصر للشكوى..." maxlength="500">
            </div>

            <div class="form-group">
                <label class="form-label">تفاصيل الشكوى *</label>
                <textarea class="form-textarea" id="body" placeholder="اشرح شكواك بالتفصيل...&#10;كلما كانت التفاصيل أوضح، كان الحل أسرع"></textarea>
            </div>

            <!-- التقاط صور من الكاميرا -->
            <div class="form-group attach-section">
                <label class="form-label">📸 إرفاق صور (من الكاميرا فقط - حد أقصى 5)</label>
                <label class="camera-btn" id="cameraBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    التقط صورة بالكاميرا
                    <input type="file" accept="image/jpeg,image/png,image/webp" capture="environment" id="cameraInput" style="display:none" multiple>
                </label>
                <div class="previews" id="previews"></div>
                <div class="attach-count" id="attachCount"></div>
            </div>

            <button class="submit-btn" id="submitBtn" onclick="submitComplaint()">
                📤 إرسال الشكوى
            </button>
        </div>
    </div>

    <!-- Tab: History -->
    <div class="tab-content" id="tab-history">
        <?php if (empty($myComplaints)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <div class="empty-title">لا توجد شكاوى سابقة</div>
            <div style="font-size:.82rem">لم تقم بتقديم أي شكوى حتى الآن</div>
        </div>
        <?php else: ?>
        <?php foreach ($myComplaints as $c):
            $sc = $statusConfig[$c['status']] ?? $statusConfig['pending'];
            $tl = $typeLabels[$c['complaint_type']] ?? $typeLabels['other'];
        ?>
        <div class="complaint-card" onclick="toggleComplaint(<?= $c['id'] ?>)">
            <div class="complaint-head">
                <span class="complaint-status" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
                    <?= $sc['icon'] ?> <?= $sc['label'] ?>
                </span>
                <div class="complaint-meta">
                    <div class="complaint-subject"><?= htmlspecialchars($c['subject']) ?></div>
                    <div class="complaint-date"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></div>
                    <div class="complaint-type-badge"><?= $tl ?><?= $c['target_name'] ? ' — ' . htmlspecialchars($c['target_name']) : '' ?></div>
                </div>
            </div>
            <div class="complaint-body" id="cbody_<?= $c['id'] ?>">
                <div class="complaint-detail"><?= htmlspecialchars($c['body']) ?></div>
                <?php
                $attachments = !empty($c['attachments']) ? json_decode($c['attachments'], true) : [];
                if (!empty($attachments)): ?>
                <div style="margin-top:12px">
                    <div style="font-size:.78rem;font-weight:700;color:var(--text3);margin-bottom:6px">📎 المرفقات (<?= count($attachments) ?>)</div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                        <?php foreach ($attachments as $att): ?>
                        <a href="<?= SITE_URL ?>/api/serve-file.php?f=<?= urlencode($att) ?>&t=<?= urlencode($token) ?>" target="_blank"
                           style="border-radius:8px;overflow:hidden;aspect-ratio:1;display:block;background:#F1F5F9">
                            <img src="<?= SITE_URL ?>/api/serve-file.php?f=<?= urlencode($att) ?>&t=<?= urlencode($token) ?>"
                                 style="width:100%;height:100%;object-fit:cover" alt="مرفق">
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($c['admin_reply']): ?>
                <div class="complaint-reply">
                    <div class="complaint-reply-title">💬 رد الإدارة:</div>
                    <div class="complaint-reply-text"><?= htmlspecialchars($c['admin_reply']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Bottom Bar - Always Visible -->
<div class="bottom-bar" style="left:0;right:0;width:100%">
    <a class="bottom-btn bottom-btn-back"
       href="<?= SITE_URL ?>/employee/attendance.php?token=<?= urlencode($token) ?>">
        ← الرجوع
    </a>
    <button class="bottom-btn bottom-btn-new" onclick="switchTab('new');window.scrollTo({top:0,behavior:'smooth'})">
        ✏️ شكوى جديدة
    </button>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
const TOKEN = <?= json_encode($token) ?>;
const SITE_URL = <?= json_encode(SITE_URL) ?>;
let selectedType = '';
let capturedFiles = [];

// Camera input handler
document.getElementById('cameraInput').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        if (capturedFiles.length >= 5) {
            showToast('الحد الأقصى 5 صور', 'error');
            return;
        }
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            showToast('يُسمح بالصور فقط (JPG, PNG, WEBP)', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('حجم الصورة يتجاوز 5 ميجا', 'error');
            return;
        }
        capturedFiles.push(file);
    });
    renderPreviews();
    this.value = '';
});

function renderPreviews() {
    const container = document.getElementById('previews');
    const countEl = document.getElementById('attachCount');
    container.innerHTML = '';
    capturedFiles.forEach((file, i) => {
        const div = document.createElement('div');
        div.className = 'preview-item';
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.onload = () => URL.revokeObjectURL(img.src);
        const btn = document.createElement('button');
        btn.className = 'preview-remove';
        btn.textContent = '✕';
        btn.onclick = (e) => { e.stopPropagation(); capturedFiles.splice(i, 1); renderPreviews(); };
        div.appendChild(img);
        div.appendChild(btn);
        container.appendChild(div);
    });
    countEl.textContent = capturedFiles.length > 0 ? capturedFiles.length + ' / 5 صور مرفقة' : '';
}

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach((b, i) => {
        b.classList.toggle('active', (tab === 'new' && i === 0) || (tab === 'history' && i === 1));
    });
    document.getElementById('tab-new').classList.toggle('active', tab === 'new');
    document.getElementById('tab-history').classList.toggle('active', tab === 'history');
}

function selectType(el) {
    document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    selectedType = el.dataset.type;

    const tg = document.getElementById('targetGroup');
    const tl = document.getElementById('targetLabel');
    if (selectedType === 'person') {
        tg.style.display = 'block';
        tl.textContent = 'اسم الشخص';
        document.getElementById('targetName').placeholder = 'اكتب اسم الشخص...';
    } else if (selectedType === 'branch') {
        tg.style.display = 'block';
        tl.textContent = 'اسم الفرع';
        document.getElementById('targetName').placeholder = 'اكتب اسم الفرع...';
    } else if (selectedType === 'group') {
        tg.style.display = 'block';
        tl.textContent = 'اسم المجموعة';
        document.getElementById('targetName').placeholder = 'اكتب اسم المجموعة...';
    } else {
        tg.style.display = 'none';
    }
}

async function submitComplaint() {
    const btn = document.getElementById('submitBtn');
    const subject = document.getElementById('subject').value.trim();
    const body = document.getElementById('body').value.trim();
    const targetName = document.getElementById('targetName').value.trim();

    if (!selectedType) { showToast('يرجى اختيار نوع الشكوى', 'error'); return; }
    if (subject.length < 5) { showToast('يرجى كتابة عنوان الشكوى (5 أحرف على الأقل)', 'error'); return; }
    if (body.length < 10) { showToast('يرجى كتابة تفاصيل الشكوى (10 أحرف على الأقل)', 'error'); return; }

    btn.disabled = true;
    btn.textContent = '⏳ جاري الإرسال...';

    try {
        const fd = new FormData();
        fd.append('token', TOKEN);
        fd.append('action', 'submit');
        fd.append('complaint_type', selectedType);
        fd.append('target_name', targetName);
        fd.append('subject', subject);
        fd.append('body', body);
        capturedFiles.forEach(file => fd.append('attachments[]', file));

        const res = await fetch(SITE_URL + '/api/complaints.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            // Reset form
            document.getElementById('subject').value = '';
            document.getElementById('body').value = '';
            document.getElementById('targetName').value = '';
            document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
            document.getElementById('targetGroup').style.display = 'none';
            selectedType = '';
            capturedFiles = [];
            renderPreviews();
            // Reload after short delay to update history
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'حدث خطأ', 'error');
        }
    } catch (e) {
        showToast('خطأ في الاتصال بالخادم', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '📤 إرسال الشكوى';
    }
}

function toggleComplaint(id) {
    const body = document.getElementById('cbody_' + id);
    if (body) body.classList.toggle('open');
}

function showToast(msg, type) {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'toast ' + (type || '');
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>
</body>
</html>
