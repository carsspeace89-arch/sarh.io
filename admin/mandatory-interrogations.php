<?php
// =============================================================
// admin/mandatory-interrogations.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mandatory_interrogation.php';

requireAdminLogin();
mi_ensure_tables();

$pageTitle = 'الاستجواب الإلزامي';
$activePage = 'mandatory-interrogations';

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $flash = ['type' => 'error', 'text' => 'طلب غير صالح (CSRF).'];
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'create_template') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $questionsText = (string)($_POST['questions'] ?? '');

                $lines = preg_split('/\r\n|\r|\n/', $questionsText) ?: [];
                $questions = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $questions[] = $line;
                    }
                }

                $id = mi_create_template($title, $questions, $description, (int)$_SESSION['admin_id']);
                auditLog('mandatory_template_create', "إنشاء نموذج استجواب #{$id}", $id);
                $flash = ['type' => 'success', 'text' => 'تم إنشاء النموذج بنجاح.'];
            }

            if ($action === 'assign_template') {
                $templateId = (int)($_POST['template_id'] ?? 0);
                $targetType = $_POST['target_type'] ?? 'employees';
                $employeeIds = [];

                if ($targetType === 'employees') {
                    $employeeIds = array_map('intval', $_POST['employee_ids'] ?? []);
                } elseif ($targetType === 'branch') {
                    $branchId = (int)($_POST['branch_id'] ?? 0);
                    $st = db()->prepare("SELECT id FROM employees WHERE is_active = 1 AND deleted_at IS NULL AND branch_id = ?");
                    $st->execute([$branchId]);
                    $employeeIds = array_map('intval', array_column($st->fetchAll(), 'id'));
                } elseif ($targetType === 'all') {
                    $st = db()->query("SELECT id FROM employees WHERE is_active = 1 AND deleted_at IS NULL");
                    $employeeIds = array_map('intval', array_column($st->fetchAll(), 'id'));
                }

                $notes = trim($_POST['assignment_notes'] ?? '');
                $res = mi_assign_template_to_employees($templateId, $employeeIds, (int)$_SESSION['admin_id'], null, $notes ?: null);
                auditLog('mandatory_assign', 'تعيين نموذج إلزامي', $templateId);
                $flash = ['type' => 'success', 'text' => "تم التعيين: {$res['inserted']} | تم التجاوز: {$res['skipped']}"];
            }

            if ($action === 'review_assignment') {
                $assignmentId = (int)($_POST['assignment_id'] ?? 0);
                $status = $_POST['status'] ?? '';
                $notes = trim($_POST['review_notes'] ?? '');

                if (mi_review_assignment($assignmentId, $status, (int)$_SESSION['admin_id'], $notes ?: null)) {
                    auditLog('mandatory_review', "مراجعة استجواب #{$assignmentId} => {$status}", $assignmentId);
                    $flash = ['type' => 'success', 'text' => 'تم تحديث حالة التقرير.'];
                } else {
                    $flash = ['type' => 'error', 'text' => 'تعذر تحديث الحالة.'];
                }
            }

            if ($action === 'send_followup') {
                $assignmentId = (int)($_POST['assignment_id'] ?? 0);
                $templateId = (int)($_POST['template_id'] ?? 0);
                $notes = trim($_POST['followup_notes'] ?? '');

                $st = db()->prepare("SELECT employee_id FROM mandatory_interrogation_assignments WHERE id = ? LIMIT 1");
                $st->execute([$assignmentId]);
                $row = $st->fetch();

                if ($row) {
                    mi_review_assignment($assignmentId, 'rejected', (int)$_SESSION['admin_id'], $notes ?: 'تم طلب استجواب إضافي');
                    $res = mi_assign_template_to_employees($templateId, [(int)$row['employee_id']], (int)$_SESSION['admin_id'], $assignmentId, $notes ?: null);
                    auditLog('mandatory_followup', "إرسال استجواب إضافي بناء على الطلب #{$assignmentId}", $assignmentId);
                    $flash = ['type' => 'success', 'text' => "تم إرسال استجواب إضافي (مُضاف: {$res['inserted']})."];
                } else {
                    $flash = ['type' => 'error', 'text' => 'الطلب الأصلي غير موجود.'];
                }
            }
        } catch (Throwable $e) {
            $flash = ['type' => 'error', 'text' => 'خطأ: ' . $e->getMessage()];
        }
    }
}

$templates = mi_get_templates(false);
$branches = db()->query("SELECT id, name FROM branches WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$employees = db()->query("SELECT id, name, job_title, branch_id FROM employees WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC")->fetchAll();

$rows = db()->query("SELECT a.*, t.title AS template_title, e.name AS employee_name, e.job_title, b.name AS branch_name,
                            adm.full_name AS reviewed_by_name
                     FROM mandatory_interrogation_assignments a
                     JOIN mandatory_interrogation_templates t ON t.id = a.template_id
                     JOIN employees e ON e.id = a.employee_id
                     LEFT JOIN branches b ON b.id = e.branch_id
                     LEFT JOIN admins adm ON adm.id = a.reviewed_by
                     ORDER BY a.id DESC
                     LIMIT 250")->fetchAll();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="page-header">
  <div class="page-title">
    <h1>🧾 الاستجواب الإلزامي قبل الحضور</h1>
    <p>تعيين نماذج مكتوبة للموظفين/الفروع، وإيقاف تسجيل الحضور حتى مراجعة الإدارة.</p>
  </div>
</div>

<?php if ($flash): ?>
  <div class="card" style="margin-bottom:14px;border-right:4px solid <?= $flash['type']==='success' ? '#10B981' : '#EF4444' ?>">
    <div class="card-body" style="padding:12px 14px;font-weight:700"><?= htmlspecialchars($flash['text']) ?></div>
  </div>
<?php endif; ?>

<div class="grid" style="display:grid;grid-template-columns:1fr;gap:14px">

  <div class="card">
    <div class="card-header"><h3>➕ إنشاء نموذج جديد</h3></div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="action" value="create_template">
        <div class="form-group">
          <label class="form-label">عنوان النموذج</label>
          <input class="form-control" name="title" required placeholder="مثال: استجواب ملاحظات المركبة">
        </div>
        <div class="form-group">
          <label class="form-label">وصف (اختياري)</label>
          <textarea class="form-control" name="description" rows="2"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">الأسئلة (كل سؤال في سطر مستقل)</label>
          <textarea class="form-control" name="questions" rows="7" required placeholder="السؤال 1\nالسؤال 2\nالسؤال 3"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">حفظ النموذج</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>📌 تعيين نموذج إجباري</h3></div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="action" value="assign_template">

        <div class="form-group">
          <label class="form-label">اختر النموذج</label>
          <select class="form-control" name="template_id" required>
            <?php foreach ($templates as $t): ?>
              <option value="<?= (int)$t['id'] ?>">
                <?= htmlspecialchars($t['title']) ?><?= (int)$t['is_default'] === 1 ? ' (افتراضي)' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">نطاق التعيين</label>
          <select class="form-control" name="target_type" id="targetType" required>
            <option value="employees">موظف/موظفون محددون</option>
            <option value="branch">فرع كامل</option>
            <option value="all">كل الموظفين النشطين</option>
          </select>
        </div>

        <div class="form-group" id="empWrap">
          <label class="form-label">اختر الموظفين (يمكن اختيار أكثر من موظف)</label>
          <select class="form-control" name="employee_ids[]" multiple size="8">
            <?php foreach ($employees as $e): ?>
              <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['name']) ?> — <?= htmlspecialchars((string)$e['job_title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" id="branchWrap" style="display:none">
          <label class="form-label">اختر الفرع</label>
          <select class="form-control" name="branch_id">
            <?php foreach ($branches as $b): ?>
              <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">ملاحظة تظهر للموظف (اختياري)</label>
          <textarea class="form-control" name="assignment_notes" rows="2" placeholder="مثال: يرجى كتابة تقرير تفصيلي ودقيق"></textarea>
        </div>

        <button class="btn btn-primary" type="submit">تعيين النموذج</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>📋 الطلبات والحالات</h3></div>
    <div class="card-body" style="padding:0">
      <?php if (!$rows): ?>
        <div style="padding:20px">لا توجد بيانات.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
          <tr>
            <th>#</th>
            <th>الموظف</th>
            <th>النموذج</th>
            <th>الحالة</th>
            <th>الإرسال</th>
            <th>مراجعة الإدارة</th>
            <th>إجراءات</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($r['employee_name']) ?></strong>
                <div style="font-size:.78rem;color:#64748b"><?= htmlspecialchars((string)$r['job_title']) ?><?= $r['branch_name'] ? ' - ' . htmlspecialchars($r['branch_name']) : '' ?></div>
              </td>
              <td><?= htmlspecialchars($r['template_title']) ?></td>
              <td>
                <?php
                  $st = $r['status'];
                  $map = [
                    'pending' => ['بانتظار إجابة الموظف', '#F59E0B'],
                    'submitted' => ['بانتظار اعتماد الإدارة', '#3B82F6'],
                    'approved' => ['مقبول', '#10B981'],
                    'rejected' => ['مرفوض/مطلوب متابعة', '#EF4444'],
                  ];
                ?>
                <span style="font-weight:700;color:<?= $map[$st][1] ?? '#334155' ?>"><?= $map[$st][0] ?? $st ?></span>
              </td>
              <td><?= $r['submitted_at'] ? htmlspecialchars($r['submitted_at']) : '—' ?></td>
              <td>
                <?= $r['reviewed_at'] ? htmlspecialchars($r['reviewed_at']) : '—' ?>
                <?php if ($r['reviewed_by_name']): ?>
                  <div style="font-size:.75rem;color:#64748b">بواسطة: <?= htmlspecialchars($r['reviewed_by_name']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($r['status'] === 'submitted'): ?>
                  <form method="post" style="display:inline-block">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="review_assignment">
                    <input type="hidden" name="assignment_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="status" value="approved">
                    <input type="hidden" name="review_notes" value="تم اعتماد التقرير">
                    <button class="btn btn-sm btn-primary" type="submit">اعتماد</button>
                  </form>

                  <form method="post" style="display:inline-block">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="review_assignment">
                    <input type="hidden" name="assignment_id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="review_notes" value="تم رفض التقرير، مطلوب استجواب إضافي">
                    <button class="btn btn-sm btn-danger" type="submit">رفض</button>
                  </form>

                  <details style="margin-top:6px">
                    <summary style="cursor:pointer;color:#0f766e;font-weight:700">إرسال استجواب إضافي</summary>
                    <form method="post" style="margin-top:6px">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                      <input type="hidden" name="action" value="send_followup">
                      <input type="hidden" name="assignment_id" value="<?= (int)$r['id'] ?>">
                      <select class="form-control form-control-sm" name="template_id" required>
                        <?php foreach ($templates as $t): ?>
                          <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['title']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <textarea class="form-control form-control-sm" name="followup_notes" rows="2" placeholder="تعليمات إضافية للموظف"></textarea>
                      <button class="btn btn-sm btn-outline" type="submit" style="margin-top:6px">إرسال متابعة</button>
                    </form>
                  </details>
                <?php else: ?>
                  <span style="color:#94A3B8">لا يوجد</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php if (!empty($r['final_report'])): ?>
              <tr>
                <td></td>
                <td colspan="6" style="background:#f8fafc">
                  <strong>التقرير النهائي:</strong>
                  <div style="white-space:pre-wrap;line-height:1.7;margin-top:4px"><?= htmlspecialchars($r['final_report']) ?></div>
                </td>
              </tr>
            <?php endif; ?>
            <?php if (!empty($r['admin_notes'])): ?>
              <tr>
                <td></td>
                <td colspan="6" style="background:#fff7ed">
                  <strong>ملاحظات الإدارة:</strong>
                  <div style="white-space:pre-wrap;line-height:1.7;margin-top:4px"><?= htmlspecialchars($r['admin_notes']) ?></div>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function(){
  const target = document.getElementById('targetType');
  const empWrap = document.getElementById('empWrap');
  const branchWrap = document.getElementById('branchWrap');

  function toggle() {
    const v = target.value;
    empWrap.style.display = v === 'employees' ? '' : 'none';
    branchWrap.style.display = v === 'branch' ? '' : 'none';
  }

  target.addEventListener('change', toggle);
  toggle();
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
