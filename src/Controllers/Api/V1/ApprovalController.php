<?php
// =============================================================
// src/Controllers/Api/V1/ApprovalController.php - Approval Workflow API
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\ApprovalService;
use App\Services\RbacService;

class ApprovalController extends Controller
{
    private ApprovalService $service;

    public function __construct()
    {
        $this->service = new ApprovalService();
    }

    /**
     * GET /api/v1/approvals/pending
     */
    public function pending(): void
    {
        $this->requireAdmin();

        $type = $this->input('type');
        $pending = $this->service->getPending($type);

        $this->json([
            'success' => true,
            'data' => $pending,
            'count' => count($pending),
        ]);
    }

    /**
     * GET /api/v1/approvals/count
     */
    public function count(): void
    {
        $this->requireAdmin();

        $this->json([
            'success' => true,
            'data' => [
                'total' => $this->service->countPending(),
                'overtime' => $this->service->countPending('overtime'),
                'leave' => $this->service->countPending('leave'),
                'transfer' => $this->service->countPending('transfer'),
            ],
        ]);
    }

    /**
     * POST /api/v1/approvals
     */
    public function create(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('type', 'نوع الطلب')
            ->in('type', ['overtime', 'leave', 'transfer', 'attendance_correction', 'document'])
            ->required('employee_id', 'الموظف')
            ->integer('employee_id', 1)
            ->required('title', 'العنوان')
            ->string('title', 3, 255);

        if ($v->fails()) {
            $this->validationError($v);
        }

        $body['requested_by'] = $_SESSION['admin_id'];
        $result = $this->service->createRequest($body);

        $this->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * POST /api/v1/approvals/{id}/approve
     */
    public function approve(int $id): void
    {
        $this->requireAdmin();
        RbacService::requirePermission('manage_overtime');

        $body = $this->jsonBody();
        $result = $this->service->approve($id, $_SESSION['admin_id'], $body['notes'] ?? null);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/approvals/{id}/reject
     */
    public function reject(int $id): void
    {
        $this->requireAdmin();
        RbacService::requirePermission('manage_overtime');

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('notes', 'سبب الرفض')
            ->string('notes', 3, 1000);

        if ($v->fails()) {
            $this->validationError($v);
        }

        $result = $this->service->reject($id, $_SESSION['admin_id'], $body['notes']);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/approvals/{id}/cancel
     */
    public function cancel(int $id): void
    {
        $this->requireAdmin();

        $result = $this->service->cancel($id, $_SESSION['admin_id']);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * التأكد من أن المستخدم مسجل دخوله كمدير
     */
    private function requireAdmin(): void
    {
        if (empty($_SESSION['admin_id'])) {
            $this->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }
    }
}
