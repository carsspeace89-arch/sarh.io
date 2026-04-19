#!/bin/bash

# =================================================================
# سكريبت تثبيت نظام المراسلات المجدولة
# Scheduled Emails System Installation Script
# =================================================================

echo "=================================================="
echo "📧 تثبيت نظام المراسلات المجدولة"
echo "=================================================="
echo ""

# المتغيرات
DB_USER="u307296675_whats"
DB_NAME="u307296675_whats"
WEB_ROOT="/home/u307296675/domains/sarh.io/public_html"

# الألوان
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. إنشاء الجداول
echo -e "${YELLOW}الخطوة 1: إنشاء جداول قاعدة البيانات...${NC}"
mysql -u $DB_USER -p $DB_NAME < $WEB_ROOT/migrations/007_scheduled_emails.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ تم إنشاء الجداول بنجاح${NC}"
else
    echo -e "${RED}✗ فشل إنشاء الجداول${NC}"
    exit 1
fi

echo ""

# 2. التحقق من صلاحيات الملفات
echo -e "${YELLOW}الخطوة 2: التحقق من صلاحيات الملفات...${NC}"
chmod +x $WEB_ROOT/cron/send-scheduled-emails.php
echo -e "${GREEN}✓ تم ضبط الصلاحيات${NC}"

echo ""

# 3. اختبار الاتصال
echo -e "${YELLOW}الخطوة 3: اختبار النظام...${NC}"
php $WEB_ROOT/cron/send-scheduled-emails.php

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ النظام يعمل بشكل صحيح${NC}"
else
    echo -e "${RED}✗ هناك خطأ في التشغيل${NC}"
    exit 1
fi

echo ""

# 4. إضافة Cron Job
echo -e "${YELLOW}الخطوة 4: إعداد Cron Job...${NC}"
echo "يرجى إضافة السطر التالي إلى crontab يدوياً:"
echo ""
echo -e "${GREEN}*/15 * * * * /usr/bin/php $WEB_ROOT/cron/send-scheduled-emails.php${NC}"
echo ""
echo "لإضافته، قم بتشغيل الأمر التالي ثم الصق السطر أعلاه:"
echo "crontab -e"

echo ""
echo "=================================================="
echo -e "${GREEN}✅ اكتمل التثبيت بنجاح!${NC}"
echo "=================================================="
echo ""
echo "الخطوات التالية:"
echo "1. قم بإضافة cron job كما هو موضح أعلاه"
echo "2. افتح: https://sarh.io/admin/scheduled-emails.php"
echo "3. أضف أول مراسلة مجدولة"
echo ""
echo "للمزيد من المعلومات، راجع: SCHEDULED_EMAILS_README.md"
echo ""
