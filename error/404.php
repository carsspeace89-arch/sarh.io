<?php
// أي صفحة غير موجودة → تحويل مباشر لبوابة الحضور
require_once __DIR__ . '/../includes/config.php';
header('Location: ' . SITE_URL . '/employee/index.php');
exit;
