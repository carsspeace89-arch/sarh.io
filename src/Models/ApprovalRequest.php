<?php
// =============================================================
// src/Models/ApprovalRequest.php - نموذج طلبات الموافقة
// =============================================================

namespace App\Models;

use App\Core\Model;

class ApprovalRequest extends Model
{
    protected string $table = 'approval_requests';

    /**
     * الطلبات المعلقة
     */
    public function getPending(?string $type = null, ?int $limit = null): array
    {
        $sql = "SELECT ar.*, e.name AS employee_name, e.job_title,
                       b.name AS branch_name, a.username AS requested_by_name
                FROM approval_requests ar
                JOIN employees e ON ar.employee_id = e.id
                LEFT JOIN branches b ON e.branch_id = b.id
                LEFT JOIN admins a ON ar.requested_by = a.id
                WHERE ar.status = 'pending'";
        $params = [];

        if ($type) {
            $sql .= " AND ar.type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY FIELD(ar.priority, 'urgent', 'high', 'normal', 'low'), ar.requested_at ASC";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * طلبات موظف معين
     */
    public function getByEmployee(int $employeeId, ?string $status = null): array
    {
        $sql = "SELECT * FROM approval_requests WHERE employee_id = ?";
        $params = [$employeeId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY requested_at DESC";
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * عدد الطلبات المعلقة
     */
    public function countPending(?string $type = null): int
    {
        $sql = "SELECT COUNT(*) FROM approval_requests WHERE status = 'pending'";
        $params = [];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        return (int)$this->query($sql, $params)->fetchColumn();
    }

    /**
     * الطلبات المنتهية الصلاحية
     */
    public function getExpired(): array
    {
        return $this->query(
            "SELECT * FROM approval_requests
             WHERE status = 'pending' AND expires_at IS NOT NULL AND expires_at < NOW()"
        )->fetchAll();
    }
}
