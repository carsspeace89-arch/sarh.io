<?php
// =============================================================
// src/Controllers/Api/V1/NotificationController.php - Notification Center API
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    private Notification $model;
    private NotificationService $service;

    public function __construct()
    {
        $this->model = new Notification();
        $this->service = new NotificationService();
    }

    /**
     * GET /api/v1/notifications
     */
    public function index(): void
    {
        $this->requireAdmin();

        $page = max(1, (int)($this->input('page') ?? 1));
        $perPage = min(100, max(1, (int)($this->input('per_page') ?? 25)));
        $type = $this->input('type');
        $isRead = $this->input('is_read');

        $filters = [];
        if ($type) $filters['type'] = $type;
        if ($isRead !== null) $filters['is_read'] = $isRead;

        $result = $this->model->getFiltered($filters, $page, $perPage);

        $this->json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
                'total_pages' => $result['total_pages'],
            ],
        ]);
    }

    /**
     * GET /api/v1/notifications/unread
     */
    public function unread(): void
    {
        $this->requireAdmin();

        $limit = min(50, max(1, (int)($this->input('limit') ?? 20)));
        $adminId = $_SESSION['admin_id'];

        $notifications = $this->model->getUnread($limit, $adminId);
        $count = $this->model->countUnread($adminId);

        $this->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $count,
        ]);
    }

    /**
     * GET /api/v1/notifications/count
     */
    public function count(): void
    {
        $this->requireAdmin();

        $this->json([
            'success' => true,
            'unread_count' => $this->model->countUnread($_SESSION['admin_id']),
        ]);
    }

    /**
     * POST /api/v1/notifications/{id}/read
     */
    public function markRead(int $id): void
    {
        $this->requireAdmin();

        $this->model->markRead($id);

        $this->json(['success' => true, 'message' => 'تم تعليم الإشعار كمقروء']);
    }

    /**
     * POST /api/v1/notifications/read-all
     */
    public function markAllRead(): void
    {
        $this->requireAdmin();

        $this->model->markAllRead($_SESSION['admin_id']);

        $this->json(['success' => true, 'message' => 'تم تعليم جميع الإشعارات كمقروءة']);
    }

    /**
     * POST /api/v1/notifications/send
     */
    public function send(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = $this->jsonBody();
        $v = $this->validate($body)
            ->required('type', 'النوع')
            ->required('title', 'العنوان')
            ->string('title', 3, 255);

        if ($v->fails()) {
            $this->validationError($v);
        }

        $id = $this->service->create([
            'employee_id' => $body['employee_id'] ?? null,
            'admin_id' => $body['admin_id'] ?? null,
            'type' => $body['type'],
            'title' => $body['title'],
            'message' => $body['message'] ?? null,
            'data' => $body['data'] ?? null,
        ]);

        $this->json(['success' => true, 'id' => $id, 'message' => 'تم إرسال الإشعار'], 201);
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['admin_id'])) {
            $this->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }
    }
}
