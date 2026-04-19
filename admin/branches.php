<?php
// =============================================================
// admin/branches.php - إدارة الفروع (CRUD + ورديات + موقع)
// =============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

$pageTitle  = 'إدارة الفروع';
$activePage = 'branches';
$message    = '';
$msgType    = '';

// =================== إجراءات POST ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'طلب غير صالح';
        $msgType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        // --- إضافة فرع ---
        if ($action === 'add') {
            $name     = sanitize($_POST['name'] ?? '');
            $lat      = (float)($_POST['latitude'] ?? 0);
            $lon      = (float)($_POST['longitude'] ?? 0);
            $radius   = (int)($_POST['geofence_radius'] ?? 25);

            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                $message = 'إحداثيات غير صالحة. خط العرض يجب أن يكون بين -90 و 90، وخط الطول بين -180 و 180';
                $msgType = 'error';
            } elseif ($radius < 10 || $radius > 10000) {
                $message = 'نصف قطر الجيوفينس يجب أن يكون بين 10 و 10000 متر';
                $msgType = 'error';
            } elseif ($name && $lat != 0 && $lon != 0) {
                try {
                    $stmt = db()->prepare("INSERT INTO branches (name, latitude, longitude, geofence_radius) VALUES (?,?,?,?)");
                    $stmt->execute([$name, $lat, $lon, $radius]);
                    $newBranchId = db()->lastInsertId();

                    // حفظ الورديات
                    for ($s = 1; $s <= 3; $s++) {
                        $sStart = trim($_POST["shift_start_{$s}"] ?? '');
                        $sEnd   = trim($_POST["shift_end_{$s}"] ?? '');
                        if ($sStart && $sEnd) {
                            $stmt2 = db()->prepare("INSERT INTO branch_shifts (branch_id, shift_number, shift_start, shift_end, is_active) VALUES (?,?,?,?,1)");
                            $stmt2->execute([$newBranchId, $s, $sStart, $sEnd]);
                        }
                    }

                    $message = "تم إضافة الفرع «{$name}» بنجاح";
                    $msgType = 'success';
                } catch (PDOException $e) {
                    $message = 'خطأ: ' . ($e->getCode() == 23000 ? 'اسم الفرع مكرر' : $e->getMessage());
                    $msgType = 'error';
                }
            } else {
                $message = 'أدخل اسم الفرع والإحداثيات';
                $msgType = 'error';
            }
        }

        // --- حذف فرع ---
        if ($action === 'delete') {
            $id = (int)($_POST['branch_id'] ?? 0);
            if ($id) {
                $empCount = db()->prepare("SELECT COUNT(*) FROM employees WHERE branch_id = ?");
                $empCount->execute([$id]);
                if ((int)$empCount->fetchColumn() > 0) {
                    $message = 'لا يمكن حذف فرع يحتوي على موظفين. انقل الموظفين أولاً.';
                    $msgType = 'error';
                } else {
                    db()->prepare("DELETE FROM branch_shifts WHERE branch_id = ?")->execute([$id]);
                    db()->prepare("DELETE FROM branches WHERE id=?")->execute([$id]);
                    $message = "تم حذف الفرع";
                    $msgType = 'success';
                }
            }
        }

        // --- تفعيل/تعطيل ---
        if ($action === 'toggle') {
            $id = (int)($_POST['branch_id'] ?? 0);
            if ($id) {
                db()->prepare("UPDATE branches SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
                $message = "تم تغيير حالة الفرع";
                $msgType = 'success';
            }
        }
    }
    header('Location: branches.php?msg=' . urlencode($message) . '&t=' . $msgType);
    exit;
}

// عرض الرسالة من redirect
if (!empty($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $msgType = $_GET['t'] ?? 'success';
}

// =================== جلب الفروع والورديات ===================
$branches = db()->query("SELECT b.*, (SELECT COUNT(*) FROM employees WHERE branch_id = b.id) AS emp_count FROM branches b ORDER BY b.id ASC")->fetchAll();

$allShifts = db()->query("SELECT * FROM branch_shifts WHERE is_active = 1 ORDER BY branch_id, shift_number")->fetchAll();
$shiftsByBranch = [];
foreach ($allShifts as $s) {
    $shiftsByBranch[$s['branch_id']][] = $s;
}

$csrf = generateCsrfToken();

require_once __DIR__ . '/../includes/admin_layout.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});
</script>

<style>
    .branch-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px; margin-bottom: 22px; }
    .branch-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); overflow: hidden; transition: transform .2s, box-shadow .2s; }
    .branch-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
    .branch-card.inactive { opacity: .6; }
    .bc-header { background: linear-gradient(135deg, var(--primary), var(--primary-d)); padding: 16px 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; }
    .bc-name { font-size: 1.1rem; font-weight: 700; }
    .bc-badge { padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3); }
    .bc-badge.off { background: rgba(0,0,0,.3); }
    .bc-body { padding: 16px 20px; }
    .bc-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; margin-bottom: 12px; }
    .bc-info { font-size: .78rem; }
    .bc-label { color: var(--text3); font-weight: 600; margin-bottom: 2px; }
    .bc-val { font-weight: 700; color: var(--text); }
    .bc-section { font-size: .72rem; font-weight: 700; color: var(--primary-d); margin: 10px 0 6px; padding-bottom: 4px; border-bottom: 1px solid var(--primary-l); }
    .bc-actions { display: flex; gap: 8px; flex-wrap: wrap; padding: 12px 20px; border-top: 1px solid var(--border); background: var(--surface2); }
    .shift-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; font-size: .78rem; font-weight: 600; background: var(--surface2); border: 1px solid var(--border); margin-bottom: 4px; }
    .shift-tag .shift-num { background: var(--primary); color: #fff; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .68rem; }
    .modal-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,.5); z-index: 1000; display: none; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-branch { background: #fff; border-radius: 16px; width: 95%; max-width: 700px; max-height: 90vh; overflow-y: auto; overflow-x: hidden; box-shadow: 0 25px 50px rgba(0,0,0,.25); padding: 0; isolation: isolate; }
    .modal-branch-head { background: linear-gradient(135deg, var(--primary), var(--primary-d)); padding: 20px 24px; color: #fff; border-radius: 16px 16px 0 0; font-size: 1.1rem; font-weight: 700; }
    .modal-branch-body { padding: 24px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .form-row.col3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-group { display: flex; flex-direction: column; }
    .form-label { font-size: .78rem; font-weight: 600; color: var(--text2); margin-bottom: 4px; }
    .form-control { padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-family: inherit; font-size: .88rem; transition: border-color .2s; }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }
    .form-section { font-size: .82rem; font-weight: 700; color: var(--primary-d); margin: 16px 0 8px; padding: 6px 0; border-bottom: 2px solid var(--primary-l); display: flex; align-items: center; gap: 8px; }
    .modal-branch-foot { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
    #branchMapAdd { width: 100%; height: 250px; border-radius: 10px; margin-bottom: 10px; border: 2px solid var(--border); overflow: hidden; position: relative; z-index: 0; contain: layout paint; isolation: isolate; }
    .shift-row { display: grid; grid-template-columns: auto 1fr 1fr; gap: 10px; align-items: end; margin-bottom: 10px; padding: 10px; background: var(--surface2); border-radius: 8px; border: 1px solid var(--border); }
    .shift-row-label { font-size: .82rem; font-weight: 700; color: var(--primary-d); min-width: 65px; padding-bottom: 8px; }
</style>

<?php if ($message): ?>
    <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card-header" style="margin-bottom:18px">
    <span class="card-title"><span class="card-title-bar"></span> الفروع (<?= count($branches) ?>)</span>
    <button class="btn btn-primary" onclick="openAddModal()">+ إضافة فرع</button>
</div>

<div class="branch-grid">
    <?php foreach ($branches as $b):
        $bShifts = $shiftsByBranch[$b['id']] ?? [];
    ?>
        <div class="branch-card <?= $b['is_active'] ? '' : 'inactive' ?>">
            <div class="bc-header">
                <span class="bc-name"><?= htmlspecialchars($b['name']) ?></span>
                <span class="bc-badge <?= $b['is_active'] ? '' : 'off' ?>"><?= $b['is_active'] ? 'مفعّل' : 'معطّل' ?></span>
            </div>
            <div class="bc-body">
                <div class="bc-info-grid">
                    <div class="bc-info">
                        <div class="bc-label">عدد الموظفين</div>
                        <div class="bc-val"><?= $b['emp_count'] ?></div>
                    </div>
                    <div class="bc-info">
                        <div class="bc-label">نصف القطر</div>
                        <div class="bc-val"><?= $b['geofence_radius'] ?> م</div>
                    </div>
                </div>
                <div class="bc-section">الورديات</div>
                <?php if ($bShifts): ?>
                    <?php foreach ($bShifts as $shift): ?>
                        <div class="shift-tag">
                            <span class="shift-num"><?= (int)$shift['shift_number'] ?></span>
                            <?= htmlspecialchars(substr($shift['shift_start'], 0, 5)) ?> — <?= htmlspecialchars(substr($shift['shift_end'], 0, 5)) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="font-size:.78rem;color:var(--text3)">لا توجد ورديات</div>
                <?php endif; ?>
            </div>
            <div class="bc-actions">
                <a class="btn btn-secondary btn-sm" href="branch-edit.php?id=<?= (int)$b['id'] ?>">تعديل</a>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn btn-secondary btn-sm"><?= $b['is_active'] ? 'تعطيل' : 'تفعيل' ?></button>
                </form>
                <?php if ($b['emp_count'] == 0): ?>
                    <form method="POST" style="display:inline" onsubmit="return confirm('حذف الفرع نهائياً؟')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($branches)): ?>
        <div class="card" style="text-align:center;padding:40px;color:var(--text3)">لا توجد فروع بعد. اضغط "إضافة فرع" لبدء الإعداد.</div>
    <?php endif; ?>
</div>

<!-- Modal إضافة فرع -->
<div class="modal-overlay" id="addModal">
    <div class="modal-branch">
        <div class="modal-branch-head">إضافة فرع جديد</div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="add">
            <div class="modal-branch-body">
                <div class="form-section">بيانات الفرع</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">اسم الفرع *</label>
                        <input class="form-control" name="name" required placeholder="مثال: صرح الرئيسي">
                    </div>
                    <div class="form-group">
                        <label class="form-label">نصف قطر النطاق (م) *</label>
                        <input class="form-control" type="number" name="geofence_radius" value="25" min="1" required>
                    </div>
                </div>
                <div class="form-section">
                    الموقع الجغرافي — اضغط على الخريطة أو أدخل الإحداثيات يدوياً
                    <button type="button" class="btn btn-secondary btn-sm" onclick="useMyLocation()" style="margin-right:auto;font-size:.72rem;padding:3px 10px">📍 موقعي الحالي</button>
                </div>
                <div id="branchMapAdd"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">خط العرض *</label>
                        <input class="form-control" type="number" step="any" name="latitude" id="addLat" required style="direction:ltr" placeholder="24.7136" oninput="syncMapFromInputs()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">خط الطول *</label>
                        <input class="form-control" type="number" step="any" name="longitude" id="addLon" required style="direction:ltr" placeholder="46.6753" oninput="syncMapFromInputs()">
                    </div>
                </div>

                <div class="form-section">الورديات (يُسمح بالتسجيل قبل الوردية بـ 90 دقيقة — الانصراف تلقائي)</div>
                <div class="shift-row">
                    <div class="shift-row-label">الوردية 1</div>
                    <div class="form-group"><label class="form-label">من</label><input class="form-control" type="time" name="shift_start_1" value="08:00"></div>
                    <div class="form-group"><label class="form-label">إلى</label><input class="form-control" type="time" name="shift_end_1" value="12:00"></div>
                </div>
                <div class="shift-row">
                    <div class="shift-row-label">الوردية 2</div>
                    <div class="form-group"><label class="form-label">من</label><input class="form-control" type="time" name="shift_start_2"></div>
                    <div class="form-group"><label class="form-label">إلى</label><input class="form-control" type="time" name="shift_end_2"></div>
                </div>
                <div class="shift-row">
                    <div class="shift-row-label">الوردية 3</div>
                    <div class="form-group"><label class="form-label">من</label><input class="form-control" type="time" name="shift_start_3"></div>
                    <div class="form-group"><label class="form-label">إلى</label><input class="form-control" type="time" name="shift_end_3"></div>
                </div>

            </div>
            <div class="modal-branch-foot">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ →</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

let addMap, addMarker;
function openAddModal() {
    openModal('addModal');
    setTimeout(() => {
        if (addMap) { addMap.remove(); addMap = null; addMarker = null; }
        addMap = L.map('branchMapAdd').setView([24.7136, 46.6753], 13);
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '© Esri', maxZoom: 19 }).addTo(addMap);
        addMap.on('click', e => placeMarker(e.latlng.lat, e.latlng.lng));
        addMap.invalidateSize();
        const lat = parseFloat(document.getElementById('addLat').value);
        const lon = parseFloat(document.getElementById('addLon').value);
        if (lat && lon) placeMarker(lat, lon, false);
    }, 350);
}

function placeMarker(lat, lon, updateInputs = true) {
    if (!addMap) return;
    if (addMarker) addMap.removeLayer(addMarker);
    addMarker = L.marker([lat, lon]).addTo(addMap);
    addMap.setView([lat, lon], addMap.getZoom() < 14 ? 16 : addMap.getZoom());
    if (updateInputs) {
        document.getElementById('addLat').value = lat.toFixed(8);
        document.getElementById('addLon').value = lon.toFixed(8);
    }
}

function syncMapFromInputs() {
    const lat = parseFloat(document.getElementById('addLat').value);
    const lon = parseFloat(document.getElementById('addLon').value);
    if (!isNaN(lat) && !isNaN(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
        placeMarker(lat, lon, false);
    }
}

function useMyLocation() {
    if (!navigator.geolocation) { alert('المتصفح لا يدعم تحديد الموقع'); return; }
    navigator.geolocation.getCurrentPosition(
        pos => placeMarker(pos.coords.latitude, pos.coords.longitude),
        err => alert('تعذّر تحديد الموقع: ' + err.message),
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); });
});

function tick() { const el = document.getElementById('topbarClock'); if (el) el.textContent = new Date().toLocaleString('ar-SA'); }
tick(); setInterval(tick, 1000);

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
document.getElementById('sidebarOverlay')?.addEventListener('click', toggleSidebar);
</script>

</div>
</div>
</body>
</html>
