# GitHub Actions Workflows

## 📋 Workflows المتاحة

### 1. Deploy to Hostinger (`deploy.yml`)
- **متى يعمل**: عند الدفع لـ `master`
- **الوظيفة**: 
  - تشغيل الاختبارات
  - النشر التلقائي على Hostinger
  - تحديث composer dependencies
  - تشغيل migrations
  - مسح الكاش

### 2. Run Tests (`tests.yml`)
- **متى يعمل**: على كل Pull Request وعند الدفع لـ `develop`
- **الوظيفة**:
  - تشغيل PHPUnit على PHP 8.1 & 8.2
  - فحص أمان المكتبات
  - فحص Code Style (PSR-12)

## 🔐 إعداد GitHub Secrets

لتفعيل النشر التلقائي، أضف هذه الـ Secrets في GitHub:

1. اذهب إلى: `https://github.com/carsspeace89-arch/sarh.io/settings/secrets/actions`
2. اضغط **New repository secret**
3. أضف هذه القيم:

| Secret Name | القيمة | الوصف |
|------------|--------|-------|
| `HOST` | `fr-int-web1580.main-hosting.eu` | عنوان سيرفر Hostinger |
| `USERNAME` | `u307296675` | اسم المستخدم |
| `SSH_KEY` | [المفتاح الخاص] | محتوى ملف `~/.ssh/id_ed25519` |
| `PORT` | `65002` | منفذ SSH |

### الحصول على SSH_KEY:

```bash
# على سيرفر Hostinger
cat ~/.ssh/id_ed25519
```

انسخ **كامل المحتوى** (بما في ذلك السطر الأول والأخير):
```
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

⚠️ **تحذير**: لا تشارك المفتاح الخاص أبدًا!

## 🚀 الاستخدام

### النشر التلقائي:
```bash
git add .
git commit -m "feat: add new feature"
git push origin master  # سيتم النشر تلقائياً
```

### تشغيل يدوي:
اذهب إلى: `Actions` → `Deploy to Hostinger` → `Run workflow`

## 📊 مراقبة النشر

راقب حالة النشر في:
```
https://github.com/carsspeace89-arch/sarh.io/actions
```
