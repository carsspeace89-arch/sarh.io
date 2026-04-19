<?php
// =============================================================
// includes/mandatory_interrogation.php
// =============================================================
// Mandatory interrogation/report workflow helpers.
// =============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function mi_ensure_tables(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    db()->exec("CREATE TABLE IF NOT EXISTS mandatory_interrogation_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        questions_json LONGTEXT NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        is_default TINYINT(1) NOT NULL DEFAULT 0,
        created_by INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_default (is_default)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    db()->exec("CREATE TABLE IF NOT EXISTS mandatory_interrogation_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_id INT NOT NULL,
        employee_id INT NOT NULL,
        status ENUM('pending','submitted','approved','rejected') NOT NULL DEFAULT 'pending',
        answers_json LONGTEXT NULL,
        final_report TEXT NULL,
        admin_notes TEXT NULL,
        submitted_at DATETIME NULL,
        reviewed_at DATETIME NULL,
        reviewed_by INT NULL,
        parent_assignment_id INT NULL,
        created_by INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_employee_created (employee_id, created_at),
        INDEX idx_employee_status (employee_id, status),
        INDEX idx_status_created (status, created_at),
        INDEX idx_template (template_id),
        INDEX idx_parent (parent_assignment_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    mi_seed_default_template();
    $ready = true;
}

function mi_seed_default_template(): void
{
    $stmt = db()->query("SELECT id FROM mandatory_interrogation_templates WHERE is_default = 1 LIMIT 1");
    if ($stmt->fetch()) {
        return;
    }

    $defaultQuestions = [
        'من هو آخر موظف استلمت منه المركبة أو سلمته المركبة؟',
        'اذكر الحالة الفنية عند الاستلام (حرارة، صوت، سحب، عدادات إنذار، زيوت).',
        'ما أول علامة خلل ظهرت معك؟ ومتى ظهرت بالتوقيت التقريبي؟',
        'هل أبلغت الإدارة أو المشرف؟ اذكر طريقة البلاغ ووقته إن وجد.',
        'اكتب تسلسل ما حدث حتى لحظة التوقف النهائي للمركبة.',
        'من هم الموظفون الذين استخدموا المركبة خلال آخر فترة حسب علمك؟',
        'أي تفاصيل إضافية قد تساعد في تحديد السبب والمسؤولية؟'
    ];

    $sql = "INSERT INTO mandatory_interrogation_templates (title, description, questions_json, is_active, is_default, created_by)
            VALUES (?, ?, ?, 1, 1, NULL)";
    db()->prepare($sql)->execute([
        'نموذج استجواب المركبة (الحصر التراجعي)',
        'نموذج إلزامي لتوثيق الوقائع قبل السماح بتسجيل الحضور.',
        json_encode($defaultQuestions, JSON_UNESCAPED_UNICODE),
    ]);
}

function mi_get_templates(bool $onlyActive = true): array
{
    mi_ensure_tables();
    $sql = 'SELECT * FROM mandatory_interrogation_templates';
    if ($onlyActive) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY is_default DESC, id DESC';
    return db()->query($sql)->fetchAll();
}

function mi_create_template(string $title, array $questions, ?string $description, int $adminId): int
{
    mi_ensure_tables();

    $title = trim($title);
    if ($title === '') {
        throw new RuntimeException('عنوان النموذج مطلوب');
    }

    $clean = [];
    foreach ($questions as $q) {
        $q = trim((string)$q);
        if ($q !== '') {
            $clean[] = $q;
        }
    }

    if (count($clean) === 0) {
        throw new RuntimeException('أضف سؤالا واحدا على الأقل');
    }

    $stmt = db()->prepare("INSERT INTO mandatory_interrogation_templates (title, description, questions_json, is_active, is_default, created_by)
                           VALUES (?, ?, ?, 1, 0, ?)");
    $stmt->execute([$title, trim((string)$description) ?: null, json_encode($clean, JSON_UNESCAPED_UNICODE), $adminId]);
    return (int)db()->lastInsertId();
}

function mi_get_latest_assignment(int $employeeId): ?array
{
    mi_ensure_tables();
    $sql = "SELECT a.*, t.title AS template_title, t.questions_json
            FROM mandatory_interrogation_assignments a
            JOIN mandatory_interrogation_templates t ON t.id = a.template_id
            WHERE a.employee_id = ?
            ORDER BY a.id DESC
            LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$employeeId]);
    return $stmt->fetch() ?: null;
}

function mi_get_blocking_assignment(int $employeeId): ?array
{
    $latest = mi_get_latest_assignment($employeeId);
    if (!$latest) {
        return null;
    }

    if (in_array($latest['status'], ['pending', 'submitted', 'rejected'], true)) {
        return $latest;
    }

    return null;
}

function mi_assign_template_to_employees(int $templateId, array $employeeIds, int $adminId, ?int $parentId = null, ?string $adminNotes = null): array
{
    mi_ensure_tables();

    $employeeIds = array_values(array_unique(array_map('intval', $employeeIds)));
    $employeeIds = array_filter($employeeIds, static fn(int $id) => $id > 0);

    $inserted = 0;
    $skipped = 0;

    $stmt = db()->prepare("INSERT INTO mandatory_interrogation_assignments
        (template_id, employee_id, status, parent_assignment_id, created_by, admin_notes)
        VALUES (?, ?, 'pending', ?, ?, ?)");

    foreach ($employeeIds as $employeeId) {
        $latest = mi_get_latest_assignment($employeeId);
        if ($latest && in_array($latest['status'], ['pending', 'submitted', 'rejected'], true)) {
            $skipped++;
            continue;
        }

        $stmt->execute([
            $templateId,
            $employeeId,
            $parentId,
            $adminId,
            $adminNotes,
        ]);
        $inserted++;
    }

    return ['inserted' => $inserted, 'skipped' => $skipped];
}

function mi_submit_assignment(int $assignmentId, int $employeeId, array $answers, string $finalReport): bool
{
    mi_ensure_tables();

    $stmt = db()->prepare("SELECT id, status FROM mandatory_interrogation_assignments WHERE id = ? AND employee_id = ? LIMIT 1");
    $stmt->execute([$assignmentId, $employeeId]);
    $row = $stmt->fetch();

    if (!$row) {
        return false;
    }

    if (!in_array($row['status'], ['pending', 'rejected'], true)) {
        return false;
    }

    $cleanAnswers = [];
    foreach ($answers as $idx => $val) {
        $txt = trim((string)$val);
        if ($txt !== '') {
            $cleanAnswers[(string)$idx] = $txt;
        }
    }

    $finalReport = trim($finalReport);
    if ($finalReport === '') {
        return false;
    }

    $up = db()->prepare("UPDATE mandatory_interrogation_assignments
                         SET status = 'submitted', answers_json = ?, final_report = ?, submitted_at = NOW(), updated_at = NOW()
                         WHERE id = ?");
    return $up->execute([
        json_encode($cleanAnswers, JSON_UNESCAPED_UNICODE),
        $finalReport,
        $assignmentId,
    ]);
}

function mi_review_assignment(int $assignmentId, string $status, int $adminId, ?string $notes): bool
{
    mi_ensure_tables();
    if (!in_array($status, ['approved', 'rejected'], true)) {
        return false;
    }

    $stmt = db()->prepare("UPDATE mandatory_interrogation_assignments
                           SET status = ?, reviewed_by = ?, reviewed_at = NOW(), admin_notes = ?, updated_at = NOW()
                           WHERE id = ?");
    return $stmt->execute([$status, $adminId, trim((string)$notes) ?: null, $assignmentId]);
}

function mi_get_questions(array $assignmentRow): array
{
    $questions = [];
    if (!empty($assignmentRow['questions_json'])) {
        $decoded = json_decode((string)$assignmentRow['questions_json'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $q) {
                $q = trim((string)$q);
                if ($q !== '') {
                    $questions[] = $q;
                }
            }
        }
    }
    return $questions;
}
