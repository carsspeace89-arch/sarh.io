<?php
// =============================================================
// src/Core/ErrorHandler.php - معالج الأخطاء المركزي
// =============================================================

namespace App\Core;

class ErrorHandler
{
    private static bool $registered = false;

    /**
     * تسجيل معالج الأخطاء
     */
    public static function register(): void
    {
        if (self::$registered) return;
        self::$registered = true;

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * تحويل الأخطاء إلى استثناءات
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Log but don't throw for notices/warnings to preserve backward compatibility
        if ($severity === E_NOTICE || $severity === E_USER_NOTICE || $severity === E_DEPRECATED) {
            Logger::warning("PHP Notice: {$message}", ['file' => $file, 'line' => $line]);
            return true;
        }

        if ($severity === E_WARNING || $severity === E_USER_WARNING) {
            Logger::warning("PHP Warning: {$message}", ['file' => $file, 'line' => $line]);
            return true;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * معالجة الاستثناءات غير الملتقطة
     */
    public static function handleException(\Throwable $e): void
    {
        Logger::error('Uncaught Exception: ' . $e->getMessage(), [
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, "Error: {$e->getMessage()}\n");
            exit(1);
        }

        if (!headers_sent()) {
            http_response_code(500);
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isApi = str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');

        if ($isAjax || $isApi) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ في الخادم',
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Redirect to error page or show minimal HTML
            $errorPage = dirname(__DIR__, 2) . '/error/500.php';
            if (file_exists($errorPage)) {
                include $errorPage;
            } else {
                echo '<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8">';
                echo '<title>خطأ</title></head><body>';
                echo '<h1>حدث خطأ في الخادم</h1>';
                echo '<p>يرجى المحاولة لاحقاً أو الاتصال بالدعم الفني.</p>';
                echo '</body></html>';
            }
        }
        exit(1);
    }

    /**
     * معالجة الأخطاء القاتلة عند الإغلاق
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(
                new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }
}
