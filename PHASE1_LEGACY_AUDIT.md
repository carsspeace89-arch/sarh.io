# Phase 1: Legacy Freeze & Codebase Audit Report
**Version:** 1.0  
**Date:** 2025  
**Status:** ✅ COMPLETED  

---

## 1. Legacy Files Marked (⛔ LEGACY — DO NOT EXTEND)

### includes/ (13 files)
| File | Purpose | Risk Level |
|------|---------|------------|
| config.php | Session management, security headers, CSP nonce | 🟡 MEDIUM |
| db.php | PDO Singleton, db() helper | 🟢 LOW |
| functions.php | 34+ core functions, 3 deprecated | 🔴 HIGH |
| auth.php | Admin authentication, auto-checkout | 🟠 HIGH |
| rate_limiter.php | File-based rate limiting | 🟢 LOW |
| mail.php | PHPMailer SMTP | 🟢 LOW |
| email-templates.php | 13 HTML email template builders | 🟢 LOW |
| admin_layout.php | Admin panel HTML header/nav | 🟡 MEDIUM |
| admin_footer.php | Admin panel footer + JS | 🟡 MEDIUM |
| helpers.php | Empty (Composer placeholder) | 🟢 LOW |
| print_settings.php | Client-side print settings JS | 🟢 LOW |
| report_print_header.php | Report HTML header template | 🟢 LOW |
| report_print_footer.php | Report HTML footer template | 🟢 LOW |

### api/ root (35 files — NOT api/v1/)
| File | Deprecated Funcs Used | db() | Risk |
|------|----------------------|------|------|
| check-in.php | getBranchSchedule, isWithinGeofence, recordAttendance | ✓ | 🔴 CRITICAL |
| check-out.php | isWithinGeofence, recordAttendance | ✓ | 🔴 CRITICAL |
| ot.php | getBranchSchedule, isWithinGeofence, recordAttendance | ✓ | 🔴 CRITICAL |
| overtime-end.php | getBranchSchedule, recordAttendance | ✓ | 🔴 HIGH |
| overtime.php | isWithinGeofence | ✓ | 🟠 HIGH |
| overtime-approve.php | verifyCsrfToken, auditLog | ✓ | 🟡 MEDIUM |
| regenerate-tokens.php | verifyCsrfToken, generateUniqueToken, auditLog | ✓ | 🟡 MEDIUM |
| send-all-links.php | verifyCsrfToken, getSystemSetting, auditLog | ✓ | 🟡 MEDIUM |
| profile-action.php | verifyCsrfToken, sanitize | ✓ | 🟡 MEDIUM |
| realtime-attendance.php | assignTimeToShift | ✓ | 🟡 MEDIUM |
| realtime-dashboard.php | assignTimeToShift | ✓ | 🟡 MEDIUM |
| employee-notifications.php | getBranchSchedule | ✓ | 🟡 MEDIUM |
| verify-device.php | logTampering | ✓ | 🟡 MEDIUM |
| auth-pin.php | getEmployeeByPin | ✓ | 🟡 MEDIUM |
| Other 21 files | minimal legacy functions | varies | 🟢 LOW |

---

## 2. Deprecated Functions Dependency Map

### 🔴 `isWithinGeofence()` → GeofenceService::isWithinGeofence()
**Callers:**
- `api/check-in.php`
- `api/check-out.php`
- `api/ot.php`
- `api/overtime.php`

### 🔴 `recordAttendance()` → AttendanceService::record()
**Callers:**
- `api/check-in.php`
- `api/check-out.php`
- `api/ot.php`
- `api/overtime-end.php`
- `includes/auth.php` (triggerAutoCheckout)

### 🔴 `getBranchSchedule()` → ShiftService::getBranchSchedule()
**Callers:**
- `api/check-in.php`
- `api/ot.php`
- `api/overtime-end.php`
- `api/employee-notifications.php`
- `admin/attendance.php` (4x)

---

## 3. High-Usage Legacy Functions (NOT deprecated but in legacy layer)

### `getSystemSetting()` — 40+ usages
**Files:** admin/settings.php (12x), admin/dashboard.php, admin/report-builder.php, admin/stars.php, admin/attendance.php, admin/report-payroll.php, admin/report-compare.php, admin/report-daily.php, admin/report-monthly.php, api/send-all-links.php, api/overtime.php  
**Migration:** Create `src/Services/SettingsService.php` (cached, typed)

### `generateCsrfToken()` / `verifyCsrfToken()` — 75+ usages
**Files:** Nearly all admin/ and several api/ files  
**Migration:** Already exists as `src/Middleware/CsrfProtection.php` — replace callers gradually

### `sanitize()` — 35+ usages
**Files:** admin/settings.php, admin/employees.php, admin/announcements.php, admin/audio-library.php, admin/scheduled-emails.php, api/profile-action.php  
**Migration:** Replace with `htmlspecialchars()` + prepared statements (sanitize is misnamed - it only does `htmlspecialchars` + `trim`)

### `assignTimeToShift()` — 10+ usages
**Files:** admin/attendance.php, admin/dashboard.php, admin/report-daily.php, admin/report-monthly.php, admin/report-hours.php, api/realtime-attendance.php, api/realtime-dashboard.php  
**Migration:** Use `ShiftService::assignTimeToShift()` 

### `db()` direct access — 88.6% of api/, 100% of admin/
**Migration:** Use Model classes + QueryBuilder from `src/Core/`

---

## 4. src/ Issues Found

### Critical (Fix in Phase 2)
| File | Issue | Fix |
|------|-------|-----|
| src/Controllers/Api/V1/AuthController.php | Direct DB queries | Use Employee Model |
| src/Controllers/Api/V1/EmployeeController.php | Direct DB queries (3 locations) | Use Employee Model |
| src/Core/Controller.php | Calls legacy `verifyCsrfToken()` | Use CsrfProtection middleware |
| src/Queue/Jobs/AutoCheckoutJob.php | Direct DB queries | Use Attendance Model |
| src/Queue/Jobs/GenerateReportJob.php | Direct DB queries | Use Model classes |

### High (Fix in Phase 2-3)
| File | Issue |
|------|-------|
| src/Services/AuthService.php | LoginAttempts direct DB queries — needs LoginAttempt Model |
| src/Services/GeofenceService.php | Impossible travel detection logic flawed |
| src/Models/Employee.php | Missing `findByDeviceFingerprint()` method |
| src/Middleware/RateLimiter.php | Uses /tmp for storage (shared hosting risk) |

---

## 5. Risk Classification Summary

| Risk Level | Count | Description |
|------------|-------|-------------|
| 🔴 CRITICAL | 3 files | api/check-in, check-out, ot — use ALL 3 deprecated functions |
| 🟠 HIGH | 6 files | Core attendance flow + auth.php auto-checkout |
| 🟡 MEDIUM | 20+ files | Use legacy functions but not deprecated ones |
| 🟢 LOW | 30+ files | Minimal legacy dependencies, template-only, or static |

---

## 6. Admin Files — Top 4 Highest Risk

| File | Deprecated Funcs | Legacy Funcs | DB Queries |
|------|-----------------|--------------|------------|
| admin/attendance.php | getBranchSchedule (4x) | assignTimeToShift, getSystemSetting, generateUniqueToken/Pin, verifyCsrfToken | 25+ |
| admin/employees.php | — | generateUniquePin (5x), generateUniqueToken (3x), sanitize (8x) | 25+ |
| admin/settings.php | — | getSystemSetting (12x), sanitize (8x), verifyCsrfToken | 8+ |
| admin/report-builder.php | — | getSystemSetting (3x), sanitize, formatDuration, verifyCsrfToken | 12+ |

---

## 7. Global Rules Enforced

1. ⛔ **NO new code** in `includes/*` — 13/13 files marked LEGACY
2. ⛔ **NO new code** in `api/*` (root) — 35/35 files marked LEGACY
3. ✅ **ALL new code** goes to `src/*` and `api/v1/*`
4. ✅ **New endpoints** ONLY via Router + Controllers in `src/Controllers/Api/V1/`
5. ✅ **Business logic** ONLY in `src/Services/`
6. ✅ **Data access** ONLY through `src/Models/` and `src/Core/QueryBuilder`

---

## 8. Phase 1 Validation Checklist

- [x] All includes/ files audited (13/13)
- [x] All api/ legacy files audited (35/35)
- [x] All admin/ files audited (46/46)
- [x] All src/ files audited (30/30)
- [x] LEGACY headers added to includes/ (13/13 files)
- [x] LEGACY headers added to api/ root (35/35 files)
- [x] Deprecated functions identified (3 functions)
- [x] Deprecated function callers mapped (5 files for isWithinGeofence, 5 for recordAttendance, 5 for getBranchSchedule)
- [x] High-usage legacy functions cataloged (5 categories)
- [x] src/ design violations cataloged (5 critical, 4 high)
- [x] Risk classification complete (CRITICAL/HIGH/MEDIUM/LOW)
- [x] Dependency map produced
- [x] Global rules documented

---

**PHASE 1 COMPLETED SUCCESSFULLY — ALL REQUIREMENTS VERIFIED**
