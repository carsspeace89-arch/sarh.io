<?php
// =============================================================
// admin/audio-library.php - المكتبة الصوتية
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'المكتبة الصوتية';
$activePage = 'audio-library';
$message    = '';
$msgType    = '';

$uploadDir = __DIR__ . '/../assets/audio/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/aac', 'audio/mp4'];
$allowedExts  = ['mp3', 'wav', 'ogg', 'webm', 'aac', 'm4a'];

$categories = [
    'geofence_enter'   => ['label' => '🎵 دخول النطاق', 'desc' => 'يُشغّل عند دخول الموظف نطاق العمل'],
    'geofence_exit'    => ['label' => '🔔 خروج النطاق', 'desc' => 'يُشغّل عند خروج الموظف من النطاق'],
    'checkin_success'   => ['label' => '✅ نجاح تسجيل الحضور', 'desc' => 'يُشغّل بعد تسجيل الحضور بنجاح'],
    'checkout_success'  => ['label' => '☑️ نجاح تسجيل الانصراف', 'desc' => 'يُشغّل بعد تسجيل الانصراف'],
    'notification'      => ['label' => '📢 إشعار', 'desc' => 'صوت الإشعارات'],
    'custom'            => ['label' => '📂 عام', 'desc' => 'مقاطع صوتية عامة'],
];

// =================== إجراءات POST ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'طلب غير صالح';
        $msgType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        // ── Upload new audio ──
        if ($action === 'upload') {
            $title    = sanitize($_POST['title'] ?? '');
            $category = sanitize($_POST['category'] ?? 'custom');
            $playMode = ($_POST['play_mode'] ?? 'once') === 'loop' ? 'loop' : 'once';
            $volume   = max(0, min(1, (float)($_POST['volume'] ?? 1)));

            if (!$title) {
                $message = 'أدخل اسم المقطع الصوتي';
                $msgType = 'error';
            } elseif (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
                $message = 'اختر ملف صوتي للرفع';
                $msgType = 'error';
            } else {
                $file = $_FILES['audio_file'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if ($file['size'] > $maxFileSize) {
                    $message = 'حجم الملف يتجاوز 10 ميجابايت';
                    $msgType = 'error';
                } elseif (!in_array($ext, $allowedExts)) {
                    $message = 'نوع الملف غير مدعوم. الأنواع المدعومة: ' . implode(', ', $allowedExts);
                    $msgType = 'error';
                } else {
                    $safeName = 'audio_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destPath = $uploadDir . $safeName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $stmt = db()->prepare("INSERT INTO audio_library (title, filename, original_name, file_size, category, play_mode, volume, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $safeName, $file['name'], $file['size'], $category, $playMode, $volume, $_SESSION['admin_id'] ?? null]);
                        auditLog('upload_audio', "رفع ملف صوتي: {$title} ({$safeName})");
                        $message = "تم رفع الملف الصوتي بنجاح";
                        $msgType = 'success';
                    } else {
                        $message = 'فشل في رفع الملف';
                        $msgType = 'error';
                    }
                }
            }
        }

        // ── Activate audio for a category ──
        if ($action === 'activate') {
            $id = (int)($_POST['audio_id'] ?? 0);
            if ($id) {
                $audio = db()->prepare("SELECT id, category FROM audio_library WHERE id = ?");
                $audio->execute([$id]);
                $audio = $audio->fetch();
                if ($audio) {
                    // Deactivate all others in same category
                    db()->prepare("UPDATE audio_library SET is_active = 0 WHERE category = ?")->execute([$audio['category']]);
                    // Activate this one
                    db()->prepare("UPDATE audio_library SET is_active = 1 WHERE id = ?")->execute([$id]);
                    auditLog('activate_audio', "تفعيل مقطع صوتي #{$id} للتصنيف: {$audio['category']}");
                    $message = 'تم تفعيل المقطع الصوتي';
                    $msgType = 'success';
                }
            }
        }

        // ── Deactivate audio ──
        if ($action === 'deactivate') {
            $id = (int)($_POST['audio_id'] ?? 0);
            if ($id) {
                db()->prepare("UPDATE audio_library SET is_active = 0 WHERE id = ?")->execute([$id]);
                auditLog('deactivate_audio', "إلغاء تفعيل مقطع صوتي #{$id}");
                $message = 'تم إلغاء التفعيل';
                $msgType = 'success';
            }
        }

        // ── Update audio settings ──
        if ($action === 'update') {
            $id       = (int)($_POST['audio_id'] ?? 0);
            $title    = sanitize($_POST['title'] ?? '');
            $category = sanitize($_POST['category'] ?? 'custom');
            $playMode = ($_POST['play_mode'] ?? 'once') === 'loop' ? 'loop' : 'once';
            $volume   = max(0, min(1, (float)($_POST['volume'] ?? 1)));
            if ($id && $title) {
                db()->prepare("UPDATE audio_library SET title=?, category=?, play_mode=?, volume=? WHERE id=?")
                    ->execute([$title, $category, $playMode, $volume, $id]);
                auditLog('update_audio', "تحديث إعدادات مقطع صوتي #{$id}: {$title}");
                $message = 'تم تحديث الإعدادات';
                $msgType = 'success';
            }
        }

        // ── Delete audio ──
        if ($action === 'delete') {
            $id = (int)($_POST['audio_id'] ?? 0);
            if ($id) {
                $audio = db()->prepare("SELECT filename FROM audio_library WHERE id = ?");
                $audio->execute([$id]);
                $audio = $audio->fetch();
                if ($audio) {
                    $filePath = $uploadDir . $audio['filename'];
                    if (file_exists($filePath)) @unlink($filePath);
                    db()->prepare("DELETE FROM audio_library WHERE id = ?")->execute([$id]);
                    auditLog('delete_audio', "حذف ملف صوتي #{$id}: {$audio['filename']}");
                    $message = 'تم حذف الملف الصوتي';
                    $msgType = 'success';
                }
            }
        }
    }
}

// ── Fetch all audio files ──
$audioFiles = db()->query("SELECT * FROM audio_library ORDER BY category, is_active DESC, created_at DESC")->fetchAll();

// Group by category
$grouped = [];
foreach ($audioFiles as $af) {
    $grouped[$af['category']][] = $af;
}

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<div class="content fade-in">

<?php if ($message): ?>
<div class="alert alert-<?= $msgType === 'success' ? 'success' : 'danger' ?>" style="margin-bottom:20px;padding:14px 18px;border-radius:12px;font-size:.88rem;font-weight:600;
  <?= $msgType === 'success' ? 'background:rgba(34,197,94,.1);color:#16A34A;border:1px solid rgba(34,197,94,.2)' : 'background:rgba(239,68,68,.1);color:#DC2626;border:1px solid rgba(239,68,68,.2)' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px">
    <div style="background:var(--card-bg,#fff);border-radius:14px;padding:16px;text-align:center;border:1px solid var(--border-color,rgba(0,0,0,.06))">
        <div style="font-size:1.8rem;font-weight:800;color:var(--primary,#F97316)"><?= count($audioFiles) ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary,#64748B);font-weight:600">إجمالي المقاطع</div>
    </div>
    <div style="background:var(--card-bg,#fff);border-radius:14px;padding:16px;text-align:center;border:1px solid var(--border-color,rgba(0,0,0,.06))">
        <div style="font-size:1.8rem;font-weight:800;color:#16A34A"><?= count(array_filter($audioFiles, fn($a) => $a['is_active'])) ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary,#64748B);font-weight:600">مقاطع مفعّلة</div>
    </div>
    <div style="background:var(--card-bg,#fff);border-radius:14px;padding:16px;text-align:center;border:1px solid var(--border-color,rgba(0,0,0,.06))">
        <div style="font-size:1.8rem;font-weight:800;color:#3B82F6"><?= count($categories) ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary,#64748B);font-weight:600">تصنيفات</div>
    </div>
</div>

<!-- Upload Form -->
<div style="background:var(--card-bg,#fff);border-radius:16px;padding:20px;margin-bottom:24px;border:1px solid var(--border-color,rgba(0,0,0,.06))">
    <div style="font-size:1rem;font-weight:800;color:var(--text-primary,#1E293B);margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span style="font-size:1.3rem">🎵</span> رفع ملف صوتي جديد
    </div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="action" value="upload">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">اسم المقطع *</label>
                <input type="text" name="title" required placeholder="مثال: نغمة الدخول"
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">التصنيف *</label>
                <select name="category" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
                    <?php foreach ($categories as $catKey => $cat): ?>
                        <option value="<?= $catKey ?>"><?= $cat['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">طريقة التشغيل</label>
                <select name="play_mode" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
                    <option value="once">▶ مرة واحدة</option>
                    <option value="loop">🔁 تكرار</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">مستوى الصوت</label>
                <input type="range" name="volume" min="0" max="1" step="0.05" value="1" id="uploadVolume"
                    style="width:100%;margin-top:8px" oninput="document.getElementById('volLabel').textContent=Math.round(this.value*100)+'%'">
                <span id="volLabel" style="font-size:.68rem;color:var(--text-secondary,#94A3B8)">100%</span>
            </div>
            <div>
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">الملف الصوتي * (MP3, WAV, OGG — حتى 10MB)</label>
                <input type="file" name="audio_file" accept=".mp3,.wav,.ogg,.webm,.aac,.m4a,audio/*" required
                    style="width:100%;padding:8px;border:1.5px dashed var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.82rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
            </div>
        </div>

        <button type="submit" style="padding:10px 28px;border:none;border-radius:10px;background:linear-gradient(135deg,#F97316,#EA580C);color:#fff;font-size:.88rem;font-weight:700;font-family:inherit;cursor:pointer;transition:transform .15s">
            📤 رفع المقطع
        </button>
    </form>
</div>

<!-- Audio Library by Category -->
<?php foreach ($categories as $catKey => $catInfo): ?>
<div style="background:var(--card-bg,#fff);border-radius:16px;padding:18px;margin-bottom:16px;border:1px solid var(--border-color,rgba(0,0,0,.06))">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div>
            <div style="font-size:.95rem;font-weight:800;color:var(--text-primary,#1E293B)"><?= $catInfo['label'] ?></div>
            <div style="font-size:.7rem;color:var(--text-secondary,#94A3B8);margin-top:2px"><?= $catInfo['desc'] ?></div>
        </div>
        <?php
            $activeInCat = array_filter($grouped[$catKey] ?? [], fn($a) => $a['is_active']);
            $activeOne = $activeInCat ? array_values($activeInCat)[0] : null;
        ?>
        <?php if ($activeOne): ?>
            <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(34,197,94,.1);color:#16A34A;padding:4px 12px;border-radius:8px;font-size:.72rem;font-weight:700;border:1px solid rgba(34,197,94,.2)">
                🔊 <?= htmlspecialchars($activeOne['title']) ?>
            </span>
        <?php else: ?>
            <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(148,163,184,.08);color:#94A3B8;padding:4px 12px;border-radius:8px;font-size:.72rem;font-weight:600">
                🔇 لا يوجد مقطع مفعّل
            </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($grouped[$catKey])): ?>
        <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($grouped[$catKey] as $audio): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;
                background:<?= $audio['is_active'] ? 'rgba(34,197,94,.04)' : 'var(--hover-bg,rgba(0,0,0,.02))' ?>;
                border:1px solid <?= $audio['is_active'] ? 'rgba(34,197,94,.15)' : 'var(--border-color,rgba(0,0,0,.04))' ?>;
                transition:all .2s">

                <!-- Play Button -->
                <button onclick="togglePlay(this, '<?= htmlspecialchars(SITE_URL . '/assets/audio/' . $audio['filename']) ?>', <?= $audio['volume'] ?>)"
                    style="width:40px;height:40px;border-radius:50%;border:none;
                    background:<?= $audio['is_active'] ? 'linear-gradient(135deg,#16A34A,#059669)' : 'linear-gradient(135deg,#3B82F6,#2563EB)' ?>;
                    color:#fff;font-size:1.1rem;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:transform .15s"
                    title="تشغيل/إيقاف">
                    ▶
                </button>

                <!-- Info -->
                <div style="flex:1;min-width:0">
                    <div style="font-size:.85rem;font-weight:700;color:var(--text-primary,#1E293B);display:flex;align-items:center;gap:6px">
                        <?= htmlspecialchars($audio['title']) ?>
                        <?php if ($audio['is_active']): ?>
                            <span style="background:#16A34A;color:#fff;padding:1px 7px;border-radius:6px;font-size:.6rem;font-weight:700">مفعّل</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.68rem;color:var(--text-secondary,#94A3B8);margin-top:2px;display:flex;gap:10px;flex-wrap:wrap">
                        <span>📁 <?= htmlspecialchars($audio['original_name'] ?: $audio['filename']) ?></span>
                        <?php if ($audio['file_size']): ?>
                            <span>📦 <?= round($audio['file_size'] / 1024) ?> KB</span>
                        <?php endif; ?>
                        <span><?= $audio['play_mode'] === 'loop' ? '🔁 تكرار' : '▶ مرة واحدة' ?></span>
                        <span>🔊 <?= round($audio['volume'] * 100) ?>%</span>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display:flex;gap:4px;flex-shrink:0">
                    <?php if (!$audio['is_active']): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="audio_id" value="<?= $audio['id'] ?>">
                            <button type="submit" style="padding:6px 12px;border:none;border-radius:8px;background:rgba(34,197,94,.1);color:#16A34A;font-size:.72rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s"
                                title="تفعيل">✅ تفعيل</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <input type="hidden" name="action" value="deactivate">
                            <input type="hidden" name="audio_id" value="<?= $audio['id'] ?>">
                            <button type="submit" style="padding:6px 12px;border:none;border-radius:8px;background:rgba(148,163,184,.1);color:#64748B;font-size:.72rem;font-weight:700;cursor:pointer;font-family:inherit"
                                title="إلغاء التفعيل">⏸ إلغاء</button>
                        </form>
                    <?php endif; ?>

                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($audio)) ?>)"
                        style="padding:6px 12px;border:none;border-radius:8px;background:rgba(59,130,246,.1);color:#3B82F6;font-size:.72rem;font-weight:700;cursor:pointer;font-family:inherit"
                        title="تعديل">✏️</button>

                    <form method="POST" style="display:inline" onsubmit="return confirm('هل تريد حذف هذا المقطع الصوتي؟')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="audio_id" value="<?= $audio['id'] ?>">
                        <button type="submit" style="padding:6px 12px;border:none;border-radius:8px;background:rgba(239,68,68,.1);color:#DC2626;font-size:.72rem;font-weight:700;cursor:pointer;font-family:inherit"
                            title="حذف">🗑️</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:20px;color:var(--text-secondary,#94A3B8);font-size:.82rem">
            لا يوجد مقاطع صوتية في هذا التصنيف
        </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

</div>

<!-- Edit Modal -->
<div id="editModal" style="position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.45);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card-bg,#fff);border-radius:18px;padding:24px;max-width:440px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.15)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div style="font-size:1rem;font-weight:800;color:var(--text-primary,#1E293B)">✏️ تعديل المقطع الصوتي</div>
            <button onclick="closeEditModal()" style="background:none;border:none;font-size:1.3rem;color:var(--text-secondary,#94A3B8);cursor:pointer">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="audio_id" id="editId">

            <div style="margin-bottom:12px">
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">اسم المقطع</label>
                <input type="text" name="title" id="editTitle" required
                    style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">التصنيف</label>
                    <select name="category" id="editCategory" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
                        <?php foreach ($categories as $catKey => $cat): ?>
                            <option value="<?= $catKey ?>"><?= $cat['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">طريقة التشغيل</label>
                    <select name="play_mode" id="editPlayMode" style="width:100%;padding:10px 14px;border:1.5px solid var(--border-color,#E2E8F0);border-radius:10px;font-family:inherit;font-size:.88rem;background:var(--input-bg,#F8FAFC);color:var(--text-primary,#1E293B)">
                        <option value="once">▶ مرة واحدة</option>
                        <option value="loop">🔁 تكرار</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:.78rem;font-weight:700;color:var(--text-secondary,#64748B);margin-bottom:4px">مستوى الصوت: <span id="editVolLabel">100%</span></label>
                <input type="range" name="volume" id="editVolume" min="0" max="1" step="0.05" value="1" style="width:100%"
                    oninput="document.getElementById('editVolLabel').textContent=Math.round(this.value*100)+'%'">
            </div>
            <button type="submit" style="width:100%;padding:12px;border:none;border-radius:10px;background:linear-gradient(135deg,#3B82F6,#2563EB);color:#fff;font-size:.88rem;font-weight:700;font-family:inherit;cursor:pointer">
                💾 حفظ التعديلات
            </button>
        </form>
    </div>
</div>

<script>
// ── Audio Player ──
var currentAudio = null;
var currentBtn = null;

function togglePlay(btn, url, volume) {
    if (currentAudio && currentBtn === btn) {
        currentAudio.pause();
        currentAudio = null;
        btn.textContent = '▶';
        btn.style.transform = '';
        currentBtn = null;
        return;
    }
    if (currentAudio) {
        currentAudio.pause();
        if (currentBtn) { currentBtn.textContent = '▶'; currentBtn.style.transform = ''; }
    }
    currentAudio = new Audio(url);
    currentAudio.volume = volume || 1;
    currentBtn = btn;
    btn.textContent = '⏸';
    btn.style.transform = 'scale(1.1)';
    currentAudio.play().catch(function(){});
    currentAudio.onended = function() {
        btn.textContent = '▶';
        btn.style.transform = '';
        currentAudio = null;
        currentBtn = null;
    };
}

// ── Edit Modal ──
function openEditModal(audio) {
    document.getElementById('editId').value = audio.id;
    document.getElementById('editTitle').value = audio.title;
    document.getElementById('editCategory').value = audio.category;
    document.getElementById('editPlayMode').value = audio.play_mode;
    document.getElementById('editVolume').value = audio.volume;
    document.getElementById('editVolLabel').textContent = Math.round(audio.volume * 100) + '%';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
