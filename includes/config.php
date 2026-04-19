<?php
// ⛔⛔⛔ LEGACY — HARD FROZEN — DO NOT MODIFY ⛔⛔⛔
// =============================================================
// includes/config.php - إعدادات النظام الأساسية
// =============================================================

// Disable QUIC/HTTP3 globally - fix ERR_QUIC_PROTOCOL_ERROR
if (!headers_sent()) { header('Alt-Svc: clear'); }

// ================== تحميل ملف .env (بدون Composer) ==================
$_envFile = dirname(__DIR__) . '/.env';
if (file_exists($_envFile)) {
    $_envLines = file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($_envLines as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#') continue;
        if (strpos($_line, '=') === false) continue;
        [$_key, $_val] = explode('=', $_line, 2);
        $_key = trim($_key);
        $_val = trim($_val);
        // إزالة علامات الاقتباس إن وُجدت
        if (preg_match('/^(["\'])(.*)\\1$/', $_val, $_m)) $_val = $_m[2];
        if (!array_key_exists($_key, $_ENV)) {
            $_ENV[$_key] = $_val;
            putenv("{$_key}={$_val}");
        }
    }
}

// ================== اتصال قاعدة البيانات ==================
// Require critical env vars in production (no insecure fallbacks)
function _requireEnv(string $key, string $fallback = ''): string {
    $val = $_ENV[$key] ?? '';
    if ($val !== '') return $val;
    if ($fallback !== '') return $fallback;
    error_log("CRITICAL: Missing required environment variable: {$key}");
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        exit('Server configuration error');
    }
    return '';
}
define('DB_HOST',    _requireEnv('DB_HOST', 'localhost'));
define('DB_USER',    _requireEnv('DB_USER'));
define('DB_PASS',    _requireEnv('DB_PASS'));
define('DB_NAME',    _requireEnv('DB_NAME'));
define('DB_CHARSET', 'utf8mb4');

// ================== رابط الموقع (Auto-detect) ==================
// يمكن تحديد SITE_URL في .env للتجاوز اليدوي
if (!empty($_ENV['SITE_URL'])) {
    define('SITE_URL', rtrim($_ENV['SITE_URL'], '/'));
} else {
    // يتم اكتشاف الرابط تلقائياً - لا حاجة لتعديله يدوياً
    $_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    // التحقق من HTTP_HOST لمنع Host Header Injection
    $_rawHost  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // إزالة المنفذ للمقارنة
    $_hostOnly = preg_replace('/:\d+$/', '', $_rawHost);
    // السماح فقط بأحرف DNS صالحة (حماية من header injection)
    $_host     = preg_match('/^[a-zA-Z0-9._\-]+$/', $_hostOnly) ? $_rawHost : 'localhost';
    // إزالة أي نقاط في نهاية اسم المضيف
    $_host     = rtrim($_host, '.');
    $_basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // اذهب لجذر المشروع (قد نكون داخل admin/ أو api/ أو employee/)
    if (preg_match('#/(admin|api|employee|includes|error)$#', $_basePath)) {
        $_basePath = dirname($_basePath);
    }
    // التأكد من عدم وجود شرطة مائلة في نهاية basePath (إلا إذا كان جذر)
    $_basePath = ($_basePath === '/' || $_basePath === '') ? '' : $_basePath;
    define('SITE_URL', $_protocol . '://' . $_host . $_basePath);
}
define('SITE_NAME', 'نظام الحضور والانصراف');

// ================== إعدادات الجلسة ==================
$_isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $_isSecure ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
// إذا كان "تذكرني" مفعلاً — 30 يوم، وإلا 24 ساعة
$_rememberMe = (!empty($_COOKIE['remember_admin']) && $_COOKIE['remember_admin'] === '1');
$_sessLifetime = $_rememberMe ? 2592000 : 86400;
ini_set('session.cookie_lifetime', $_sessLifetime);
ini_set('session.gc_maxlifetime', $_sessLifetime);

// ================== timezone ==================
date_default_timezone_set('Asia/Riyadh');

// ================== بدء الجلسة ==================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================== رسائل الخطأ ==================
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ================== Security Headers ==================
if (!headers_sent()) {
    // Generate CSP nonce for inline scripts/styles
    $cspNonce = base64_encode(random_bytes(16));
    if (!defined('CSP_NONCE')) define('CSP_NONCE', $cspNonce);

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), camera=(self), microphone=(self)');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$cspNonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'nonce-{$cspNonce}' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none'");
}

// ================== v4.0 Bootstrap (MVC + Services) ==================
$_bootstrapFile = dirname(__DIR__) . '/config/bootstrap.php';
if (file_exists($_bootstrapFile)) {
    require_once $_bootstrapFile;
}

// ================== Session Timeout (configurable, cached) ==================
if (!empty($_SESSION['admin_id'])) {
    // جلب مهلة الجلسة من الإعدادات (بالدقائق) — افتراضي 120 دقيقة
    // يتم تخزينها مؤقتاً في الجلسة لتجنب استعلام DB في كل طلب
    $idleTimeoutMin = 120;
    if (isset($_SESSION['_cached_session_timeout']) && isset($_SESSION['_cached_timeout_at'])
        && (time() - $_SESSION['_cached_timeout_at']) < 300) {
        $idleTimeoutMin = $_SESSION['_cached_session_timeout'];
    } else {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $_tmpPdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stk = $_tmpPdo->query("SELECT setting_value FROM settings WHERE setting_key = 'session_lifetime'")->fetchColumn();
            if ($stk) $idleTimeoutMin = max(5, (int)$stk);
            $_tmpPdo = null;
        } catch (Exception $e) {}
        $_SESSION['_cached_session_timeout'] = $idleTimeoutMin;
        $_SESSION['_cached_timeout_at'] = time();
    }
    $idleTimeout = $idleTimeoutMin * 60;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idleTimeout) {
        // انتهاء مهلة الجلسة — تسجيل خروج تلقائي
        session_unset();
        session_destroy();
        // AJAX request — return JSON redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['session_expired' => true, 'redirect' => '/admin/login.php']);
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash_error'] = 'انتهت جلستك بسبب عدم النشاط. يرجى تسجيل الدخول مجدداً.';
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    } else {
        $_SESSION['last_activity'] = time();
    }
}
