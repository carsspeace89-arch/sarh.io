<?php
// =============================================================
// admin/inbox-send.php - إرسال رسائل صندوق الوارد للموظفين
// مخالفات · خصومات · مكافآت · تحذيرات · إشعارات
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'إرسال رسالة للموظف';
$activePage = 'inbox-send';

// ── قائمة الموظفين ──
$employees = db()->query("
    SELECT e.id, e.name, e.job_title, b.name AS branch_name
    FROM employees e
    LEFT JOIN branches b ON e.branch_id = b.id
    WHERE e.is_active = 1 AND e.deleted_at IS NULL
    ORDER BY e.name
")->fetchAll();

// ── معالجة POST ──
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    // ── إرسال رسالة واحدة أو جماعية ──
    if ($action === 'send_message') {
        $targets    = $_POST['targets'] ?? ''; // 'all' أو array من IDs
        $empIds     = $_POST['employee_ids'] ?? [];
        $msgType    = $_POST['msg_type'] ?? 'info';
        $title      = trim($_POST['title'] ?? '');
        $body       = trim($_POST['body'] ?? '');
        $amount     = !empty($_POST['amount']) ? (float)$_POST['amount'] : null;
        $currency   = trim($_POST['currency'] ?? 'ريال');
        $refDate    = !empty($_POST['reference_date']) ? $_POST['reference_date'] : null;

        $validTypes = ['violation', 'deduction', 'reward', 'warning', 'info'];
        if (!in_array($msgType, $validTypes)) $msgType = 'info';
        if (strlen($title) < 2 || strlen($body) < 3) {
            echo json_encode(['success' => false, 'message' => 'العنوان والمحتوى مطلوبان']);
            exit;
        }

        // تحديد المستلمين
        if ($targets === 'all') {
            $recipients = array_column($employees, 'id');
        } elseif (!empty($empIds)) {
            $recipients = array_map('intval', (array)$empIds);
            // تحقق أن الـ IDs موجودة
            $validIds = array_column($employees, 'id');
            $recipients = array_values(array_intersect($recipients, $validIds));
        } else {
            echo json_encode(['success' => false, 'message' => 'لم يتم اختيار موظف']);
            exit;
        }

        if (empty($recipients)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم اختيار موظف']);
            exit;
        }

        try {
            db()->beginTransaction();

            $stmt = db()->prepare("
                INSERT INTO employee_inbox
                    (employee_id, admin_id, msg_type, title, body, amount, currency, reference_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($recipients as $eid) {
                $stmt->execute([
                    $eid,
                    $_SESSION['admin_id'],
                    $msgType,
                    $title,
                    $body,
                    $amount,
                    $currency,
                    $refDate,
                ]);
                // تحديث العداد
                db()->prepare("UPDATE employees SET unread_inbox_count = unread_inbox_count + 1 WHERE id = ?")
                    ->execute([$eid]);
            }

            db()->commit();

            $typeLabel = ['violation'=>'مخالفة','deduction'=>'خصم','reward'=>'مكافأة','warning'=>'تحذير','info'=>'إشعار'][$msgType];
            $countStr = count($recipients) > 1 ? count($recipients) . ' موظفين' : 'موظف واحد';
            auditLog('inbox_send', "إرسال {$typeLabel} لـ {$countStr}: {$title}");

            // إرسال إشعار Push للموظفين
            $typeIcons = ['violation'=>'🚫','deduction'=>'💸','reward'=>'🏆','warning'=>'⚠️','info'=>'ℹ️'];
            $pushIcon = $typeIcons[$msgType] ?? 'ℹ️';
            $pushResult = sendPushNotification($recipients, $pushIcon . ' ' . $title, $body, [
                'tag' => 'inbox-' . $msgType . '-' . time(),
                'url' => '/employee/my-inbox.php'
            ]);

            echo json_encode([
                'success'   => true,
                'message'   => "تم إرسال الرسالة بنجاح إلى {$countStr}" . ($pushResult['sent'] > 0 ? " (+ {$pushResult['sent']} إشعار push)" : ''),
                'sent_to'   => count($recipients),
                'push_sent' => $pushResult['sent'] ?? 0,
            ]);
        } catch (Exception $e) {
            db()->rollBack();
            echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header">
    <div class="page-title">
        <h1>📨 إرسال رسالة للموظف</h1>
        <p>أرسل مخالفات أو خصومات أو مكافآت أو إشعارات لموظف واحد أو لجميع الموظفين</p>
    </div>
    <div class="page-actions">
        <a href="inbox-messages.php" class="btn btn-secondary">
            <?= svgIcon('audit', 16) ?> سجل الرسائل المرسلة
        </a>
    </div>
</div>

<div style="max-width:760px;margin:0 auto">

    <!-- بطاقات الإرشاد السريع -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px">
        <?php
        $typeCards = [
            ['type'=>'violation','icon'=>'🚫','label'=>'مخالفة','color'=>'#EF4444','bg'=>'#FEF2F2'],
            ['type'=>'deduction','icon'=>'💸','label'=>'خصم','color'=>'#F59E0B','bg'=>'#FFFBEB'],
            ['type'=>'reward',   'icon'=>'🏆','label'=>'مكافأة','color'=>'#10B981','bg'=>'#ECFDF5'],
            ['type'=>'warning',  'icon'=>'⚠️','label'=>'تحذير','color'=>'#F97316','bg'=>'#FFF7ED'],
            ['type'=>'info',     'icon'=>'ℹ️','label'=>'إشعار عام','color'=>'#3B82F6','bg'=>'#EFF6FF'],
        ];
        foreach ($typeCards as $tc): ?>
        <div class="stat-card" style="cursor:pointer;border:2px solid transparent;transition:all .2s"
             onclick="selectType('<?= $tc['type'] ?>')"
             id="typeCard_<?= $tc['type'] ?>">
            <div style="font-size:1.8rem;margin-bottom:6px"><?= $tc['icon'] ?></div>
            <div style="font-weight:700;font-size:.85rem;color:<?= $tc['color'] ?>"><?= $tc['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- نموذج الإرسال -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">✍️ تفاصيل الرسالة</h3>
        </div>
        <div class="card-body">
            <form id="sendForm">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="send_message">

                <!-- نوع الرسالة -->
                <div class="form-group" style="margin-bottom:18px">
                    <label class="form-label">نوع الرسالة <span style="color:#EF4444">*</span></label>
                    <select name="msg_type" id="msgType" class="form-control" onchange="onTypeChange(this.value)" required>
                        <option value="violation">🚫 مخالفة</option>
                        <option value="deduction">💸 خصم</option>
                        <option value="reward">🏆 مكافأة</option>
                        <option value="warning">⚠️ تحذير</option>
                        <option value="info" selected>ℹ️ إشعار عام</option>
                    </select>
                </div>

                <!-- المستلمون -->
                <div class="form-group" style="margin-bottom:18px">
                    <label class="form-label">المستلمون <span style="color:#EF4444">*</span></label>
                    <div style="display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;color:#F97316">
                            <input type="radio" name="targets" value="specific" checked onchange="toggleTargets(this.value)">
                            موظف/موظفون محددون
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;color:#EF4444">
                            <input type="radio" name="targets" value="all" onchange="toggleTargets(this.value)">
                            جميع الموظفين (<?= count($employees) ?>)
                        </label>
                    </div>
                    <div id="empSelector">
                        <select name="employee_ids[]" id="empSelect" class="form-control" multiple
                                style="min-height:120px" required>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['name']) ?>
                                <?= $emp['job_title'] ? '— ' . htmlspecialchars($emp['job_title']) : '' ?>
                                <?= $emp['branch_name'] ? ' | ' . htmlspecialchars($emp['branch_name']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div style="font-size:.75rem;color:#94A3B8;margin-top:4px">اضغط Ctrl (أو Cmd) لاختيار أكثر من موظف</div>
                    </div>
                </div>

                <!-- العنوان -->
                <div class="form-group" style="margin-bottom:18px">
                    <label class="form-label">العنوان <span style="color:#EF4444">*</span></label>
                    <input type="text" name="title" id="msgTitle" class="form-control"
                           placeholder="مثال: مخالفة التأخر المتكرر" maxlength="255" required>
                </div>

                <!-- المحتوى -->
                <div class="form-group" style="margin-bottom:18px">
                    <label class="form-label">تفاصيل الرسالة <span style="color:#EF4444">*</span></label>
                    <textarea name="body" id="msgBody" class="form-control"
                              rows="5" placeholder="اكتب تفاصيل الرسالة كاملة هنا..." maxlength="2000" required></textarea>
                    <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94A3B8;margin-top:3px">
                        <span>الحد الأقصى 2000 حرف</span>
                        <span id="charCount">0 / 2000</span>
                    </div>
                </div>

                <!-- المبلغ (يظهر فقط للخصم/المكافأة) -->
                <div id="amountSection" style="display:none;margin-bottom:18px">
                    <div style="display:grid;grid-template-columns:1fr auto;gap:10px">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">المبلغ</label>
                            <input type="number" name="amount" id="msgAmount" class="form-control"
                                   placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">العملة</label>
                            <select name="currency" class="form-control">
                                <option value="ريال">ريال</option>
                                <option value="دولار">دولار</option>
                                <option value="جنيه">جنيه</option>
                                <option value="دينار">دينار</option>
                                <option value="درهم">درهم</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- التاريخ المرجعي -->
                <div class="form-group" style="margin-bottom:24px">
                    <label class="form-label">التاريخ المرجعي (اختياري)</label>
                    <input type="date" name="reference_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    <div style="font-size:.75rem;color:#94A3B8;margin-top:3px">تاريخ وقوع المخالفة أو استحقاق المكافأة</div>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">مسح النموذج</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <?= svgIcon('bell', 16) ?>
                        <span id="submitText">إرسال الرسالة</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- قالب نصوص جاهزة -->
    <div class="card" style="margin-top:20px">
        <div class="card-header">
            <h3 class="card-title">📋 قوالب جاهزة</h3>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px">
                <?php
                $templates = [
                    ['type'=>'violation','title'=>'مخالفة التأخر','body'=>"تم رصد تأخرك في الحضور بما يخالف لوائح العمل المعتمدة.\nيُرجى الالتزام بمواعيد الدوام المقررة تفادياً للإجراءات التأديبية."],
                    ['type'=>'violation','title'=>'مخالفة الغياب بدون إذن','body'=>"تغيبت عن العمل دون إشعار مسبق أو اعتماد رسمي.\nيُرجى الرجوع لمسؤولك المباشر لتسوية هذا الغياب."],
                    ['type'=>'deduction','title'=>'خصم تأخر','body'=>"تم خصم مبلغ من راتبك جراء التأخر المتكرر في الحضور.\nللاستفسار يُرجى مراجعة قسم الموارد البشرية."],
                    ['type'=>'deduction','title'=>'خصم مخالفة','body'=>"تم تطبيق خصم مالي وفق لوائح العمل المعتمدة.\nيمكنك الاعتراض خلال 3 أيام عمل."],
                    ['type'=>'reward','title'=>'مكافأة الالتزام','body'=>"تهانينا! لقد حصلت على مكافأة تقديراً لالتزامك الاستثنائي بمواعيد الحضور.\nاستمر في هذا المستوى الرائع!"],
                    ['type'=>'reward','title'=>'مكافأة الأداء المتميز','body'=>"يسعدنا مكافأتك على أدائك المتميز وجهودك المبذولة.\nنقدر مساهمتك ونتطلع لمزيد من التميز."],
                    ['type'=>'warning','title'=>'تحذير رسمي','body'=>"هذا تحذير رسمي يُوجَّه إليك بسبب المخالفات المتكررة.\nفي حال الاستمرار سيتم اتخاذ إجراءات تأديبية أشد."],
                    ['type'=>'info','title'=>'إشعار اجتماع','body'=>"يُرجى العلم بوجود اجتماع عمل. سيتم إشعارك بالموعد والمكان قريباً."],
                ];
                foreach ($templates as $tpl): ?>
                <button class="btn btn-secondary"
                        style="text-align:right;font-size:.78rem;padding:7px 12px;justify-content:flex-start"
                        onclick='applyTemplate(<?= json_encode($tpl["type"]) ?>,<?= json_encode($tpl["title"]) ?>,<?= json_encode($tpl["body"]) ?>)'>
                    <?= ['violation'=>'🚫','deduction'=>'💸','reward'=>'🏆','warning'=>'⚠️','info'=>'ℹ️'][$tpl['type']] ?>
                    <?= htmlspecialchars($tpl['title']) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<script>
const TYPE_COLORS = {
    violation: '#EF4444', deduction: '#F59E0B',
    reward: '#10B981', warning: '#F97316', info: '#3B82F6'
};
const TYPE_LABELS = {
    violation:'🚫 مخالفة', deduction:'💸 خصم',
    reward:'🏆 مكافأة', warning:'⚠️ تحذير', info:'ℹ️ إشعار عام'
};

function selectType(type) {
    document.getElementById('msgType').value = type;
    onTypeChange(type);
}

function onTypeChange(type) {
    // تمييز البطاقة المختارة
    document.querySelectorAll('[id^="typeCard_"]').forEach(c => {
        c.style.borderColor = 'transparent';
        c.style.transform = '';
    });
    const card = document.getElementById('typeCard_' + type);
    if (card) {
        card.style.borderColor = TYPE_COLORS[type] || '#F97316';
        card.style.transform = 'scale(1.04)';
    }
    // إظهار/إخفاء حقل المبلغ
    document.getElementById('amountSection').style.display =
        (type === 'deduction' || type === 'reward') ? 'block' : 'none';
    // إسم زر الإرسال
    document.getElementById('submitText').textContent = 'إرسال ' + (TYPE_LABELS[type] || 'الرسالة');
}

function toggleTargets(val) {
    const sel = document.getElementById('empSelector');
    if (val === 'all') {
        sel.style.opacity = '.4';
        sel.style.pointerEvents = 'none';
        document.getElementById('empSelect').required = false;
    } else {
        sel.style.opacity = '1';
        sel.style.pointerEvents = '';
        document.getElementById('empSelect').required = true;
    }
}

function applyTemplate(type, title, body) {
    document.getElementById('msgType').value = type;
    document.getElementById('msgTitle').value = title;
    document.getElementById('msgBody').value = body;
    onTypeChange(type);
    document.getElementById('charCount').textContent = body.length + ' / 2000';
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetForm() {
    document.getElementById('sendForm').reset();
    onTypeChange('info');
    document.getElementById('charCount').textContent = '0 / 2000';
    toggleTargets('specific');
}

document.getElementById('msgBody').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length + ' / 2000';
});

document.getElementById('sendForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span style="opacity:.6">جاري الإرسال...</span>';

    const fd = new FormData(this);

    try {
        const res = await fetch('inbox-send.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            Toast.success(data.message);
            setTimeout(() => resetForm(), 800);
        } else {
            Toast.error(data.message || 'حدث خطأ');
        }
    } catch (err) {
        Toast.error('خطأ في الاتصال');
    }

    btn.disabled = false;
    document.getElementById('submitText').textContent = 'إرسال الرسالة';
    btn.prepend(Object.assign(document.createElement('span'), {innerHTML: '<?= svgIcon('bell', 16) ?>'}));
});

// تهيئة أولية
onTypeChange('info');
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
