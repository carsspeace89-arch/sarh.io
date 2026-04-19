-- =====================================================
-- Migration: نظام الشكاوى - Complaints System
-- =====================================================

CREATE TABLE IF NOT EXISTS `complaints` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT UNSIGNED NOT NULL,
    `complaint_type` ENUM('person','branch','group','other') NOT NULL DEFAULT 'other',
    `target_name` VARCHAR(255) DEFAULT NULL COMMENT 'اسم الشخص أو الفرع أو المجموعة',
    `subject` VARCHAR(500) NOT NULL COMMENT 'عنوان الشكوى',
    `body` TEXT NOT NULL COMMENT 'تفاصيل الشكوى',
    `status` ENUM('pending','reviewing','resolved','rejected') NOT NULL DEFAULT 'pending',
    `admin_reply` TEXT DEFAULT NULL,
    `admin_id` INT UNSIGNED DEFAULT NULL,
    `attachments` TEXT DEFAULT NULL COMMENT 'JSON array of file paths',
    `resolved_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
