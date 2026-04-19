<?php
// =============================================================
// src/Models/Notification.php - نموذج الإشعارات
// =============================================================

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    /**
     * جلب غير المقروءة
     */
    public function getUnread(int $limit = 20, ?int $adminId = null): array
    {
        $sql = "SELECT n.*, e.name AS employee_name
                FROM notifications n
                LEFT JOIN employees e ON n.employee_id = e.id
                WHERE n.is_read = 0";
        $params = [];

        if ($adminId !== null) {
            $sql .= " AND (n.admin_id = ? OR n.admin_id IS NULL)";
            $params[] = $adminId;
        }

        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        $params[] = $limit;

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * عدد غير المقروءة
     */
    public function countUnread(?int $adminId = null): int
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE is_read = 0";
        $params = [];

        if ($adminId !== null) {
            $sql .= " AND (admin_id = ? OR admin_id IS NULL)";
            $params[] = $adminId;
        }

        return (int)$this->query($sql, $params)->fetchColumn();
    }

    /**
     * تعليم كمقروء
     */
    public function markRead(int $id): void
    {
        $this->update($id, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * تعليم الكل كمقروء
     */
    public function markAllRead(?int $adminId = null): void
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0";
        $params = [];

        if ($adminId !== null) {
            $sql .= " AND (admin_id = ? OR admin_id IS NULL)";
            $params[] = $adminId;
        }

        $this->query($sql, $params);
    }

    /**
     * جلب الإشعارات مع تصفية وصفحات
     */
    public function getFiltered(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = 'n.type = ?';
            $params[] = $filters['type'];
        }
        if (isset($filters['is_read'])) {
            $where[] = 'n.is_read = ?';
            $params[] = (int)$filters['is_read'];
        }
        if (!empty($filters['employee_id'])) {
            $where[] = 'n.employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'n.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'n.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->query(
            "SELECT COUNT(*) FROM notifications n WHERE {$whereStr}",
            $params
        )->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $records = $this->query(
            "SELECT n.*, e.name AS employee_name
             FROM notifications n
             LEFT JOIN employees e ON n.employee_id = e.id
             WHERE {$whereStr}
             ORDER BY n.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        )->fetchAll();

        return [
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * حذف الإشعارات القديمة
     */
    public function deleteOlderThan(int $days): int
    {
        $stmt = $this->query(
            "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        return $stmt->rowCount();
    }
}
