-- ================================================================
-- Migration 007: Scheduled Emails System - نظام المراسلات المجدولة
-- ================================================================
-- يوفر هذا التحديث جداول لإدارة المراسلات التلقائية المجدولة
-- تاريخ الإنشاء: 2026-04-14
-- ================================================================

-- جدول المراسلات المجدولة
CREATE TABLE IF NOT EXISTS `scheduled_emails` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'عنوان المراسلة',
  `report_type` VARCHAR(50) NOT NULL COMMENT 'نوع التقرير: daily, late, absent, overtime, monthly, payroll, summary',
  `frequency` ENUM('daily', 'weekly', 'monthly') NOT NULL DEFAULT 'daily' COMMENT 'التكرار',
  `send_time` TIME NOT NULL DEFAULT '08:00:00' COMMENT 'وقت الإرسال اليومي',
  `day_of_week` TINYINT(1) NULL DEFAULT NULL COMMENT 'يوم الأسبوع للإرسال الأسبوعي: 0=أحد, 1=إثنين, إلخ',
  `day_of_month` TINYINT(2) NULL DEFAULT NULL COMMENT 'يوم الشهر للإرسال الشهري: 1-28',
  `recipients` TEXT NOT NULL COMMENT 'قائمة الإيميلات المستلمة مفصولة بفواصل',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'تفعيل/إيقاف المراسلة',
  `filters` TEXT NULL COMMENT 'فلاتر إضافية بصيغة JSON',
  `last_sent_at` DATETIME NULL DEFAULT NULL COMMENT 'آخر وقت تم فيه الإرسال',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_active_frequency` (`is_active`, `frequency`),
  INDEX `idx_send_time` (`send_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المراسلات المجدولة';

-- جدول سجل إرسال البريد
CREATE TABLE IF NOT EXISTS `email_send_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'معرف المراسلة المجدولة',
  `recipients` TEXT NOT NULL COMMENT 'المستلمون الفعليون',
  `subject` VARCHAR(500) NOT NULL COMMENT 'موضوع الرسالة',
  `status` ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
  `error_message` TEXT NULL COMMENT 'رسالة الخطأ في حالة الفشل',
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_schedule_id` (`schedule_id`),
  INDEX `idx_sent_at` (`sent_at`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`schedule_id`) REFERENCES `scheduled_emails` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='سجل إرسال البريد الإلكتروني';

-- إضافة بيانات تجريبية (اختياري)
INSERT INTO `scheduled_emails` 
  (`title`, `report_type`, `frequency`, `send_time`, `recipients`, `is_active`) 
VALUES 
  ('تقرير الحضور اليومي', 'daily', 'daily', '08:00:00', 'admin@sarh.io', 1),
  ('تقرير المتأخرين الأسبوعي', 'late', 'weekly', '09:00:00', 'manager@sarh.io', 1)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ================================================================
-- ملاحظات:
-- 1. يتم تشغيل ملف cron/send-scheduled-emails.php كل ساعة
-- 2. التحقق من وقت الإرسال يتم في الكود البرمجي
-- 3. يدعم النظام ثلاثة أنواع من التكرار: يومي، أسبوعي، شهري
-- ================================================================
