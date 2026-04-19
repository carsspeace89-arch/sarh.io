-- ═══════════════════════════════════════════════════════
-- جدول المكتبة الصوتية
-- ═══════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS audio_library (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'اسم المقطع الصوتي',
    filename VARCHAR(500) NOT NULL COMMENT 'اسم الملف في مجلد assets/audio/',
    original_name VARCHAR(500) DEFAULT NULL COMMENT 'الاسم الأصلي للملف',
    file_size INT DEFAULT 0 COMMENT 'حجم الملف بالبايت',
    duration VARCHAR(20) DEFAULT NULL COMMENT 'مدة المقطع (اختياري)',
    category ENUM('geofence_enter','geofence_exit','checkin_success','checkout_success','notification','custom') NOT NULL DEFAULT 'custom' COMMENT 'تصنيف المقطع',
    is_active TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'هل هو المقطع المفعّل لهذا التصنيف؟',
    play_mode ENUM('once','loop') NOT NULL DEFAULT 'once' COMMENT 'طريقة التشغيل',
    volume DECIMAL(3,2) NOT NULL DEFAULT 1.00 COMMENT 'مستوى الصوت 0.00 - 1.00',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_active (category, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة الملف الصوتي الحالي
INSERT IGNORE INTO audio_library (title, filename, original_name, category, is_active, play_mode, volume) VALUES
('Drop It Like It''s Hot', 'snoop-dogg-drop-it-like-its-hot.mp3', 'snoop-dogg-drop-it-like-its-hot.mp3', 'geofence_enter', 1, 'loop', 1.00);
