<?php
// =============================================================
// admin/scheduled-emails.php - إدارة المراسلات المجدولة
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'إدارة المراسلات المجدولة';
$activePage = 'scheduled-emails';
$message    = '';
$msgType    = '';

// =================== معالجة الإجراءات ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'طلب غير صالح'; 
        $msgType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        // حذف مراسلة
        if ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            db()->prepare("DELETE FROM scheduled_emails WHERE id = ?")->execute([$id]);
            auditLog('delete_scheduled_email', "حذف مراسلة مجدولة #$id");
            $message = 'تم حذف المراسلة بنجاح';
            $msgType = 'success';
        }

        // تفعيل/إيقاف مراسلة
        if ($action === 'toggle' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $stmt = db()->prepare("SELECT is_active FROM scheduled_emails WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            if ($current) {
                $newStatus = $current['is_active'] ? 0 : 1;
                db()->prepare("UPDATE scheduled_emails SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
                $statusText = $newStatus ? 'تفعيل' : 'إيقاف';
                auditLog('toggle_scheduled_email', "$statusText مراسلة #$id");
                $message = $newStatus ? 'تم تفعيل المراسلة' : 'تم إيقاف المراسلة';
                $msgType = 'success';
            }
        }

        // إضافة/تعديل مراسلة
        if ($action === 'save') {
            $id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            $title       = sanitize($_POST['title'] ?? '');
            $report_type = sanitize($_POST['report_type'] ?? '');
            $frequency   = sanitize($_POST['frequency'] ?? 'daily');
            $send_time   = sanitize($_POST['send_time'] ?? '08:00');
            $day_of_week = sanitize($_POST['day_of_week'] ?? '1');
            $day_of_month= (int)($_POST['day_of_month'] ?? 1);
            $recipients  = sanitize($_POST['recipients'] ?? '');
            $is_active   = isset($_POST['is_active']) ? 1 : 0;
            $filters     = sanitize($_POST['filters'] ?? '{}');

            if (empty($title) || empty($report_type) || empty($recipients)) {
                $message = 'يرجى ملء جميع الحقول المطلوبة';
                $msgType = 'error';
            } else {
                // التحقق من صحة الإيميلات
                $emailList = array_filter(array_map('trim', explode(',', $recipients)));
                $validEmails = [];
                foreach ($emailList as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $validEmails[] = $email;
                    }
                }
                
                if (empty($validEmails)) {
                    $message = 'يرجى إدخال بريد إلكتروني صحيح واحد على الأقل';
                    $msgType = 'error';
                } else {
                    $recipients = implode(',', $validEmails);
                    
                    if ($id) {
                        // تحديث
                        $stmt = db()->prepare("
                            UPDATE scheduled_emails SET
                                title = ?, report_type = ?, frequency = ?, 
                                send_time = ?, day_of_week = ?, day_of_month = ?,
                                recipients = ?, is_active = ?, filters = ?,
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $report_type, $frequency, $send_time, 
                                       $day_of_week, $day_of_month, $recipients, 
                                       $is_active, $filters, $id]);
                        auditLog('update_scheduled_email', "تحديث مراسلة #$id: $title");
                        $message = 'تم تحديث المراسلة بنجاح';
                    } else {
                        // إضافة جديد
                        $stmt = db()->prepare("
                            INSERT INTO scheduled_emails 
                            (title, report_type, frequency, send_time, day_of_week, 
                             day_of_month, recipients, is_active, filters, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$title, $report_type, $frequency, $send_time,
                                       $day_of_week, $day_of_month, $recipients, 
                                       $is_active, $filters]);
                        auditLog('create_scheduled_email', "إضافة مراسلة: $title");
                        $message = 'تم إضافة المراسلة بنجاح';
                    }
                    $msgType = 'success';
                }
            }
        }
    }
}

// التحقق من وجود الجداول المطلوبة
$tablesExist = false;
try {
    $checkTable = db()->query("SHOW TABLES LIKE 'scheduled_emails'")->fetch();
    $tablesExist = !empty($checkTable);
} catch (PDOException $e) {
    $tablesExist = false;
}

// إذا لم تكن الجداول موجودة، عرض صفحة التثبيت
if (!$tablesExist) {
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تثبيت نظام المراسلات المجدولة</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
            .setup-card { background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; padding: 3rem; text-align: center; }
            h1 { color: #1f2937; margin-bottom: 1rem; font-size: 2rem; }
            .icon { font-size: 4rem; margin-bottom: 1.5rem; }
            p { color: #6b7280; font-size: 1.1rem; line-height: 1.6; margin-bottom: 2rem; }
            .btn { display: inline-block; padding: 14px 32px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 1.05rem; transition: all 0.3s; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
            .btn:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5); }
            .steps { text-align: right; background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; }
            .steps h3 { color: #374151; margin-bottom: 1rem; font-size: 1.1rem; }
            .steps ol { margin-right: 1.5rem; color: #4b5563; }
            .steps li { margin-bottom: 0.5rem; }
        </style>
    </head>
    <body>
        <div class="setup-card">
            <div class="icon">📧</div>
            <h1>نظام المراسلات المجدولة</h1>
            <p>يبدو أن النظام غير مثبت بعد. يتطلب تشغيل هذه الميزة إنشاء جداول قاعدة البيانات المطلوبة.</p>
            
            <div class="steps">
                <h3>📋 متطلبات التثبيت:</h3>
                <ol>
                    <li>تشغيل ملف SQL لإنشاء الجداول</li>
                    <li>التحقق من صلاحيات قاعدة البيانات</li>
                    <li>إعداد Cron Job للإرسال التلقائي</li>
                </ol>
            </div>
            
            <a href="setup-scheduled-emails.php" class="btn">🚀 بدء التثبيت الآن</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// جلب جميع المراسلات المجدولة
$schedules = [];
$lastSends = [];

try {
    $schedules = db()->query("
        SELECT * FROM scheduled_emails 
        ORDER BY is_active DESC, id DESC
    ")->fetchAll();
    
    // جلب آخر عمليات الإرسال
    $lastSends = db()->query("
        SELECT l.*, s.title AS schedule_title 
        FROM email_send_log l
        LEFT JOIN scheduled_emails s ON l.schedule_id = s.id
        ORDER BY l.sent_at DESC
        LIMIT 20
    ")->fetchAll();
} catch (PDOException $e) {
    $message = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
    $msgType = 'error';
    $schedules = [];
    $lastSends = [];
}

$csrf = generateCsrfToken();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<style>
.email-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.email-table {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.email-table th {
    background: #f8f9fa;
    padding: 12px 16px;
    text-align: right;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
}
.email-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f5;
}
.email-table tr:last-child td {
    border-bottom: none;
}
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}
.badge-active { background: #d1f4e0; color: #0d894f; }
.badge-inactive { background: #ffe0e0; color: #c92a2a; }
.badge-daily { background: #dbeafe; color: #1e40af; }
.badge-weekly { background: #fef3c7; color: #92400e; }
.badge-monthly { background: #e0e7ff; color: #4338ca; }
.action-btns {
    display: flex;
    gap: 8px;
}
.btn {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.btn-primary {
    background: #3b82f6;
    color: white;
}
.btn-primary:hover {
    background: #2563eb;
}
.btn-small {
    padding: 4px 10px;
    font-size: 0.85rem;
}
.btn-success { background: #10b981; color: white; }
.btn-danger { background: #ef4444; color: white; }
.btn-warning { background: #f59e0b; color: white; }
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    overflow-y: auto;
}
.modal-content {
    background: white;
    max-width: 700px;
    margin: 2rem auto;
    border-radius: 12px;
    padding: 2rem;
}
.form-group {
    margin-bottom: 1.25rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}
.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.95rem;
}
.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.frequency-options {
    display: none;
    margin-top: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}
.tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
}
.tab {
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}
.tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    font-weight: 600;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $msgType ?>" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: <?= $msgType === 'success' ? '#d1f4e0' : '#ffe0e0' ?>; color: <?= $msgType === 'success' ? '#0d894f' : '#c92a2a' ?>;">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="email-header">
    <h2 style="margin: 0;">📧 إدارة المراسلات المجدولة</h2>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="btn" style="background:#0891b2;color:white" onclick="testSmtp()">🔌 اختبار SMTP</button>
        <button class="btn" style="background:#8b5cf6;color:white" onclick="openTestModal()">📧 إرسال تجريبي</button>
        <button class="btn btn-primary" onclick="openModal()">➕ إضافة مراسلة جديدة</button>
    </div>
</div>

<!-- SMTP Status -->
<div id="smtpStatus" style="display:none; margin-bottom:1rem; padding:12px 16px; border-radius:8px; font-size:0.95rem;"></div>

<!-- Tabs -->
<div class="tabs">
    <div class="tab active" onclick="switchTab('schedules')">📅 المراسلات المجدولة</div>
    <div class="tab" onclick="switchTab('history')">📝 سجل الإرسال</div>
    <div class="tab" onclick="switchTab('previews')">👁️ معاينة القوالب</div>
</div>

<!-- Tab: Scheduled Emails -->
<div class="tab-content active" id="tab-schedules">
    <?php if (empty($schedules)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
            <p style="font-size: 1.1rem; color: #6b7280;">لا توجد مراسلات مجدولة حالياً</p>
            <button class="btn btn-primary" onclick="openModal()" style="margin-top: 1rem;">
                إضافة أول مراسلة
            </button>
        </div>
    <?php else: ?>
        <table class="email-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>نوع التقرير</th>
                    <th>التكرار</th>
                    <th>وقت الإرسال</th>
                    <th>المستلمون</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $sch): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sch['title']) ?></strong></td>
                    <td><?= getReportTypeName($sch['report_type']) ?></td>
                    <td>
                        <span class="badge badge-<?= $sch['frequency'] ?>">
                            <?= getFrequencyName($sch['frequency'], $sch) ?>
                        </span>
                    </td>
                    <td><?= date('h:i A', strtotime($sch['send_time'])) ?></td>
                    <td>
                        <?php 
                        $emails = explode(',', $sch['recipients']);
                        echo count($emails) . ' مستلم';
                        ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $sch['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $sch['is_active'] ? '✓ نشط' : '✕ متوقف' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="btn btn-small" style="background:#0891b2;color:white" onclick="sendNow(<?= $sch['id'] ?>, '<?= htmlspecialchars($sch['title'], ENT_QUOTES) ?>')" title="إرسال الآن">
                                🚀
                            </button>
                            <button class="btn btn-small" style="background:#8b5cf6;color:white" onclick="previewReport('<?= $sch['report_type'] ?>')" title="معاينة">
                                👁️
                            </button>
                            <button class="btn btn-small btn-warning" onclick="editSchedule(<?= $sch['id'] ?>)" title="تعديل">
                                ✏️
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل تريد <?= $sch['is_active'] ? 'إيقاف' : 'تفعيل' ?> هذه المراسلة؟')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $sch['id'] ?>">
                                <button type="submit" class="btn btn-small btn-success" title="<?= $sch['is_active'] ? 'إيقاف' : 'تفعيل' ?>">
                                    <?= $sch['is_active'] ? '⏸️' : '▶️' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $sch['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger" title="حذف">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Tab: Send History -->
<div class="tab-content" id="tab-history">
    <?php if (empty($lastSends)): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
            <p style="font-size: 1.1rem; color: #6b7280;">لا يوجد سجل إرسال بعد</p>
        </div>
    <?php else: ?>
        <table class="email-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>المستلمون</th>
                    <th>الحالة</th>
                    <th>تاريخ الإرسال</th>
                    <th>رسالة الخطأ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lastSends as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['schedule_title'] ?? 'غير محدد') ?></td>
                    <td><?= count(explode(',', $log['recipients'])) ?> مستلم</td>
                    <td>
                        <span class="badge badge-<?= $log['status'] === 'sent' ? 'active' : 'inactive' ?>">
                            <?= $log['status'] === 'sent' ? '✓ مرسل' : '✕ فشل' ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d h:i A', strtotime($log['sent_at'])) ?></td>
                    <td style="color: #dc2626; font-size: 0.85rem;">
                        <?= $log['error_message'] ? htmlspecialchars($log['error_message']) : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Tab: Preview Templates -->
<div class="tab-content" id="tab-previews">
    <div style="background:white; border-radius:12px; padding:2rem;">
        <h3 style="margin-top:0; color:#1e293b;">👁️ معاينة قوالب التقارير</h3>
        <p style="color:#64748b; margin-bottom:1.5rem;">اضغط على أي قالب لمعاينة شكل التقرير كما سيظهر في البريد الإلكتروني</p>
        
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
            <?php
            $reportTypes = [
                'daily' => ['📋', 'تقرير الحضور اليومي', '#3b82f6'],
                'late' => ['⏰', 'تقرير المتأخرين', '#ef4444'],
                'absent' => ['🚫', 'تقرير الغائبين', '#f59e0b'],
                'overtime' => ['⏱️', 'تقرير العمل الإضافي', '#10b981'],
                'monthly' => ['📊', 'التقرير الشهري', '#8b5cf6'],
                'summary' => ['📈', 'ملخص الحضور', '#0891b2'],
                'payroll' => ['💰', 'تقرير الرواتب', '#059669'],
            ];
            foreach ($reportTypes as $rType => $rInfo): ?>
                <div onclick="previewReport('<?= $rType ?>')" 
                     style="cursor:pointer; border:2px solid #e2e8f0; border-radius:12px; padding:20px; text-align:center; transition:all 0.2s; background:white;"
                     onmouseover="this.style.borderColor='<?= $rInfo[2] ?>'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'"
                     onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='none'; this.style.boxShadow='none'">
                    <div style="font-size:36px; margin-bottom:8px;"><?= $rInfo[0] ?></div>
                    <div style="font-weight:600; color:#1e293b; font-size:0.95rem;"><?= $rInfo[1] ?></div>
                    <div style="margin-top:8px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?= $rInfo[2] ?>;"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal للإضافة/التعديل -->
<div class="modal" id="emailModal">
    <div class="modal-content">
        <h3 style="margin-top: 0;">📧 <span id="modalTitle">إضافة مراسلة جديدة</span></h3>
        <form method="POST" id="emailForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="scheduleId">

            <div class="form-group">
                <label>عنوان المراسلة *</label>
                <input type="text" name="title" id="title" class="form-control" required placeholder="مثال: تقرير الحضور اليومي">
            </div>

            <div class="form-group">
                <label>نوع التقرير *</label>
                <select name="report_type" id="report_type" class="form-control" required>
                    <option value="">-- اختر نوع التقرير --</option>
                    <option value="daily">📋 تقرير الحضور اليومي</option>
                    <option value="late">⏰ تقرير المتأخرين</option>
                    <option value="absent">🚫 تقرير الغائبين</option>
                    <option value="overtime">⏱️ تقرير العمل الإضافي</option>
                    <option value="monthly">📊 تقرير الحضور الشهري</option>
                    <option value="payroll">💰 تقرير الرواتب</option>
                    <option value="summary">📈 ملخص الحضور</option>
                </select>
            </div>

            <div class="form-group">
                <label>التكرار *</label>
                <select name="frequency" id="frequency" class="form-control" required onchange="toggleFrequencyOptions()">
                    <option value="daily">يومي</option>
                    <option value="weekly">أسبوعي</option>
                    <option value="monthly">شهري</option>
                </select>
            </div>

            <!-- خيارات الأسبوعي -->
            <div class="frequency-options" id="weeklyOptions">
                <label>اليوم</label>
                <select name="day_of_week" class="form-control">
                    <option value="0">الأحد</option>
                    <option value="1">الإثنين</option>
                    <option value="2">الثلاثاء</option>
                    <option value="3">الأربعاء</option>
                    <option value="4">الخميس</option>
                    <option value="5">الجمعة</option>
                    <option value="6">السبت</option>
                </select>
            </div>

            <!-- خيارات الشهري -->
            <div class="frequency-options" id="monthlyOptions">
                <label>يوم من الشهر</label>
                <input type="number" name="day_of_month" min="1" max="28" value="1" class="form-control">
            </div>

            <div class="form-group">
                <label>وقت الإرسال *</label>
                <input type="time" name="send_time" id="send_time" class="form-control" value="08:00" required>
            </div>

            <div class="form-group">
                <label>المستلمون (إيميلات مفصولة بفاصلة) *</label>
                <textarea name="recipients" id="recipients" class="form-control" rows="3" required placeholder="admin@company.com, manager@company.com"></textarea>
                <small style="color: #6b7280;">يمكنك إدخال عدة إيميلات مفصولة بفاصلة</small>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" checked>
                    <label for="is_active" style="margin: 0;">تفعيل المراسلة</label>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">💾 حفظ</button>
                <button type="button" class="btn" onclick="closeModal()" style="flex: 1; background: #6b7280; color: white;">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal الإرسال التجريبي -->
<div class="modal" id="testModal">
    <div class="modal-content" style="max-width:500px;">
        <h3 style="margin-top:0;">📧 إرسال تجريبي</h3>
        <p style="color:#64748b; margin-bottom:1.5rem;">أرسل نسخة تجريبية من أي تقرير لاختبار الشكل والإعدادات</p>
        
        <div class="form-group">
            <label>البريد الإلكتروني للاختبار</label>
            <input type="email" id="testEmail" class="form-control" placeholder="test@example.com" value="etgan@sarh.io">
        </div>
        
        <div class="form-group">
            <label>نوع التقرير</label>
            <select id="testReportType" class="form-control">
                <option value="summary">📈 ملخص الحضور</option>
                <option value="daily">📋 تقرير الحضور اليومي</option>
                <option value="late">⏰ تقرير المتأخرين</option>
                <option value="absent">🚫 تقرير الغائبين</option>
                <option value="overtime">⏱️ تقرير العمل الإضافي</option>
                <option value="monthly">📊 التقرير الشهري</option>
                <option value="payroll">💰 تقرير الرواتب</option>
            </select>
        </div>
        
        <div id="testResult" style="display:none; margin-bottom:1rem; padding:12px; border-radius:8px; font-size:0.95rem;"></div>
        
        <div style="display:flex; gap:1rem;">
            <button onclick="sendTestEmail()" class="btn btn-primary" style="flex:1;" id="testSendBtn">🚀 إرسال</button>
            <button onclick="document.getElementById('testModal').style.display='none'" class="btn" style="flex:1; background:#6b7280; color:white;">إلغاء</button>
        </div>
    </div>
</div>

<script>
const schedules = <?= json_encode($schedules) ?>;
const cronSecret = '<?= htmlspecialchars($_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '', ENT_QUOTES) ?>';
const cronBase = '<?= rtrim(SITE_URL, "/") ?>/cron/send-scheduled-emails.php';
const cronUrl = cronBase + '?secret=' + encodeURIComponent(cronSecret);

function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function openModal(id = null) {
    const modal = document.getElementById('emailModal');
    const form = document.getElementById('emailForm');
    
    if (id) {
        const schedule = schedules.find(s => s.id == id);
        document.getElementById('modalTitle').textContent = 'تعديل المراسلة';
        document.getElementById('scheduleId').value = schedule.id;
        document.getElementById('title').value = schedule.title;
        document.getElementById('report_type').value = schedule.report_type;
        document.getElementById('frequency').value = schedule.frequency;
        document.getElementById('send_time').value = schedule.send_time;
        document.getElementById('recipients').value = schedule.recipients;
        document.getElementById('is_active').checked = schedule.is_active == 1;
        if (schedule.frequency === 'weekly') {
            document.querySelector('[name="day_of_week"]').value = schedule.day_of_week;
        } else if (schedule.frequency === 'monthly') {
            document.querySelector('[name="day_of_month"]').value = schedule.day_of_month;
        }
    } else {
        document.getElementById('modalTitle').textContent = 'إضافة مراسلة جديدة';
        form.reset();
        document.getElementById('scheduleId').value = '';
        document.getElementById('is_active').checked = true;
    }
    toggleFrequencyOptions();
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('emailModal').style.display = 'none';
}

function editSchedule(id) {
    openModal(id);
}

function toggleFrequencyOptions() {
    const freq = document.getElementById('frequency').value;
    document.getElementById('weeklyOptions').style.display = freq === 'weekly' ? 'block' : 'none';
    document.getElementById('monthlyOptions').style.display = freq === 'monthly' ? 'block' : 'none';
}

// === اختبار SMTP ===
async function testSmtp() {
    const status = document.getElementById('smtpStatus');
    status.style.display = 'block';
    status.style.background = '#dbeafe';
    status.style.color = '#1e40af';
    status.innerHTML = '⏳ جاري اختبار اتصال SMTP...';
    
    try {
        const res = await fetch(cronUrl + '&test_smtp=1');
        const data = await res.json();
        if (data.success) {
            status.style.background = '#dcfce7';
            status.style.color = '#166534';
            status.innerHTML = '✅ ' + data.message;
        } else {
            status.style.background = '#fee2e2';
            status.style.color = '#991b1b';
            status.innerHTML = '❌ ' + data.message;
        }
    } catch (e) {
        status.style.background = '#fee2e2';
        status.style.color = '#991b1b';
        status.innerHTML = '❌ خطأ في الاتصال: ' + e.message;
    }
    
    setTimeout(() => { status.style.display = 'none'; }, 8000);
}

// === إرسال تجريبي ===
function openTestModal() {
    document.getElementById('testModal').style.display = 'block';
    document.getElementById('testResult').style.display = 'none';
}

async function sendTestEmail() {
    const email = document.getElementById('testEmail').value;
    const type = document.getElementById('testReportType').value;
    const resultDiv = document.getElementById('testResult');
    const btn = document.getElementById('testSendBtn');
    
    if (!email) { alert('يرجى إدخال بريد إلكتروني'); return; }
    
    btn.disabled = true;
    btn.innerHTML = '⏳ جاري الإرسال...';
    resultDiv.style.display = 'block';
    resultDiv.style.background = '#dbeafe';
    resultDiv.style.color = '#1e40af';
    resultDiv.innerHTML = '⏳ جاري إرسال الرسالة التجريبية...';
    
    try {
        const res = await fetch(cronUrl + '&test_email=' + encodeURIComponent(email) + '&test_type=' + type);
        const data = await res.json();
        
        if (data.success) {
            resultDiv.style.background = '#dcfce7';
            resultDiv.style.color = '#166534';
            resultDiv.innerHTML = '✅ ' + data.message;
        } else {
            resultDiv.style.background = '#fee2e2';
            resultDiv.style.color = '#991b1b';
            resultDiv.innerHTML = '❌ ' + data.message;
        }
    } catch (e) {
        resultDiv.style.background = '#fee2e2';
        resultDiv.style.color = '#991b1b';
        resultDiv.innerHTML = '❌ خطأ: ' + e.message;
    }
    
    btn.disabled = false;
    btn.innerHTML = '🚀 إرسال';
}

// === إرسال فوري ===
async function sendNow(id, title) {
    if (!confirm('هل تريد إرسال "' + title + '" الآن؟')) return;
    
    const status = document.getElementById('smtpStatus');
    status.style.display = 'block';
    status.style.background = '#dbeafe';
    status.style.color = '#1e40af';
    status.innerHTML = '⏳ جاري إرسال: ' + title + '...';
    
    try {
        const res = await fetch(cronUrl + '&send_id=' + id);
        const data = await res.json();
        
        if (data.success) {
            status.style.background = '#dcfce7';
            status.style.color = '#166534';
            status.innerHTML = '✅ ' + (data.message || 'تم الإرسال بنجاح');
            setTimeout(() => location.reload(), 2000);
        } else {
            status.style.background = '#fee2e2';
            status.style.color = '#991b1b';
            status.innerHTML = '❌ ' + (data.error || data.message || 'فشل الإرسال');
        }
    } catch (e) {
        status.style.background = '#fee2e2';
        status.style.color = '#991b1b';
        status.innerHTML = '❌ خطأ: ' + e.message;
    }
    
    setTimeout(() => { status.style.display = 'none'; }, 8000);
}

// === معاينة القوالب ===
function previewReport(type) {
    window.open(cronUrl + '&preview=' + type, '_blank', 'width=800,height=700');
}

// إغلاق المودالات عند النقر خارجها
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php
// Helper functions
function getReportTypeName($type) {
    $types = [
        'daily' => 'تقرير الحضور اليومي',
        'late' => 'تقرير المتأخرين',
        'absent' => 'تقرير الغائبين',
        'overtime' => 'تقرير العمل الإضافي',
        'monthly' => 'تقرير الحضور الشهري',
        'payroll' => 'تقرير الرواتب',
        'summary' => 'ملخص الحضور'
    ];
    return $types[$type] ?? $type;
}

function getFrequencyName($freq, $data) {
    $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    
    switch ($freq) {
        case 'daily':
            return '⏰ يومي';
        case 'weekly':
            return '📅 أسبوعي (' . $days[$data['day_of_week']] . ')';
        case 'monthly':
            return '📆 شهري (يوم ' . $data['day_of_month'] . ')';
        default:
            return $freq;
    }
}
?>
