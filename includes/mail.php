<?php
// ⛔ LEGACY — DO NOT EXTEND | All new code must go to src/* or api/v1/*
// =============================================================
// includes/mail.php - نظام إرسال البريد الإلكتروني عبر SMTP
// =============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * إرسال بريد إلكتروني عبر SMTP
 * 
 * @param array  $recipients قائمة الإيميلات
 * @param string $subject    الموضوع
 * @param string $htmlBody   محتوى HTML
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendEmail(array $recipients, string $subject, string $htmlBody): array {
    $mail = new PHPMailer(true);
    
    try {
        // إعدادات SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? 'etgan@sarh.io';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 465);
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        
        // المرسل
        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'etgan@sarh.io';
        $fromName  = $_ENV['SMTP_FROM_NAME']  ?? 'نظام صرح للحضور';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($fromEmail, $fromName);
        
        // المستلمون
        foreach ($recipients as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email);
            }
        }
        
        // المحتوى
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        // نسخة نصية للأجهزة التي لا تدعم HTML
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</tr>'], "\n", $htmlBody));
        
        $mail->send();
        
        return ['success' => true, 'error' => null];
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * اختبار اتصال SMTP
 */
function testSmtpConnection(): array {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? 'etgan@sarh.io';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 465);
        
        $mail->smtpConnect();
        $mail->smtpClose();
        
        return ['success' => true, 'message' => 'اتصال SMTP ناجح'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'فشل الاتصال: ' . $mail->ErrorInfo];
    }
}
