# 📧 نظام المراسلات المجدولة - Scheduled Emails System

## نظرة عامة

نظام متكامل لإرسال التقارير الدورية تلقائياً عبر البريد الإلكتروني للإدارة والمسؤولين.

## المميزات

✅ **إدارة سهلة**: واجهة بسيطة لإنشاء وإدارة المراسلات المجدولة  
✅ **تكرارات متعددة**: يومي، أسبوعي، شهري  
✅ **أنواع تقارير متنوعة**: حضور، تأخير، غياب، عمل إضافي، رواتب، وغيرها  
✅ **عدة مستلمين**: إرسال لعدد غير محدود من الإيميلات  
✅ **سجل كامل**: تتبع جميع عمليات الإرسال والأخطاء  
✅ **تفعيل/إيقاف**: التحكم في كل مراسلة بشكل مستقل  

## الملفات المضافة

```
admin/scheduled-emails.php           # صفحة الإدارة الرئيسية (مع حماية ضد الجداول المفقودة)
admin/setup-scheduled-emails.php     # صفحة التثبيت التلقائي
admin/test-scheduled-db.php          # اختبار قاعدة البيانات
migrations/007_scheduled_emails.sql  # جداول قاعدة البيانات
cron/send-scheduled-emails.php       # مهمة الإرسال التلقائي
```

## التثبيت السريع (موصى به)

### الطريقة 1: التثبيت التلقائي (الأسهل)

1. افتح المتصفح وانتقل إلى:
   ```
   https://sarh.io/admin/setup-scheduled-emails.php
   ```

2. اضغط على زر "بدء التثبيت"

3. انتظر رسالة النجاح ثم افتح صفحة المراسلات

### الطريقة 2: تشغيل SQL يدوياً

قم بتشغيل ملف SQL التالي في قاعدة البيانات:

```bash
mysql -u username -p database_name < migrations/007_scheduled_emails.sql
```

أو من phpMyAdmin:
- افتح قاعدة البيانات
- اختر "SQL"
- انسخ محتوى ملف `007_scheduled_emails.sql` والصقه
- اضغط "Go"

## استخدام النظام

افتح صفحة المراسلات المجدولة:
```
https://sarh.io/admin/scheduled-emails.php
```

**ملاحظة مهمة:** تأكد من استخدام slash واحد فقط قبل admin (ليس //)
- ✅ الصحيح: `https://sarh.io/admin/scheduled-emails.php`
- ❌ الخطأ: `https://sarh.io//admin/scheduled-emails.php`

## إعداد Cron Job (للإرسال التلقائي)

أضف السطر التالي إلى crontab للتشغيل كل 15 دقيقة:

```bash
*/15 * * * * /usr/bin/php /home/u307296675/domains/sarh.io/public_html/cron/send-scheduled-emails.php
```

أو عبر cPanel:
1. اذهب إلى **Cron Jobs**
2. أضف مهمة جديدة:
   - Command: `/usr/bin/php /home/u307296675/domains/sarh.io/public_html/cron/send-scheduled-emails.php`
   - Interval: كل 15 دقيقة

### 3. إعداد SMTP (اختياري - للإنتاج)

> **ملاحظة هامة**: النظام الحالي يستخدم `mail()` الافتراضية في PHP. للحصول على موثوقية أعلى في الإنتاج، يُنصح بشدة باستخدام SMTP.

#### خيار 1: استخدام PHPMailer (موصى به)

```bash
cd /home/u307296675/domains/sarh.io/public_html
composer require phpmailer/phpmailer
```

ثم عدّل الدالة `sendEmailWithPHP()` في ملف `cron/send-scheduled-emails.php`:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailWithPHP($recipients, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    
    try {
        // إعدادات SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // أو خادم SMTP الخاص بك
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // البيانات الأساسية
        $mail->setFrom('noreply@sarh.io', 'نظام الحضور');
        $mail->addReplyTo('support@sarh.io', 'الدعم الفني');
        
        // المستلمون
        foreach ($recipients as $email) {
            $mail->addAddress($email);
        }
        
        // المحتوى
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
```

## الاستخدام

### 1. الوصول إلى النظام

من القائمة الجانبية في لوحة التحكم، اختر:  
**الإدارة** → **📧 المراسلات المجدولة**

أو مباشرة:  
`https://sarh.io/admin/scheduled-emails.php`

### 2. إضافة مراسلة جديدة

1. اضغط **➕ إضافة مراسلة جديدة**
2. املأ البيانات:
   - **العنوان**: مثل "تقرير الحضور اليومي"
   - **نوع التقرير**: اختر من القائمة
   - **التكرار**: يومي / أسبوعي / شهري
   - **وقت الإرسال**: مثل 08:00 صباحاً
   - **المستلمون**: أدخل الإيميلات مفصولة بفاصلة
3. اضغط **💾 حفظ**

### 3. إدارة المراسلات

- **✏️ تعديل**: تعديل بيانات المراسلة
- **▶️/⏸️ تفعيل/إيقاف**: تفعيل أو إيقاف مؤقت
- **🗑️ حذف**: حذف نهائي للمراسلة

### 4. عرض سجل الإرسال

من تبويب **📝 سجل الإرسال** يمكنك مشاهدة:
- جميع الرسائل المرسلة
- حالة كل رسالة (نجح/فشل)
- تاريخ ووقت الإرسال
- رسائل الأخطاء (إن وجدت)

## أنواع التقارير المتاحة

| النوع | الوصف |
|------|-------|
| **تقرير الحضور اليومي** | قائمة جميع تسجيلات الدخول والخروج لليوم |
| **تقرير المتأخرين** | الموظفون الذين تأخروا عن موعد العمل |
| **تقرير الغائبين** | الموظفون الغائبون بدون إجازة |
| **تقرير العمل الإضافي** | ساعات العمل الإضافية |
| **تقرير الحضور الشهري** | ملخص الحضور لكل موظف خلال الشهر |
| **تقرير الرواتب** | كشف الرواتب الشهري |
| **ملخص الحضور** | إحصائيات عامة (إجمالي، حاضرين، غائبين، متأخرين) |

## أمثلة للاستخدام

### مثال 1: تقرير يومي للإدارة

- **العنوان**: تقرير الحضور اليومي
- **التكرار**: يومي
- **الوقت**: 08:00 صباحاً
- **المستلمون**: `manager@company.com, hr@company.com`

### مثال 2: تقرير المتأخرين الأسبوعي

- **العنوان**: تقرير المتأخرين الأسبوعي
- **التكرار**: أسبوعي (الأحد)
- **الوقت**: 09:00 صباحاً
- **المستلمون**: `admin@company.com`

### مثال 3: كشف الرواتب الشهري

- **العنوان**: كشف الرواتب الشهري
- **التكرار**: شهري (اليوم 1)
- **الوقت**: 10:00 صباحاً
- **المستلمون**: `finance@company.com, ceo@company.com`

## الجداول في قاعدة البيانات

### `scheduled_emails`
يخزن معلومات المراسلات المجدولة:
- `id`: المعرف الفريد
- `title`: عنوان المراسلة
- `report_type`: نوع التقرير
- `frequency`: التكرار (daily/weekly/monthly)
- `send_time`: وقت الإرسال
- `day_of_week`: يوم الأسبوع (للأسبوعي)
- `day_of_month`: يوم الشهر (للشهري)
- `recipients`: قائمة الإيميلات
- `is_active`: حالة التفعيل
- `last_sent_at`: آخر وقت إرسال

### `email_send_log`
سجل جميع عمليات الإرسال:
- `id`: المعرف
- `schedule_id`: معرف المراسلة
- `recipients`: المستلمون الفعليون
- `subject`: موضوع الرسالة
- `status`: الحالة (sent/failed)
- `error_message`: رسالة الخطأ
- `sent_at`: تاريخ الإرسال

## استكشاف الأخطاء

### لا يتم إرسال أي رسائل؟

1. **تأكد من cron job**:
   ```bash
   crontab -l
   ```
   يجب أن ترى السطر المضاف

2. **تشغيل يدوي للاختبار**:
   ```bash
   php /home/u307296675/domains/sarh.io/public_html/cron/send-scheduled-emails.php
   ```

3. **فحص الأخطاء**:
   ```bash
   tail -f /home/u307296675/domains/sarh.io/public_html/error_log
   ```

### الرسائل تُرسل لكن لا تصل؟

1. **افحص مجلد SPAM**
2. **استخدم SMTP بدلاً من mail()** (راجع قسم الإعداد أعلاه)
3. **تحقق من إعدادات SPF/DKIM للدومين**

### الوقت غير صحيح؟

تأكد من المنطقة الزمنية في `config.php`:
```php
date_default_timezone_set('Asia/Riyadh');
```

## الأمان

✅ **CSRF Protection**: جميع العمليات محمية ضد CSRF  
✅ **Authentication**: يتطلب تسجيل دخول Admin  
✅ **Input Validation**: تحقق من صحة جميع المدخلات  
✅ **SQL Injection Protection**: استخدام Prepared Statements  
✅ **Cron Secret**: حماية endpoint cron من الطلبات العشوائية  

## الترقيات المستقبلية

🔜 **قوالب مخصصة**: إنشاء قوالب HTML مخصصة للبريد  
🔜 **ملفات مرفقة**: إرفاق PDF للتقارير  
🔜 **إشعارات SMS**: دعم الرسائل النصية  
🔜 **تقارير متقدمة**: تقارير مخصصة بفلاتر متقدمة  
🔜 **دعم WhatsApp**: إرسال عبر واتساب للأعمال  

## الدعم

للاستفسارات والدعم الفني، تواصل مع:
- البريد الإلكتروني: support@sarh.io
- التوثيق الكامل: راجع ملف `التوثيق الكامل.md`

---

تم التطوير بواسطة فريق sarh.io 🚀  
التاريخ: أبريل 2026
