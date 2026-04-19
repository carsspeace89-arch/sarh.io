<?php
// =============================================================
// employee/mandatory-interrogation.php
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mandatory_interrogation.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    header('Location: ' . SITE_URL . '/employee/index.php');
    exit;
}

$employee = getEmployeeByToken($token);
if (!$employee) {
    http_response_code(403);
    echo 'رابط غير صالح أو منتهي الصلاحية';
    exit;
}

mi_ensure_tables();
$assignment = mi_get_blocking_assignment((int)$employee['id']);

if (!$assignment) {
    header('Location: ' . SITE_URL . '/employee/attendance.php?token=' . urlencode($token));
    exit;
}

$questions = mi_get_questions($assignment);
if (!$questions) {
    $questions = ['اكتب تقريرك المفصل بخصوص الموضوع المطلوب.'];
}

$submitted = false;
$error = '';
$isAwaitingReview = (($assignment['status'] ?? '') === 'submitted');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $finalReport = trim($_POST['final_report'] ?? '');

    if (!is_array($answers)) {
        $answers = [];
    }

    foreach ($questions as $idx => $q) {
        if (trim((string)($answers[$idx] ?? '')) === '') {
            $error = 'يجب الإجابة على كل الأسئلة قبل الإرسال.';
            break;
        }
    }

    if ($error === '' && $finalReport === '') {
        $error = 'يرجى كتابة التقرير النهائي.';
    }

    if ($error === '') {
        $ok = mi_submit_assignment((int)$assignment['id'], (int)$employee['id'], $answers, $finalReport);
        if ($ok) {
            $submitted = true;
            $assignment = mi_get_blocking_assignment((int)$employee['id']);
        } else {
            $error = 'تعذر حفظ التقرير. حاول مرة أخرى.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>نموذج استجواب إلزامي</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/tajawal.css">
  <style>
    body{font-family:'Tajawal',sans-serif;background:#f4f7fb;margin:0;color:#0f172a}
    .wrap{max-width:760px;margin:0 auto;padding:18px}
    .card{background:#fff;border-radius:16px;box-shadow:0 10px 35px rgba(2,6,23,.08);padding:18px}
    .title{font-size:1.4rem;font-weight:800;margin:0 0 8px}
    .muted{color:#64748b;font-size:.95rem}
    .warn{background:#fff7ed;border:1px solid #fdba74;color:#9a3412;border-radius:12px;padding:10px 12px;margin:12px 0}
    .ok{background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;border-radius:12px;padding:10px 12px;margin:12px 0}
    .slide{display:none}
    .slide.active{display:block}
    .q{font-size:1.08rem;font-weight:700;margin-bottom:10px}
    textarea{width:100%;min-height:130px;border:1px solid #cbd5e1;border-radius:12px;padding:12px;font-family:inherit;font-size:1rem;box-sizing:border-box}
    .steps{display:flex;gap:6px;margin:12px 0;flex-wrap:wrap}
    .dot{width:24px;height:6px;border-radius:99px;background:#e2e8f0}
    .dot.on{background:#f97316}
    .actions{display:flex;gap:8px;justify-content:space-between;margin-top:14px}
    button{border:0;border-radius:10px;padding:10px 16px;font-family:inherit;cursor:pointer}
    .btn{background:#f97316;color:#fff;font-weight:700}
    .btn2{background:#e2e8f0;color:#1e293b}
    .btn3{background:#0f766e;color:#fff;font-weight:700}
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1 class="title">نموذج استجواب إلزامي قبل تسجيل الحضور</h1>
    <p class="muted">الموظف: <strong><?= htmlspecialchars($employee['name']) ?></strong></p>

    <?php if (!empty($assignment['admin_notes'])): ?>
      <div class="warn">ملاحظة الإدارة: <?= nl2br(htmlspecialchars($assignment['admin_notes'])) ?></div>
    <?php endif; ?>

    <?php if ($submitted || $isAwaitingReview): ?>
      <div class="ok">تم إرسال تقريرك بنجاح. سيتم مراجعته من الإدارة، وحسابك متوقف عن تسجيل الحضور حتى اعتماد التقرير.</div>
    <?php else: ?>
      <?php if ($error !== ''): ?>
        <div class="warn"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" id="miForm">
        <div class="steps" id="steps"></div>

        <?php foreach ($questions as $i => $q): ?>
          <section class="slide <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>">
            <div class="q">السؤال <?= $i + 1 ?>: <?= htmlspecialchars($q) ?></div>
            <textarea name="answers[<?= $i ?>]" placeholder="اكتب إجابتك هنا..." required><?= htmlspecialchars((string)($_POST['answers'][$i] ?? '')) ?></textarea>
          </section>
        <?php endforeach; ?>

        <section class="slide" data-idx="<?= count($questions) ?>">
          <div class="q">المراجعة النهائية والتقرير الكامل</div>
          <p class="muted">راجع إجاباتك، ثم اكتب خلاصة التقرير النهائي قبل الإرسال.</p>
          <textarea name="final_report" placeholder="اكتب التقرير النهائي هنا..." required><?= htmlspecialchars((string)($_POST['final_report'] ?? '')) ?></textarea>
        </section>

        <div class="actions">
          <button type="button" class="btn2" id="prevBtn">السابق</button>
          <button type="button" class="btn" id="nextBtn">التالي</button>
          <button type="submit" class="btn3" id="sendBtn" style="display:none">إرسال كامل للإدارة</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if (!$submitted): ?>
<script>
(function(){
  const slides = Array.from(document.querySelectorAll('.slide'));
  const steps = document.getElementById('steps');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const sendBtn = document.getElementById('sendBtn');
  let idx = 0;

  slides.forEach((_, i) => {
    const d = document.createElement('div');
    d.className = 'dot' + (i === 0 ? ' on' : '');
    steps.appendChild(d);
  });

  function setSlide(n){
    idx = Math.max(0, Math.min(slides.length - 1, n));
    slides.forEach((s, i) => s.classList.toggle('active', i === idx));
    Array.from(steps.children).forEach((d, i) => d.classList.toggle('on', i <= idx));
    prevBtn.style.visibility = idx === 0 ? 'hidden' : 'visible';
    nextBtn.style.display = idx === slides.length - 1 ? 'none' : 'inline-block';
    sendBtn.style.display = idx === slides.length - 1 ? 'inline-block' : 'none';
  }

  function validateCurrent(){
    const active = slides[idx];
    if (!active) return true;
    const ta = active.querySelector('textarea');
    if (!ta) return true;
    if (!ta.value.trim()) {
      ta.focus();
      return false;
    }
    return true;
  }

  prevBtn.addEventListener('click', () => setSlide(idx - 1));
  nextBtn.addEventListener('click', () => {
    if (!validateCurrent()) return;
    setSlide(idx + 1);
  });

  document.getElementById('miForm').addEventListener('submit', function(e){
    if (!validateCurrent()) {
      e.preventDefault();
    }
  });

  setSlide(0);
})();
</script>
<?php endif; ?>
</body>
</html>
