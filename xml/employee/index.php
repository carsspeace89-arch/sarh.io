<?php
// تحويل من /xml/employee/ إلى /employee/ مع الحفاظ على المعاملات
$query = $_SERVER['QUERY_STRING'] ?? '';
$redirect = '/employee/' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $redirect, true, 301);
exit;
