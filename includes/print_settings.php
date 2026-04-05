<?php
/**
 * Print Settings Panel - لوحة إعدادات الطباعة
 * Include in any report page BEFORE admin_footer.php
 * Provides controls for: paper size, orientation, scale, margins, width, header/footer
 * Settings are saved per-page in localStorage
 */
$_printPageKey = basename($_SERVER['SCRIPT_NAME'], '.php');
?>

<!-- زر إعدادات الطباعة العائم -->
<button class="ps-fab no-print" onclick="togglePrintSettings()" title="إعدادات الطباعة">🖨️</button>

<!-- لوحة إعدادات الطباعة -->
<div class="ps-panel no-print" id="psPrintPanel">
    <div class="ps-header">
        <span>🖨️ إعدادات الطباعة</span>
        <button class="ps-close" onclick="togglePrintSettings()">✕</button>
    </div>

    <div class="ps-body">
        <!-- حجم الورقة -->
        <div class="ps-row">
            <label>📄 الورقة</label>
            <select id="psPaper" onchange="applyPrintSettings()">
                <option value="A4-portrait">A4 عمودي</option>
                <option value="A4-landscape">A4 أفقي</option>
                <option value="Letter-portrait">Letter عمودي</option>
                <option value="Letter-landscape">Letter أفقي</option>
                <option value="Legal-portrait">Legal عمودي</option>
            </select>
        </div>

        <!-- التكبير/التصغير -->
        <div class="ps-row">
            <label>🔍 التكبير <span id="psScaleVal">100%</span></label>
            <input type="range" id="psScale" min="50" max="150" value="100" step="5" oninput="applyPrintSettings()">
        </div>

        <!-- الهوامش -->
        <div class="ps-row">
            <label>📐 الهوامش</label>
            <div class="ps-btn-group" id="psMarginGroup">
                <button data-val="none" onclick="setMargin('none')">بدون</button>
                <button data-val="narrow" onclick="setMargin('narrow')">ضيقة</button>
                <button data-val="normal" onclick="setMargin('normal')" class="active">عادية</button>
                <button data-val="wide" onclick="setMargin('wide')">واسعة</button>
            </div>
        </div>

        <!-- عرض المحتوى -->
        <div class="ps-row">
            <label>↔️ عرض المحتوى</label>
            <div class="ps-btn-group" id="psWidthGroup">
                <button data-val="full" onclick="setWidth('full')" class="active">كامل</button>
                <button data-val="auto" onclick="setWidth('auto')">تلقائي</button>
                <button data-val="190" onclick="setWidth('190')">190mm</button>
                <button data-val="180" onclick="setWidth('180')">180mm</button>
            </div>
        </div>

        <!-- حجم الخط -->
        <div class="ps-row">
            <label>🔤 حجم الخط <span id="psFontVal">تلقائي</span></label>
            <input type="range" id="psFontSize" min="5" max="14" value="0" step="0.5" oninput="applyPrintSettings()">
        </div>

        <!-- الترويسة والتذييل -->
        <div class="ps-row ps-toggles">
            <label><input type="checkbox" id="psShowHeader" checked onchange="applyPrintSettings()"> إظهار ترويسة</label>
            <label><input type="checkbox" id="psShowFooter" checked onchange="applyPrintSettings()"> إظهار تذييل</label>
        </div>

        <hr style="border:0;border-top:1px solid var(--border);margin:10px 0">

        <!-- أزرار -->
        <div class="ps-actions">
            <button class="ps-btn-print" onclick="doPrint()">🖨️ طباعة</button>
            <button class="ps-btn-reset" onclick="resetPrintSettings()">🔄 إعادة تعيين</button>
        </div>

        <div class="ps-preview-info" id="psPreviewInfo"></div>
    </div>
</div>

<!-- الأنماط الديناميكية للطباعة -->
<style id="psDynamicStyle"></style>

<style>
/* ── Print Settings Floating Button ── */
.ps-fab {
    position: fixed; bottom: 80px; left: 20px; z-index: 9990;
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, var(--royal-navy, #0f1b33), #1a2d52);
    color: #fff; border: 2px solid var(--royal-gold, #c9a84c);
    font-size: 1.2rem; cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,.3);
    transition: .2s; display: flex; align-items: center; justify-content: center;
}
.ps-fab:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(201,168,76,.4); }

/* ── Print Settings Panel ── */
.ps-panel {
    display: none; position: fixed; bottom: 80px; left: 80px; z-index: 9991;
    width: 340px; background: var(--surface1, #fff);
    border: 2px solid var(--royal-gold, #c9a84c);
    border-radius: 14px; box-shadow: 0 15px 50px rgba(0,0,0,.25);
    direction: rtl; font-family: inherit;
    max-height: 80vh; overflow-y: auto;
}
.ps-panel.show { display: block; animation: psSlideUp .2s ease; }
@keyframes psSlideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.ps-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; font-weight: 800; font-size: .92rem;
    background: linear-gradient(135deg, var(--royal-navy, #0f1b33), #1a2d52);
    color: var(--royal-gold-light, #e8d9a0);
    border-radius: 12px 12px 0 0;
}
.ps-close { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; padding: 0; }

.ps-body { padding: 14px 16px; }
.ps-row { margin-bottom: 12px; }
.ps-row > label { display: block; font-size: .78rem; font-weight: 700; color: var(--text3); margin-bottom: 5px; }
.ps-row select {
    width: 100%; padding: 8px 10px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: .84rem; font-family: inherit; background: var(--surface2); color: var(--text1);
}
.ps-row input[type="range"] { width: 100%; accent-color: var(--royal-gold, #c9a84c); }

.ps-btn-group { display: flex; gap: 4px; flex-wrap: wrap; }
.ps-btn-group button {
    flex: 1; padding: 6px 8px; border: 1.5px solid var(--border); border-radius: 6px;
    background: var(--surface2); color: var(--text2); font-size: .76rem; font-weight: 600;
    font-family: inherit; cursor: pointer; transition: .15s; min-width: 50px;
}
.ps-btn-group button.active {
    background: var(--royal-navy, #0f1b33); color: var(--royal-gold-light, #e8d9a0);
    border-color: var(--royal-gold, #c9a84c);
}
.ps-btn-group button:hover:not(.active) { border-color: var(--royal-gold, #c9a84c); }

.ps-toggles { display: flex; gap: 14px; }
.ps-toggles label { font-size: .82rem; font-weight: 600; color: var(--text2); display: flex; align-items: center; gap: 5px; }
.ps-toggles input[type="checkbox"] { accent-color: var(--royal-gold, #c9a84c); }

.ps-actions { display: flex; gap: 8px; }
.ps-btn-print {
    flex: 2; padding: 10px; border-radius: 8px; border: none;
    background: linear-gradient(135deg, #c9a84c, #a88a2a); color: #fff;
    font-weight: 800; font-size: .88rem; font-family: inherit; cursor: pointer;
    transition: .2s;
}
.ps-btn-print:hover { box-shadow: 0 4px 12px rgba(201,168,76,.4); }
.ps-btn-reset {
    flex: 1; padding: 10px; border-radius: 8px;
    border: 1.5px solid var(--border); background: var(--surface2); color: var(--text2);
    font-weight: 600; font-size: .82rem; font-family: inherit; cursor: pointer;
}

.ps-preview-info { font-size: .72rem; color: var(--text3); margin-top: 10px; text-align: center; }

@media (max-width: 600px) {
    .ps-panel { left: 10px; right: 10px; width: auto; bottom: 70px; }
    .ps-fab { bottom: 70px; left: 10px; }
}
</style>

<script>
(function(){
    const PAGE_KEY = 'ps_<?= $_printPageKey ?>';

    const PAPER_SIZES = {
        'A4-portrait':      { size: 'A4 portrait',      w: '210mm' },
        'A4-landscape':     { size: 'A4 landscape',     w: '297mm' },
        'Letter-portrait':  { size: 'letter portrait',   w: '216mm' },
        'Letter-landscape': { size: 'letter landscape', w: '279mm' },
        'Legal-portrait':   { size: 'legal portrait',   w: '216mm' },
    };

    const MARGINS = {
        none:   '0mm',
        narrow: '4mm 5mm 6mm 5mm',
        normal: '8mm 7mm 10mm 7mm',
        wide:   '12mm 10mm 15mm 10mm',
    };

    const defaults = {
        paper: 'A4-portrait', scale: 100, margin: 'normal',
        width: 'full', fontSize: 0, showHeader: true, showFooter: true
    };

    function load() {
        try { return { ...defaults, ...JSON.parse(localStorage.getItem(PAGE_KEY)) }; }
        catch { return { ...defaults }; }
    }

    function save(s) { localStorage.setItem(PAGE_KEY, JSON.stringify(s)); }

    function getState() {
        return {
            paper: el('psPaper').value,
            scale: parseInt(el('psScale').value),
            margin: el('psMarginGroup').querySelector('button.active')?.dataset.val || 'normal',
            width: el('psWidthGroup').querySelector('button.active')?.dataset.val || 'full',
            fontSize: parseFloat(el('psFontSize').value),
            showHeader: el('psShowHeader').checked,
            showFooter: el('psShowFooter').checked,
        };
    }

    function el(id) { return document.getElementById(id); }

    // تحديث واجهة اللوحة من الحالة
    function restoreUI(s) {
        el('psPaper').value = s.paper;
        el('psScale').value = s.scale;
        el('psScaleVal').textContent = s.scale + '%';
        el('psFontSize').value = s.fontSize;
        el('psFontVal').textContent = s.fontSize == 0 ? 'تلقائي' : s.fontSize + 'pt';
        el('psShowHeader').checked = s.showHeader;
        el('psShowFooter').checked = s.showFooter;

        // margin buttons
        el('psMarginGroup').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.dataset.val === s.margin);
        });
        // width buttons
        el('psWidthGroup').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.dataset.val === s.width);
        });
    }

    // بناء الأنماط الديناميكية
    window.applyPrintSettings = function() {
        const s = getState();
        save(s);

        el('psScaleVal').textContent = s.scale + '%';
        el('psFontVal').textContent = s.fontSize == 0 ? 'تلقائي' : s.fontSize + 'pt';

        const paper = PAPER_SIZES[s.paper] || PAPER_SIZES['A4-portrait'];
        const marginVal = MARGINS[s.margin] || MARGINS.normal;
        const scaleT = s.scale / 100;

        let widthCSS = '';
        if (s.width === 'full') widthCSS = 'max-width: 100% !important; width: 100% !important;';
        else if (s.width === 'auto') widthCSS = 'max-width: none !important; width: auto !important;';
        else widthCSS = `max-width: ${s.width}mm !important; width: ${s.width}mm !important; margin: 0 auto !important;`;

        let fontCSS = '';
        if (s.fontSize > 0) {
            fontCSS = `
                .content * { font-size: ${s.fontSize}pt !important; }
                .content table th { font-size: ${Math.max(s.fontSize - 1, 5)}pt !important; }
                .content table td { font-size: ${s.fontSize}pt !important; }
                .content small { font-size: ${Math.max(s.fontSize - 2, 4)}pt !important; }
            `;
        }

        const headerCSS = s.showHeader ? '' : `
            .print-report-header, .emp-print-header, .prh-inner, .prh-meta { display: none !important; }
        `;
        const footerCSS = s.showFooter ? '' : `
            .print-report-footer, .emp-print-footer, .prf-content { display: none !important; }
        `;

        el('psDynamicStyle').textContent = `
            @page { size: ${paper.size} !important; margin: ${marginVal} !important; }
            @media print {
                html, body { width: ${paper.w} !important; }
                .content { ${widthCSS} padding: 0 !important; }
                .main-content { width: 100% !important; max-width: 100% !important; }
                ${s.scale !== 100 ? `body { transform: scale(${scaleT}); transform-origin: top center; }` : ''}
                ${fontCSS}
                ${headerCSS}
                ${footerCSS}
            }
        `;

        // معلومات المعاينة
        el('psPreviewInfo').textContent =
            `${paper.size} • هوامش: ${s.margin} • تكبير: ${s.scale}% • عرض: ${s.width}`;
    };

    window.setMargin = function(val) {
        el('psMarginGroup').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.dataset.val === val);
        });
        applyPrintSettings();
    };

    window.setWidth = function(val) {
        el('psWidthGroup').querySelectorAll('button').forEach(b => {
            b.classList.toggle('active', b.dataset.val === val);
        });
        applyPrintSettings();
    };

    window.togglePrintSettings = function() {
        el('psPrintPanel').classList.toggle('show');
    };

    window.doPrint = function() {
        el('psPrintPanel').classList.remove('show');
        setTimeout(() => window.print(), 200);
    };

    window.resetPrintSettings = function() {
        localStorage.removeItem(PAGE_KEY);
        restoreUI(defaults);
        applyPrintSettings();
    };

    // التهيئة
    const saved = load();
    restoreUI(saved);
    applyPrintSettings();
})();
</script>
