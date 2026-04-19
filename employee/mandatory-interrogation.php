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

$questionDefs = mi_get_questions($assignment);
if (!$questionDefs) {
    $questionDefs = [
        ['question' => 'اكتب تقريرك المفصل بخصوص الموضوع المطلوب.', 'type' => 'text', 'options' => []],
    ];
}

$submitted = false;
$error = '';
$status = (string)($assignment['status'] ?? 'pending');
$isAwaitingReview = ($status === 'submitted');
$showFreezeIntro = !$isAwaitingReview && !isset($_GET['start']);
$freezeReason = trim((string)($assignment['admin_notes'] ?? ''));
if ($freezeReason === '') {
    $freezeReason = 'تم تجميد حسابك مؤقتاً لوجود استجواب إلزامي مطلوب قبل تسجيل الحضور.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isAwaitingReview) {
    $answers = $_POST['answers'] ?? [];
    $finalReport = trim($_POST['final_report'] ?? '');

    if (!is_array($answers)) {
        $answers = [];
    }

    foreach ($questionDefs as $idx => $qDef) {
        $type = (string)($qDef['type'] ?? 'text');
        $answer = trim((string)($answers[$idx] ?? ''));

        if ($type === 'options') {
            $opts = is_array($qDef['options'] ?? null) ? $qDef['options'] : [];
            if ($answer === '' || !in_array($answer, $opts, true)) {
                $error = 'يرجى اختيار إجابة لكل سؤال من نوع الخيارات.';
                break;
            }
        } else {
            if ($answer === '') {
                $error = 'يجب الإجابة على كل الأسئلة قبل الإرسال.';
                break;
            }
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
            $isAwaitingReview = true;
            $showFreezeIntro = false;
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
  <title>الاستجواب الإلزامي</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/tajawal.css">
  <style>
    body{font-family:'Tajawal',sans-serif;background:radial-gradient(circle at top,#3f0000,#120101 60%,#090000);margin:0;color:#f8fafc;min-height:100vh}
    .wrap{max-width:860px;margin:0 auto;padding:18px}
    .card{background:rgba(9,6,6,.92);border:1px solid #7f1d1d;border-radius:16px;box-shadow:0 18px 45px rgba(0,0,0,.45);padding:18px}
    .logo{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .logo img{width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #991b1b}
    .title{font-size:1.35rem;font-weight:900;margin:0}
    .muted{color:#fecaca;font-size:.94rem}
    .danger-box{background:linear-gradient(135deg,#7f1d1d,#450a0a);border:1px solid #ef4444;border-radius:14px;padding:14px;margin:12px 0}
    .danger-title{font-size:1.15rem;font-weight:900;color:#fee2e2;margin-bottom:8px}
    .warn{background:#3f1010;border:1px solid #ef4444;color:#ffe4e6;border-radius:12px;padding:10px 12px;margin:12px 0}
    .ok{background:#042f2e;border:1px solid #14b8a6;color:#ccfbf1;border-radius:12px;padding:10px 12px;margin:12px 0}
    .slide{display:none}
    .slide.active{display:block}
    .q{font-size:1.05rem;font-weight:800;margin-bottom:10px;color:#fff}
    textarea{width:100%;min-height:130px;border:1px solid #7f1d1d;border-radius:12px;padding:12px;font-family:inherit;font-size:1rem;box-sizing:border-box;background:#1a0a0a;color:#fff}
    .steps{display:flex;gap:6px;margin:12px 0;flex-wrap:wrap}
    .dot{width:24px;height:6px;border-radius:99px;background:#3f3f46}
    .dot.on{background:#ef4444}
    .actions{display:flex;gap:8px;justify-content:space-between;margin-top:14px;flex-wrap:wrap}
    button,.btn-link{border:0;border-radius:10px;padding:10px 16px;font-family:inherit;cursor:pointer;text-decoration:none;display:inline-block}
    .btn{background:#dc2626;color:#fff;font-weight:800}
    .btn2{background:#374151;color:#fff}
    .btn3{background:#991b1b;color:#fff;font-weight:900}
    .opts{display:grid;gap:8px}
    .opt{display:flex;align-items:center;gap:8px;background:#1a0a0a;border:1px solid #7f1d1d;border-radius:10px;padding:9px}
    .center{display:flex;justify-content:center}
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="logo">
      <img src="<?= SITE_URL ?>/assets/images/loogo.png" alt="logo">
      <div>
        <h1 class="title">الاستجواب الإلزامي قبل تسجيل الحضور</h1>
        <div class="muted">الموظف: <strong><?= htmlspecialchars($employee['name']) ?></strong></div>
      </div>
    </div>

    <?php if ($submitted || $isAwaitingReview): ?>
      <div class="ok">تم إرسال تقريرك بنجاح. سيتم مراجعته من الإدارة. حسابك سيبقى مجمداً حتى اعتماد التقرير.</div>
    <?php elseif ($showFreezeIntro): ?>
      <div class="danger-box">
        <div class="danger-title">⛔ تم تجميد حسابك مؤقتاً</div>
        <div style="line-height:1.8;color:#ffe4e6">بسبب: <?= nl2br(htmlspecialchars($freezeReason)) ?></div>
        <div style="margin-top:10px;line-height:1.8;color:#fecaca">لا يمكن تسجيل الحضور حالياً إلا بعد إكمال النموذج وإرساله للإدارة للمراجعة.</div>
      </div>
      <div class="center" style="margin-top:12px">
        <a class="btn-link btn3" href="?token=<?= urlencode($token) ?>&start=1">بدء الاستجواب الآن</a>
      </div>
    <?php else: ?>
      <?php if ($error !== ''): ?>
        <div class="warn"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($assignment['admin_notes'])): ?>
        <div class="warn">سبب التجميد: <?= nl2br(htmlspecialchars($assignment['admin_notes'])) ?></div>
      <?php endif; ?>

      <form method="post" id="miForm">
        <div class="steps" id="steps"></div>

        <?php foreach ($questionDefs as $i => $q): ?>
          <?php $type = (string)($q['type'] ?? 'text'); ?>
          <section class="slide <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>">
            <div class="q">السؤال <?= $i + 1 ?>: <?= htmlspecialchars((string)$q['question']) ?></div>

            <?php if ($type === 'options' && !empty($q['options']) && is_array($q['options'])): ?>
              <div class="opts">
                <?php foreach ($q['options'] as $opt): ?>
                  <label class="opt">
                    <input type="radio" name="answers[<?= $i ?>]" value="<?= htmlspecialchars((string)$opt) ?>" <?= ((string)($_POST['answers'][$i] ?? '') === (string)$opt) ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars((string)$opt) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <textarea name="answers[<?= $i ?>]" placeholder="اكتب إجابتك هنا..." required><?= htmlspecialchars((string)($_POST['answers'][$i] ?? '')) ?></textarea>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>

        <section class="slide" data-idx="<?= count($questionDefs) ?>">
          <div class="q">المراجعة النهائية والتقرير الكامل</div>
          <p class="muted">راجع إجاباتك، ثم اكتب التقرير النهائي قبل الإرسال.</p>
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

<?php if (!$submitted && !$isAwaitingReview && !$showFreezeIntro): ?>
<script>
(function(){
  const slides = Array.from(document.querySelectorAll('.slide'));
  const steps = document.getElementById('steps');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const sendBtn = document.getElementById('sendBtn');
  const form = document.getElementById('miForm');
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

    const text = active.querySelector('textarea');
    if (text) {
      if (!text.value.trim()) {
        text.focus();
        return false;
      }
      return true;
    }

    const radios = active.querySelectorAll('input[type="radio"]');
    if (radios.length > 0) {
      const checked = active.querySelector('input[type="radio"]:checked');
      if (!checked) {
        return false;
      }
      return true;
    }

    return true;
  }

  prevBtn.addEventListener('click', () => setSlide(idx - 1));
  nextBtn.addEventListener('click', () => {
    if (!validateCurrent()) return;
    setSlide(idx + 1);
  });

  form.addEventListener('submit', function(e){
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
