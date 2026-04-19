<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// api/serve-file.php — عرض ملفات البروفايل بشكل آمن
// =============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// إغلاق الجلسة مبكراً لمنع قفل الملف أثناء تقديم الصورة
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$relPath = trim($_GET['f'] ?? '');

// منع path traversal — يجب أن يبدأ بـ profiles/رقم/
if (
    empty($relPath) ||
    str_contains($relPath, '..') ||
    str_contains($relPath, "\0") ||
    str_contains($relPath, '%') ||
    !preg_match('#^profiles/\d+/[a-zA-Z0-9._\-]+$#', $relPath)
) {
    http_response_code(403); exit;
}

// Double-check with realpath to prevent any encoded traversal
$basePath = realpath(dirname(__DIR__) . '/storage/uploads');
$resolvedPath = realpath($basePath . '/' . $relPath);
if ($resolvedPath === false || !str_starts_with($resolvedPath, $basePath . DIRECTORY_SEPARATOR)) {
    http_response_code(403); exit;
}

// المصادقة: مدير أو موظف صاحب الملف
$authorized = false;

if (isAdminLoggedIn()) {
    $authorized = true;
} elseif (!empty($_GET['t'])) {
    $token = trim($_GET['t']);
    $emp   = getEmployeeByToken($token);
    if ($emp) {
        $empId = (int)$emp['id'];
        if (preg_match('#^profiles/' . $empId . '/#', $relPath)) {
            $authorized = true;
        }
    }
}

if (!$authorized) { http_response_code(403); exit; }

$fullPath = $resolvedPath;
if (!is_file($fullPath)) { http_response_code(404); exit; }

// التحقق من نوع MIME الفعلي (ليس الامتداد)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mime     = $finfo->file($fullPath);
$safe     = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];
if (!in_array($mime, $safe, true)) { http_response_code(403); exit; }

// منع تنفيذ محتوى html/js من داخل PDF
if ($mime === 'application/pdf') {
    header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=7200');
header('X-Content-Type-Options: nosniff');
readfile($fullPath);
exit;
