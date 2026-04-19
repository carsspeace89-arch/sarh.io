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

function mi_collect_questions_from_post(): array
{
    $texts = $_POST['question_text'] ?? [];
    $types = $_POST['question_type'] ?? [];
    $optionsList = $_POST['question_options'] ?? [];

    if (!is_array($texts)) {
        return [];
    }

    $result = [];
    foreach ($texts as $i => $text) {
        $text = trim((string)$text);
        if ($text === '') {
            continue;
        }

        $type = (string)($types[$i] ?? 'text');
        $type = in_array($type, ['text', 'options'], true) ? $type : 'text';

        $options = [];
        if ($type === 'options') {
            $raw = trim((string)($optionsList[$i] ?? ''));
            if ($raw !== '') {
                $split = preg_split('/\r\n|\r|\n|\|/', $raw) ?: [];
                foreach ($split as $opt) {
                    $opt = trim($opt);
                    if ($opt !== '') {
                        $options[] = $opt;
                    }
                }
            }
        }

        $result[] = [
            'question' => $text,
            'type' => $type,
            'options' => $options,
        ];
    }

    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $flash = ['type' => 'error', 'text' => 'طلب غير صالح (CSRF).'];
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'create_template') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $questions = mi_collect_questions_from_post();
                $id = mi_create_template($title, $questions, $description, (int)$_SESSION['admin_id']);
                auditLog('mandatory_template_create', "إنشاء نموذج استجواب #{$id}", $id);
                $flash = ['type' => 'success', 'text' => 'تم إنشاء النموذج بنجاح.'];
            }

            if ($action === 'update_template') {
                $templateId = (int)($_POST['template_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $questions = mi_collect_questions_from_post();

                if (mi_update_template($templateId, $title, $questions, $description, (int)$_SESSION['admin_id'])) {
                    auditLog('mandatory_template_update', "تعديل نموذج استجواب #{$templateId}", $templateId);
                    $flash = ['type' => 'success', 'text' => 'تم تعديل النموذج بنجاح.'];
                } else {
                    $flash = ['type' => 'error', 'text' => 'تعذر تعديل النموذج.'];
                }
            }

            if ($action === 'delete_template') {
                $templateId = (int)($_POST['template_id'] ?? 0);
                if (mi_delete_template($templateId)) {
                    auditLog('mandatory_template_delete', "تعطيل/حذف نموذج استجواب #{$templateId}", $templateId);
                    $flash = ['type' => 'success', 'text' => 'تم حذف/تعطيل النموذج بنجاح.'];
                } else {
                    $flash = ['type' => 'error', 'text' => 'تعذر حذف النموذج.'];
                }
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
foreach ($templates as &$t) {
    $t['question_defs'] = mi_normalize_questions(json_decode((string)$t['questions_json'], true) ?: []);
}
unset($t);

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
    <p>تعيين نماذج مكتوبة وإيقاف الحضور حتى مراجعة الإدارة.</p>
  </div>
</div>

<?php if ($flash): ?>
  <div class="card" style="margin-bottom:14px;border-right:4px solid <?= $flash['type']==='success' ? '#10B981' : '#EF4444' ?>">
    <div class="card-body" style="padding:12px 14px;font-weight:700"><?= htmlspecialchars($flash['text']) ?></div>
  </div>
<?php endif; ?>

<div class="card" style="margin-bottom:14px">
  <div class="card-body" style="display:flex;gap:10px;align-items:center;padding:12px 14px;background:#0b1220;color:#fff;border-radius:10px">
    <img src="<?= SITE_URL ?>/assets/images/loogo.png" alt="logo" style="width:34px;height:34px;border-radius:8px;object-fit:cover">
    <div style="font-weight:700">وحدة الاستجواب الإلزامي</div>
  </div>
</div>

<div class="card" style="margin-bottom:14px">
  <div class="card-header"><h3>➕ إنشاء نموذج جديد</h3></div>
  <div class="card-body">
    <form method="post" class="template-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
      <input type="hidden" name="action" value="create_template">
      <div class="form-group">
        <label class="form-label">عنوان النموذج</label>
        <input class="form-control" name="title" required>
      </div>
      <div class="form-group">
        <label class="form-label">وصف</label>
        <textarea class="form-control" name="description" rows="2"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">الأسئلة</label>
        <div class="q-builder"></div>
        <button type="button" class="btn btn-sm btn-outline add-q">+ إضافة سؤال</button>
      </div>
      <button class="btn btn-primary" type="submit">حفظ النموذج</button>
    </form>
  </div>
</div>

<div class="card" style="margin-bottom:14px">
  <div class="card-header"><h3>🛠 تعديل/حذف النماذج الحالية</h3></div>
  <div class="card-body">
    <?php if (!$templates): ?>
      <div>لا توجد نماذج.</div>
    <?php else: ?>
      <?php foreach ($templates as $tpl): ?>
        <details style="border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin-bottom:10px" <?= (int)$tpl['is_default'] === 1 ? 'open' : '' ?>>
          <summary style="cursor:pointer;font-weight:700">
            <?= htmlspecialchars($tpl['title']) ?>
            <?php if (!(int)$tpl['is_active']): ?><span style="color:#ef4444">(معطل)</span><?php endif; ?>
          </summary>

          <form method="post" class="template-form" style="margin-top:10px">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="action" value="update_template">
            <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">

            <div class="form-group">
              <label class="form-label">العنوان</label>
              <input class="form-control" name="title" value="<?= htmlspecialchars($tpl['title']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">الوصف</label>
              <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars((string)$tpl['description']) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">الأسئلة</label>
              <div class="q-builder">
                <?php foreach ($tpl['question_defs'] as $q): ?>
                  <div class="q-row" style="display:grid;grid-template-columns:1fr 160px 1fr auto;gap:6px;margin-bottom:6px">
                    <input class="form-control" name="question_text[]" value="<?= htmlspecialchars((string)$q['question']) ?>" placeholder="نص السؤال" required>
                    <select class="form-control q-type" name="question_type[]">
                      <option value="text" <?= $q['type'] === 'text' ? 'selected' : '' ?>>نصي</option>
                      <option value="options" <?= $q['type'] === 'options' ? 'selected' : '' ?>>خيارات</option>
                    </select>
                    <input class="form-control q-options" name="question_options[]" value="<?= htmlspecialchars(implode('|', $q['options'] ?? [])) ?>" placeholder="خيار1|خيار2|خيار3" <?= $q['type'] === 'options' ? '' : 'style="display:none"' ?>>
                    <button type="button" class="btn btn-sm btn-danger del-q">حذف</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="btn btn-sm btn-outline add-q">+ إضافة سؤال</button>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">حفظ التعديلات</button>
          </form>

          <form method="post" onsubmit="return confirm('تأكيد حذف/تعطيل النموذج؟');" style="margin-top:8px">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="action" value="delete_template">
            <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit">حذف/تعطيل النموذج</button>
          </form>
        </details>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="margin-bottom:14px">
  <div class="card-header"><h3>📌 تعيين نموذج إجباري</h3></div>
  <div class="card-body">
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
      <input type="hidden" name="action" value="assign_template">

      <div class="form-group">
        <label class="form-label">اختر النموذج</label>
        <select class="form-control" name="template_id" required>
          <?php foreach ($templates as $t): ?>
            <?php if (!(int)$t['is_active']) continue; ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['title']) ?></option>
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
        <label class="form-label">اختر الموظفين</label>
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
        <label class="form-label">سبب تجميد الحساب (يظهر للموظف قبل النموذج)</label>
        <textarea class="form-control" name="assignment_notes" rows="2" placeholder="مثال: تم تجميد الحساب بسبب عدم تقديم تقرير الاستلام الشهري"></textarea>
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
              <td><strong><?= htmlspecialchars($r['employee_name']) ?></strong><div style="font-size:.78rem;color:#64748b"><?= htmlspecialchars((string)$r['job_title']) ?></div></td>
              <td><?= htmlspecialchars($r['template_title']) ?></td>
              <td><?= htmlspecialchars($r['status']) ?></td>
              <td><?= $r['submitted_at'] ? htmlspecialchars($r['submitted_at']) : '—' ?></td>
              <td><?= $r['reviewed_at'] ? htmlspecialchars($r['reviewed_at']) : '—' ?></td>
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
                    <input type="hidden" name="review_notes" value="تم رفض التقرير">
                    <button class="btn btn-sm btn-danger" type="submit">رفض</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  const target = document.getElementById('targetType');
  const empWrap = document.getElementById('empWrap');
  const branchWrap = document.getElementById('branchWrap');

  function toggleTarget() {
    const v = target.value;
    empWrap.style.display = v === 'employees' ? '' : 'none';
    branchWrap.style.display = v === 'branch' ? '' : 'none';
  }

  if (target) {
    target.addEventListener('change', toggleTarget);
    toggleTarget();
  }

  function makeQuestionRow(prefill) {
    const row = document.createElement('div');
    row.className = 'q-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 160px 1fr auto;gap:6px;margin-bottom:6px';

    row.innerHTML = '' +
      '<input class="form-control" name="question_text[]" placeholder="نص السؤال" required>' +
      '<select class="form-control q-type" name="question_type[]"><option value="text">نصي</option><option value="options">خيارات</option></select>' +
      '<input class="form-control q-options" name="question_options[]" placeholder="خيار1|خيار2|خيار3" style="display:none">' +
      '<button type="button" class="btn btn-sm btn-danger del-q">حذف</button>';

    const text = row.querySelector('input[name="question_text[]"]');
    const type = row.querySelector('.q-type');
    const opts = row.querySelector('.q-options');

    if (prefill) {
      text.value = prefill.text || '';
      type.value = prefill.type || 'text';
      opts.value = prefill.options || '';
    }

    function syncType() {
      opts.style.display = type.value === 'options' ? '' : 'none';
    }

    type.addEventListener('change', syncType);
    syncType();

    row.querySelector('.del-q').addEventListener('click', function(){
      row.remove();
    });

    return row;
  }

  document.querySelectorAll('.template-form').forEach(function(form){
    const builder = form.querySelector('.q-builder');
    const addBtn = form.querySelector('.add-q');

    if (!builder || !addBtn) return;

    if (builder.querySelectorAll('.q-row').length === 0) {
      builder.appendChild(makeQuestionRow());
    }

    addBtn.addEventListener('click', function(){
      builder.appendChild(makeQuestionRow());
    });

    builder.querySelectorAll('.q-row').forEach(function(existingRow){
      const type = existingRow.querySelector('.q-type');
      const opts = existingRow.querySelector('.q-options');
      if (type && opts) {
        const syncType = function(){ opts.style.display = type.value === 'options' ? '' : 'none'; };
        type.addEventListener('change', syncType);
        syncType();
      }
      const del = existingRow.querySelector('.del-q');
      if (del) {
        del.addEventListener('click', function(){ existingRow.remove(); });
      }
    });
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
