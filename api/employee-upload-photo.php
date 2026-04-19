<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/employee-upload-photo.php — تحديث صورة البروفايل بواسطة الموظف
// =============================================================

header('Content-Type: application/json; charset=utf-8');
header('Alt-Svc: clear');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$token = trim($_POST['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'توكن مفقود']);
    exit;
}

$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$empId = (int)$employee['id'];

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'لم يُستلم الملف']);
    exit;
}

$file  = $_FILES['photo'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMimes, true)) {
    echo json_encode(['success' => false, 'message' => 'صيغة غير مدعومة — jpg/png/webp فقط']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
    echo json_encode(['success' => false, 'message' => 'امتداد ملف غير صالح']);
    exit;
}

if (preg_match('/\.(php|phtml|php3|php4|php5|pht|phar|sh|py|pl|cgi|exe)$/i', basename($file['name']))) {
    echo json_encode(['success' => false, 'message' => 'نوع ملف محظور']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'الحجم أكبر من 5 ميجا']);
    exit;
}

$upRoot = dirname(__DIR__) . '/storage/uploads/profiles/' . $empId . '/';
@mkdir($upRoot, 0755, true);

// حذف الصور القديمة
foreach (glob($upRoot . 'photo.*') as $old) {
    @unlink($old);
}

$ext      = match($mime) { 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
$filename = 'photo.' . $ext;

if (!move_uploaded_file($file['tmp_name'], $upRoot . $filename)) {
    echo json_encode(['success' => false, 'message' => 'فشل حفظ الملف']);
    exit;
}

$relPath = 'profiles/' . $empId . '/' . $filename;
$stmt = db()->prepare("UPDATE employees SET profile_photo = ? WHERE id = ?");
$stmt->execute([$relPath, $empId]);

$photoUrl = SITE_URL . '/api/serve-file.php?f=' . urlencode($relPath) . '&t=' . urlencode($token);

echo json_encode([
    'success'   => true,
    'message'   => 'تم تحديث الصورة بنجاح',
    'photo_url' => $photoUrl,
]);
