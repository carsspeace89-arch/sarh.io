<?php
// =============================================================
// src/Services/ApprovalService.php - خدمة سير عمل الموافقات
// =============================================================

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Core\Database;
use App\Core\Logger;

class ApprovalService
{
    private ApprovalRequest $model;

    public function __construct()
    {
        $this->model = new ApprovalRequest();
    }

    /**
     * إنشاء طلب موافقة جديد
     */
    public function createRequest(array $data): array
    {
        $required = ['type', 'employee_id', 'requested_by', 'title'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "الحقل {$field} مطلوب"];
            }
        }

        $validTypes = ['overtime', 'leave', 'transfer', 'attendance_correction', 'document'];
        if (!in_array($data['type'], $validTypes, true)) {
            return ['success' => false, 'message' => 'نوع الطلب غير صالح'];
        }

        $id = $this->model->create([
            'type' => $data['type'],
            'reference_id' => $data['reference_id'] ?? null,
            'employee_id' => $data['employee_id'],
            'requested_by' => $data['requested_by'],
            'priority' => $data['priority'] ?? 'normal',
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        // Record history
        $this->addHistory($id, 'created', $data['requested_by'], 'تم إنشاء الطلب');

        Logger::info('Approval request created', ['id' => $id, 'type' => $data['type']]);

        return ['success' => true, 'message' => 'تم إنشاء الطلب بنجاح', 'id' => $id];
    }

    /**
     * الموافقة على طلب
     */
    public function approve(int $id, int $adminId, ?string $notes = null): array
    {
        $request = $this->model->find($id);
        if (!$request) {
            return ['success' => false, 'message' => 'الطلب غير موجود'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'لا يمكن الموافقة على هذا الطلب — الحالة الحالية: ' . $request['status']];
        }

        $this->model->update($id, [
            'status' => 'approved',
            'approved_by' => $adminId,
            'notes' => $notes,
            'decided_at' => date('Y-m-d H:i:s'),
        ]);

        $this->addHistory($id, 'approved', $adminId, $notes);

        Logger::info('Approval request approved', ['id' => $id, 'admin_id' => $adminId]);

        return ['success' => true, 'message' => 'تمت الموافقة على الطلب بنجاح'];
    }

    /**
     * رفض طلب
     */
    public function reject(int $id, int $adminId, ?string $notes = null): array
    {
        $request = $this->model->find($id);
        if (!$request) {
            return ['success' => false, 'message' => 'الطلب غير موجود'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'لا يمكن رفض هذا الطلب — الحالة الحالية: ' . $request['status']];
        }

        $this->model->update($id, [
            'status' => 'rejected',
            'approved_by' => $adminId,
            'notes' => $notes,
            'decided_at' => date('Y-m-d H:i:s'),
        ]);

        $this->addHistory($id, 'rejected', $adminId, $notes);

        Logger::info('Approval request rejected', ['id' => $id, 'admin_id' => $adminId]);

        return ['success' => true, 'message' => 'تم رفض الطلب'];
    }

    /**
     * إلغاء طلب (من الطالب)
     */
    public function cancel(int $id, int $adminId): array
    {
        $request = $this->model->find($id);
        if (!$request) {
            return ['success' => false, 'message' => 'الطلب غير موجود'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'لا يمكن إلغاء هذا الطلب'];
        }

        $this->model->update($id, [
            'status' => 'cancelled',
            'decided_at' => date('Y-m-d H:i:s'),
        ]);

        $this->addHistory($id, 'cancelled', $adminId, 'تم الإلغاء');

        return ['success' => true, 'message' => 'تم إلغاء الطلب'];
    }

    /**
     * رفض الطلبات المنتهية الصلاحية تلقائياً
     */
    public function autoRejectExpired(): int
    {
        $expired = $this->model->getExpired();
        $count = 0;

        foreach ($expired as $req) {
            $this->model->update($req['id'], [
                'status' => 'rejected',
                'notes' => 'تم الرفض تلقائياً بسبب انتهاء الصلاحية',
                'decided_at' => date('Y-m-d H:i:s'),
            ]);
            $this->addHistory($req['id'], 'rejected', 0, 'رفض تلقائي — انتهاء المهلة');
            $count++;
        }

        if ($count > 0) {
            Logger::info("Auto-rejected {$count} expired approval requests");
        }

        return $count;
    }

    /**
     * جلب الطلبات المعلقة
     */
    public function getPending(?string $type = null, int $limit = 50): array
    {
        return $this->model->getPending($type, $limit);
    }

    /**
     * عدد الطلبات المعلقة
     */
    public function countPending(?string $type = null): int
    {
        return $this->model->countPending($type);
    }

    /**
     * إضافة سجل في التاريخ
     */
    private function addHistory(int $approvalId, string $action, int $performedBy, ?string $notes = null): void
    {
        try {
            $db = Database::getInstance();
            $db->prepare(
                "INSERT INTO approval_history (approval_id, action, performed_by, notes) VALUES (?, ?, ?, ?)"
            )->execute([$approvalId, $action, $performedBy, $notes]);
        } catch (\Throwable $e) {
            Logger::warning('Failed to add approval history', ['error' => $e->getMessage()]);
        }
    }
}
