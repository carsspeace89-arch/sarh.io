<?php
// ⛔⛔⛔ LEGACY — HARD FROZEN — DO NOT MODIFY ⛔⛔⛔
// =============================================================
// includes/admin_layout.php - الهيكل المشترك لصفحات الإدارة
// =============================================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="<?= !empty($_COOKIE['attendance_theme']) && $_COOKIE['attendance_theme'] === 'dark' ? 'dark' : '' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#F97316">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?> - لوحة التحكم</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/loogo.png">
    <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/loogo.png">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/fonts/tajawal.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css?v=<?= filemtime(__DIR__.'/../assets/css/admin.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dark-mode.css?v=<?= filemtime(__DIR__.'/../assets/css/dark-mode.css') ?: time() ?>">
    <?php if (function_exists('\App\Middleware\CsrfProtection::metaTag')): ?>
    <?= \App\Middleware\CsrfProtection::metaTag() ?>
    <?php else: ?>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <?php endif; ?>
    <script>window.SITE_URL = '<?= SITE_URL ?>';</script>
    <script src="<?= SITE_URL ?>/assets/js/theme.js"></script>
</head>

<body>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <div class="brand-mark"><img src="<?= SITE_URL ?>/assets/images/loogo.png" alt="Logo" style="width:42px;height:42px;border-radius:10px;object-fit:cover"></div>
                <div>
                    <div class="brand-name"><?= SITE_NAME ?></div>
                    <div class="brand-sub">مرحباً، <?= htmlspecialchars($_SESSION['admin_name'] ?? 'مدير') ?></div>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item nav-item-home <?= ($activePage ?? '') === 'dashboard'  ? 'active' : '' ?>">
                <span class="nav-icon"><?= svgIcon('dashboard', 18) ?></span> لوحة التحكم
            </a>

            <!-- ═══ إدارة الموظفين ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['employees','employee-transfer','stars','employee-performance']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('employees', 16) ?></span>
                    <span class="nav-group-title">إدارة الموظفين</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="employees.php" class="nav-item <?= ($activePage ?? '') === 'employees'  ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('employees', 18) ?></span> الموظفين
                    </a>
                    <a href="employee-transfer.php" class="nav-item <?= ($activePage ?? '') === 'employee-transfer' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('transfer', 18) ?></span> نقل الموظفين
                    </a>
                    <a href="stars.php" class="nav-item <?= ($activePage ?? '') === 'stars' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('star', 18) ?></span> نظام النجوم
                    </a>
                    <a href="employee-performance.php" class="nav-item <?= ($activePage ?? '') === 'employee-performance' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('star', 18) ?></span> بطاقة الأداء
                    </a>
                </div>
            </div>

            <!-- ═══ الحضور والدوام ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['attendance','auto-attendance','late-report','report-absence','report-hours','report-overtime','report-early']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('attendance', 16) ?></span>
                    <span class="nav-group-title">الحضور والدوام</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="attendance.php" class="nav-item <?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('attendance', 18) ?></span> تقارير الحضور
                    </a>
                    <a href="auto-attendance.php" class="nav-item <?= ($activePage ?? '') === 'auto-attendance' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('robot', 18) ?></span> الحضور التلقائي
                    </a>
                    <a href="late-report.php" class="nav-item <?= ($activePage ?? '') === 'late-report' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('late', 18) ?></span> تقرير التأخير
                    </a>
                    <a href="report-absence.php" class="nav-item <?= ($activePage ?? '') === 'report-absence' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('absent', 18) ?></span> تقرير الغياب
                    </a>
                    <a href="report-hours.php" class="nav-item <?= ($activePage ?? '') === 'report-hours' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('attendance', 18) ?></span> ساعات العمل
                    </a>
                    <a href="report-overtime.php" class="nav-item <?= ($activePage ?? '') === 'report-overtime' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('late', 18) ?></span> تقرير الأوفرتايم
                    </a>
                    <a href="report-early.php" class="nav-item <?= ($activePage ?? '') === 'report-early' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('star', 18) ?></span> تقرير المتميزين
                    </a>
                </div>
            </div>

            <!-- ═══ التقارير والإحصائيات ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['report-monthly','report-branches','report-charts','report-payroll','report-builder','report-compare']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('chart', 16) ?></span>
                    <span class="nav-group-title">التقارير والإحصائيات</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="report-monthly.php" class="nav-item <?= ($activePage ?? '') === 'report-monthly' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('calendar', 18) ?></span> التقرير الشهري
                    </a>
                    <a href="report-branches.php" class="nav-item <?= ($activePage ?? '') === 'report-branches' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('compare', 18) ?></span> مقارنة الفروع
                    </a>
                    <a href="report-charts.php" class="nav-item <?= ($activePage ?? '') === 'report-charts' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('chart', 18) ?></span> التقارير البيانية
                    </a>
                    <a href="report-payroll.php" class="nav-item <?= ($activePage ?? '') === 'report-payroll' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('document', 18) ?></span> كشف الرواتب
                    </a>
                    <a href="report-builder.php" class="nav-item <?= ($activePage ?? '') === 'report-builder' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('settings', 18) ?></span> تقرير مخصص
                    </a>
                    <a href="report-compare.php" class="nav-item <?= ($activePage ?? '') === 'report-compare' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('compare', 18) ?></span> المقارنات
                    </a>
                </div>
            </div>

            <!-- ═══ التواصل والإشعارات ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['announcements','inbox-send','inbox-messages','scheduled-emails','notifications','complaints']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('bell', 16) ?></span>
                    <span class="nav-group-title">التواصل والإشعارات</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="announcements.php" class="nav-item <?= ($activePage ?? '') === 'announcements' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('bell', 18) ?></span> الإعلانات والأخبار
                    </a>
                    <a href="inbox-send.php" class="nav-item <?= ($activePage ?? '') === 'inbox-send' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('bell', 18) ?></span> إرسال للموظفين
                    </a>
                    <a href="inbox-messages.php" class="nav-item <?= ($activePage ?? '') === 'inbox-messages' ? 'active' : '' ?>" style="position:relative">
                        <span class="nav-icon"><?= svgIcon('audit', 18) ?></span> صناديق الوارد
                        <span class="notif-badge" id="sidebarInboxBadge" style="display:none;position:absolute;left:14px;top:8px;background:#10B981;color:#fff;font-size:.65rem;padding:1px 6px;border-radius:10px;font-weight:700"></span>
                    </a>
                    <a href="scheduled-emails.php" class="nav-item <?= ($activePage ?? '') === 'scheduled-emails' ? 'active' : '' ?>">
                        <span class="nav-icon">📧</span> المراسلات المجدولة
                    </a>
                    <a href="notifications.php" class="nav-item <?= ($activePage ?? '') === 'notifications' ? 'active' : '' ?>" style="position:relative">
                        <span class="nav-icon"><?= svgIcon('bell', 18) ?></span> الإشعارات
                        <span class="notif-badge" id="sidebarNotifBadge" style="display:none;position:absolute;left:14px;top:8px;background:#EF4444;color:#fff;font-size:.65rem;padding:1px 6px;border-radius:10px;font-weight:700"></span>
                    </a>
                    <a href="complaints.php" class="nav-item <?= ($activePage ?? '') === 'complaints' ? 'active' : '' ?>">
                        <span class="nav-icon">📢</span> شكاوى الموظفين
                    </a>
                </div>
            </div>

            <!-- ═══ الأمن والمراقبة ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['tampering','secret-reports','audit-log']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('shield', 16) ?></span>
                    <span class="nav-group-title">الأمن والمراقبة</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="tampering.php" class="nav-item <?= ($activePage ?? '') === 'tampering' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('shield', 18) ?></span> حالات التلاعب
                    </a>
                    <a href="secret-reports.php" class="nav-item <?= ($activePage ?? '') === 'secret-reports' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('secret', 18) ?></span> التقارير السرية
                    </a>
                    <a href="audit-log.php" class="nav-item <?= ($activePage ?? '') === 'audit-log' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('audit', 18) ?></span> سجل المراجعة
                    </a>
                </div>
            </div>

            <!-- ═══ الإدارة العامة ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['branches','leaves','documents-expiry','audio-library']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('branch', 16) ?></span>
                    <span class="nav-group-title">الإدارة العامة</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="branches.php" class="nav-item <?= ($activePage ?? '') === 'branches'   ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('branch', 18) ?></span> إدارة الفروع
                    </a>
                    <a href="leaves.php" class="nav-item <?= ($activePage ?? '') === 'leaves' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('leave', 18) ?></span> إدارة الإجازات
                    </a>
                    <a href="documents-expiry.php" class="nav-item <?= ($activePage ?? '') === 'documents-expiry' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('document', 18) ?></span> الوثائق المنتهية
                    </a>
                    <a href="audio-library.php" class="nav-item <?= ($activePage ?? '') === 'audio-library' ? 'active' : '' ?>">
                        <span class="nav-icon">🔊</span> المكتبة الصوتية
                    </a>
                </div>
            </div>

            <!-- ═══ النظام ═══ -->
            <div class="nav-group <?= in_array($activePage ?? '', ['backups','settings']) ? 'open' : '' ?>">
                <button class="nav-group-toggle">
                    <span class="nav-group-icon"><?= svgIcon('settings', 16) ?></span>
                    <span class="nav-group-title">النظام</span>
                    <span class="nav-group-arrow">‹</span>
                </button>
                <div class="nav-group-items">
                    <a href="backups.php" class="nav-item <?= ($activePage ?? '') === 'backups' ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('backup', 18) ?></span> النسخ الاحتياطي
                    </a>
                    <a href="settings.php" class="nav-item <?= ($activePage ?? '') === 'settings'   ? 'active' : '' ?>">
                        <span class="nav-icon"><?= svgIcon('settings', 18) ?></span> إعدادات النظام
                    </a>
                </div>
            </div>

        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <span class="nav-icon"><?= svgIcon('logout', 18) ?></span> تسجيل الخروج
            </a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburgerBtn" aria-label="القائمة">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
                    </svg>
                </button>
                <div class="topbar-page-icon">
                    <?php
                    $pageIcons = ['dashboard' => 'dashboard', 'employees' => 'employees', 'attendance' => 'attendance', 'settings' => 'settings', 'branches' => 'branch'];
                    echo svgIcon($pageIcons[$activePage ?? 'dashboard'] ?? 'dashboard', 20);
                    ?>
                </div>
                <h1><?= htmlspecialchars($pageTitle ?? '') ?></h1>
                <span class="topbar-badge">لوحة التحكم</span>
            </div>
            <div class="topbar-right">
                <span class="topbar-clock" id="topbarClock"></span>
                <a href="notifications.php" title="الإشعارات" style="position:relative;color:var(--text-primary);text-decoration:none;font-size:1.2rem;margin:0 6px">
                    <?= svgIcon('bell', 20) ?><span id="topbarNotifBadge" style="display:none;position:absolute;top:-4px;right:-6px;background:#EF4444;color:#fff;font-size:.6rem;padding:1px 5px;border-radius:8px;font-weight:700;min-width:14px;text-align:center"></span>
                </a>
                <button class="theme-toggle" id="themeToggleBtn" title="تبديل المظهر">
                    <span class="icon-moon">🌙</span>
                    <span class="icon-sun">☀️</span>
                </button>
                <a href="logout.php" class="topbar-logout-btn" title="تسجيل الخروج">
                    <?= svgIcon('logout', 18) ?>
                    <span class="topbar-logout-text">خروج</span>
                </a>
            </div>
        </div>
        <!-- Bottom Navigation (mobile) -->
        <nav class="bottom-nav" id="bottomNav">
            <a href="dashboard.php" class="bnav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <?= svgIcon('dashboard', 22) ?>
                <span>الرئيسية</span>
            </a>
            <a href="branches.php" class="bnav-item <?= ($activePage ?? '') === 'branches' ? 'active' : '' ?>">
                <?= svgIcon('branch', 22) ?>
                <span>الفروع</span>
            </a>
            <a href="employees.php" class="bnav-item <?= ($activePage ?? '') === 'employees' ? 'active' : '' ?>">
                <?= svgIcon('employees', 22) ?>
                <span>الموظفين</span>
            </a>
            <a href="attendance.php" class="bnav-item <?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">
                <?= svgIcon('attendance', 22) ?>
                <span>الحضور</span>
            </a>
            <a href="settings.php" class="bnav-item <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>">
                <?= svgIcon('settings', 22) ?>
                <span>الإعدادات</span>
            </a>
        </nav>
    <!-- Dropdown Overlay (mobile bottom-sheet) -->
    <div class="dropdown-overlay" id="dropdownOverlay"></div>

    <script>
    // ── Sidebar & Nav Groups (embedded for guaranteed loading) ──
    (function(){
        // Sidebar toggle
        function toggleSidebar(){
            document.getElementById('sidebar')?.classList.toggle('open');
            document.getElementById('sidebarOverlay')?.classList.toggle('show');
        }
        document.getElementById('hamburgerBtn')?.addEventListener('click', toggleSidebar);
        document.getElementById('sidebarOverlay')?.addEventListener('click', toggleSidebar);

        // Theme toggle
        document.getElementById('themeToggleBtn')?.addEventListener('click', function(){
            if(typeof ThemeManager !== 'undefined') ThemeManager.toggle();
        });

        // Nav group toggle (event delegation)
        document.querySelectorAll('.nav-group-toggle').forEach(function(btn){
            btn.addEventListener('click', function(){
                var group = this.closest('.nav-group');
                if(!group) return;
                group.classList.toggle('open');
                // Save state
                try {
                    var openGroups = [];
                    document.querySelectorAll('.nav-group.open .nav-group-title').forEach(function(t){
                        openGroups.push(t.textContent.trim());
                    });
                    localStorage.setItem('sidebar_open_groups', JSON.stringify(openGroups));
                } catch(e){}
            });
        });

        // Restore saved open groups
        try {
            var saved = JSON.parse(localStorage.getItem('sidebar_open_groups') || '[]');
            if(saved.length){
                document.querySelectorAll('.nav-group').forEach(function(g){
                    var title = g.querySelector('.nav-group-title');
                    if(title && saved.indexOf(title.textContent.trim()) !== -1){
                        g.classList.add('open');
                    }
                });
            }
        } catch(e){}

        // Clock
        function tick(){ var el=document.getElementById('topbarClock'); if(el) el.textContent=new Date().toLocaleString('ar-SA'); }
        tick(); setInterval(tick,1000);

        // Expose for admin_footer.php compatibility
        window.toggleSidebar = toggleSidebar;
    })();
    </script>

        <div class="content fade-in">