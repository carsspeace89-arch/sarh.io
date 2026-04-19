-- =====================================================
-- جدول الإعلانات والأخبار والتعميمات
-- =====================================================

CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL COMMENT 'عنوان الإعلان',
  `content` TEXT NOT NULL COMMENT 'محتوى الإعلان',
  `type` ENUM('news', 'announcement', 'circular', 'alert', 'event') DEFAULT 'announcement' COMMENT 'نوع الإعلان',
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' COMMENT 'أولوية الإعلان',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT 'نشط/غير نشط',
  `show_in_ticker` TINYINT(1) DEFAULT 1 COMMENT 'عرض في الشريط المتحرك',
  `icon` VARCHAR(50) DEFAULT '📢' COMMENT 'أيقونة الإعلان',
  `color` VARCHAR(20) DEFAULT '#F97316' COMMENT 'لون الإعلان',
  `start_date` DATETIME DEFAULT NULL COMMENT 'تاريخ بداية العرض',
  `end_date` DATETIME DEFAULT NULL COMMENT 'تاريخ نهاية العرض',
  `target_audience` ENUM('all', 'employees', 'specific_branch', 'specific_department') DEFAULT 'all' COMMENT 'الجمهور المستهدف',
  `branch_id` INT UNSIGNED DEFAULT NULL COMMENT 'الفرع المستهدف (إن وجد)',
  `created_by` INT UNSIGNED DEFAULT NULL COMMENT 'المستخدم الذي أنشأ الإعلان',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`),
  INDEX `idx_ticker` (`show_in_ticker`),
  INDEX `idx_dates` (`start_date`, `end_date`),
  INDEX `idx_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج بيانات تجريبية
INSERT INTO `announcements` (`title`, `content`, `type`, `priority`, `icon`, `color`, `show_in_ticker`) VALUES
('مواعيد العمل الجديدة', 'تم تحديث مواعيد العمل لتكون من الساعة 8 صباحاً حتى 4 مساءً', 'announcement', 'high', '⏰', '#F97316', 1),
('عطلة رسمية', 'يوم الخميس القادم عطلة رسمية بمناسبة اليوم الوطني', 'news', 'urgent', '🎉', '#EF4444', 1),
('تدريب إلزامي', 'دورة تدريبية للسلامة المهنية يوم الأحد في الساعة 10 صباحاً', 'event', 'normal', '📚', '#3B82F6', 1),
('صيانة النظام', 'سيتم إجراء صيانة للنظام يوم السبت من 12-2 ظهراً', 'alert', 'high', '🔧', '#EAB308', 1);
