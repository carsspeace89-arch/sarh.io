"""
generate_docs.py — توليد / تحديث ملف "التوثيق الكامل.md"
يقرأ جميع ملفات التوثيق (.md, .txt) في المشروع ويجمعها في ملف واحد.

الاستخدام:
    python generate_docs.py
"""

import os
import datetime

# المجلد الحالي (جذر المشروع)
PROJECT_ROOT = os.path.dirname(os.path.abspath(__file__))

# الملف الناتج
OUTPUT_FILE = os.path.join(PROJECT_ROOT, "التوثيق الكامل.md")

# الامتدادات المطلوبة
EXTENSIONS = {'.md', '.txt'}

# ملفات/مجلدات يتم تجاهلها
IGNORE_FILES = {
    os.path.basename(OUTPUT_FILE),  # لا تُدرج الملف الناتج نفسه
    'config.php.local',
    '.htaccess.local',
}
IGNORE_DIRS = {'.git', 'node_modules', 'vendor', 'assets', '__pycache__'}


def collect_files(root: str) -> list[tuple[str, str]]:
    """اجمع كل الملفات النصية/التوثيقية مع مساراتها النسبية."""
    results = []
    for dirpath, dirnames, filenames in os.walk(root):
        # تجاهل المجلدات غير المطلوبة
        dirnames[:] = [d for d in dirnames if d not in IGNORE_DIRS]

        for fname in sorted(filenames):
            if fname in IGNORE_FILES:
                continue
            _, ext = os.path.splitext(fname)
            if ext.lower() in EXTENSIONS:
                full = os.path.join(dirpath, fname)
                rel = os.path.relpath(full, root).replace('\\', '/')
                results.append((rel, full))
    return results


def build_document(files: list[tuple[str, str]]) -> str:
    """أنشئ محتوى الملف النهائي."""
    now = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    lines = [
        f"# التوثيق الكامل — نظام الحضور والانصراف",
        f"",
        f"> تم التوليد تلقائياً بتاريخ: **{now}**  ",
        f"> عدد الملفات: **{len(files)}**",
        f"",
        f"---",
        f"",
        f"## فهرس الملفات",
        f"",
    ]

    # فهرس
    for i, (rel, _) in enumerate(files, 1):
        anchor = rel.replace('/', '-').replace('.', '-').replace(' ', '-')
        lines.append(f"{i}. [{rel}](#{anchor})")
    lines.append("")
    lines.append("---")
    lines.append("")

    # محتوى كل ملف
    for rel, full in files:
        anchor = rel.replace('/', '-').replace('.', '-').replace(' ', '-')
        lines.append(f"<a id=\"{anchor}\"></a>")
        lines.append(f"")
        lines.append(f"## 📄 {rel}")
        lines.append(f"")

        try:
            with open(full, 'r', encoding='utf-8') as f:
                content = f.read()
        except UnicodeDecodeError:
            try:
                with open(full, 'r', encoding='utf-8-sig') as f:
                    content = f.read()
            except Exception:
                content = "(تعذر قراءة الملف — ترميز غير مدعوم)"

        # تحديد لغة الكود حسب الامتداد
        ext = os.path.splitext(rel)[1].lower()
        lang = 'markdown' if ext == '.md' else 'text'

        lines.append(f"```{lang}")
        lines.append(content.rstrip())
        lines.append(f"```")
        lines.append(f"")
        lines.append(f"---")
        lines.append(f"")

    return '\n'.join(lines)


def main():
    files = collect_files(PROJECT_ROOT)

    if not files:
        print("لم يتم العثور على ملفات توثيقية (.md / .txt)")
        return

    content = build_document(files)

    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write(content)

    action = "تحديث" if os.path.exists(OUTPUT_FILE) else "إنشاء"
    print(f"✅ تم {action} الملف: التوثيق الكامل.md")
    print(f"   الملفات المضمّنة: {len(files)}")
    for rel, _ in files:
        print(f"   • {rel}")


if __name__ == '__main__':
    main()
