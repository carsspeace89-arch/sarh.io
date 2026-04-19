<?php
// =============================================================
// api/complaints.php — API للشكاوى (إرسال شكوى من الموظف)
// =============================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// التحقق من التوكن
$token = trim($_POST['token'] ?? $_GET['token'] ?? '');
if ($token === '') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'توكن مفقود']);
    exit;
}
$employee = getEmployeeByToken($token);
if (!$employee || !$employee['is_active']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'حساب غير صالح']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

$action = $_POST['action'] ?? 'submit';

if ($action === 'submit') {
    $complaintType = $_POST['complaint_type'] ?? '';
    $targetName    = trim($_POST['target_name'] ?? '');
    $subject       = trim($_POST['subject'] ?? '');
    $body          = trim($_POST['body'] ?? '');

    // التحقق
    $validTypes = ['person', 'branch', 'group', 'other'];
    if (!in_array($complaintType, $validTypes)) {
        echo json_encode(['success' => false, 'message' => 'نوع الشكوى غير صالح']);
        exit;
    }
    if ($subject === '' || mb_strlen($subject) < 5) {
        echo json_encode(['success' => false, 'message' => 'يرجى كتابة عنوان الشكوى (5 أحرف على الأقل)']);
        exit;
    }
    if ($body === '' || mb_strlen($body) < 10) {
        echo json_encode(['success' => false, 'message' => 'يرجى كتابة تفاصيل الشكوى (10 أحرف على الأقل)']);
        exit;
    }
    if (mb_strlen($subject) > 500) {
        echo json_encode(['success' => false, 'message' => 'عنوان الشكوى طويل جداً']);
        exit;
    }

    // إنشاء الجدول إذا لم يكن موجوداً
    db()->exec("CREATE TABLE IF NOT EXISTS `complaints` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT UNSIGNED NOT NULL,
        `complaint_type` ENUM('person','branch','group','other') NOT NULL DEFAULT 'other',
        `target_name` VARCHAR(255) DEFAULT NULL,
        `subject` VARCHAR(500) NOT NULL,
        `body` TEXT NOT NULL,
        `attachments` TEXT DEFAULT NULL,
        `status` ENUM('pending','reviewing','resolved','rejected') NOT NULL DEFAULT 'pending',
        `admin_reply` TEXT DEFAULT NULL,
        `admin_id` INT UNSIGNED DEFAULT NULL,
        `resolved_at` DATETIME DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_employee` (`employee_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // إضافة عمود المرفقات إذا لم يكن موجوداً
    try {
        db()->exec("ALTER TABLE complaints ADD COLUMN `attachments` TEXT DEFAULT NULL AFTER `body`");
    } catch (Exception $e) { /* العمود موجود */ }

    // معالجة المرفقات (صور من الكاميرا)
    $attachmentPaths = [];
    $empId = (int)$employee['id'];
    $uploadDir = dirname(__DIR__) . '/storage/uploads/complaints/' . $empId . '/';

    if (!empty($_FILES['attachments'])) {
        @mkdir($uploadDir, 0755, true);
        $files = $_FILES['attachments'];
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        if ($fileCount > 5) {
            echo json_encode(['success' => false, 'message' => 'الحد الأقصى 5 صور']);
            exit;
        }

        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $origName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($fileError !== UPLOAD_ERR_OK || empty($tmpName)) continue;

            // التحقق من الحجم (5 ميجا)
            if ($fileSize > 5 * 1024 * 1024) continue;

            // التحقق من نوع الملف (صور فقط)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpName);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowedMimes, true)) continue;

            // فحص الامتداد
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
                $ext = match($mime) { 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
            }

            // منع الملفات الخبيثة
            if (preg_match('/\.(php|phtml|php3|php4|php5|pht|phar|sh|py|pl|cgi|exe)$/i', $origName)) continue;

            $filename = 'complaint_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                $attachmentPaths[] = 'complaints/' . $empId . '/' . $filename;
            }
        }
    }

    $attachmentsJson = !empty($attachmentPaths) ? json_encode($attachmentPaths) : null;

    $stmt = db()->prepare("INSERT INTO complaints (employee_id, complaint_type, target_name, subject, body, attachments) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $empId,
        $complaintType,
        $targetName ?: null,
        $subject,
        $body,
        $attachmentsJson
    ]);

    echo json_encode(['success' => true, 'message' => 'تم إرسال شكواك بنجاح. سيتم مراجعتها من الإدارة.', 'attachments_count' => count($attachmentPaths)]);
    exit;
}

if ($action === 'my_complaints') {
    // إنشاء الجدول إذا لم يكن موجوداً
    db()->exec("CREATE TABLE IF NOT EXISTS `complaints` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT UNSIGNED NOT NULL,
        `complaint_type` ENUM('person','branch','group','other') NOT NULL DEFAULT 'other',
        `target_name` VARCHAR(255) DEFAULT NULL,
        `subject` VARCHAR(500) NOT NULL,
        `body` TEXT NOT NULL,
        `attachments` TEXT DEFAULT NULL,
        `status` ENUM('pending','reviewing','resolved','rejected') NOT NULL DEFAULT 'pending',
        `admin_reply` TEXT DEFAULT NULL,
        `admin_id` INT UNSIGNED DEFAULT NULL,
        `resolved_at` DATETIME DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_employee` (`employee_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // إضافة عمود المرفقات إذا لم يكن موجوداً
    try {
        db()->exec("ALTER TABLE complaints ADD COLUMN `attachments` TEXT DEFAULT NULL AFTER `body`");
    } catch (Exception $e) { /* العمود موجود */ }

    $stmt = db()->prepare("SELECT id, complaint_type, target_name, subject, status, admin_reply, attachments, created_at FROM complaints WHERE employee_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([(int)$employee['id']]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'complaints' => $complaints]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
