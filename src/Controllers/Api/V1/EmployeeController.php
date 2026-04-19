<?php
// =============================================================
// src/Controllers/Api/V1/EmployeeController.php - Employee API Controller
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\Employee;
use App\Middleware\RedisRateLimiter;

class EmployeeController extends Controller
{
    private Employee $employee;
    private RedisRateLimiter $rateLimiter;

    public function __construct()
    {
        $this->employee = new Employee();
        $this->rateLimiter = new RedisRateLimiter();
    }

    /**
     * GET /api/v1/employee/{token}
     */
    public function getByToken(string $token): void
    {
        $limit = $this->rateLimiter->checkByIP('employee_get', 60, 60);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        $token = htmlspecialchars(strip_tags(trim($token)), ENT_QUOTES, 'UTF-8');
        if (empty($token)) {
            $this->json(['success' => false, 'message' => 'الرمز المميز مطلوب'], 400);
        }

        $emp = $this->employee->findByToken($token);
        if (!$emp) {
            $this->json(['success' => false, 'message' => 'موظف غير موجود'], 404);
        }

        // Get branch info
        $branch = null;
        if ($emp['branch_id']) {
            $branchModel = new \App\Models\Branch();
            $branchData = $branchModel->find($emp['branch_id']);
            if ($branchData) {
                $branch = [
                    'id' => $branchData['id'],
                    'name' => $branchData['name'],
                    'latitude' => $branchData['latitude'],
                    'longitude' => $branchData['longitude'],
                    'geofence_radius' => $branchData['geofence_radius'],
                ];
            }
        }

        // Get today's attendance
        $attendanceModel = new \App\Models\Attendance();
        $todayRecords = $attendanceModel->getTodayRecords($emp['id']);

        $this->json([
            'success' => true,
            'employee' => [
                'id' => $emp['id'],
                'name' => $emp['name'],
                'token' => $emp['unique_token'],
                'branch_id' => $emp['branch_id'],
                'bypass_geofence' => (bool)($emp['bypass_geofence'] ?? false),
            ],
            'branch' => $branch,
            'today' => array_map(fn($r) => [
                'type' => $r['type'],
                'time' => date('H:i', strtotime($r['timestamp'])),
                'status' => $r['status'] ?? '',
            ], $todayRecords),
        ]);
    }

    /**
     * POST /api/v1/employee/register-device
     */
    public function registerDevice(): void
    {
        $limit = $this->rateLimiter->checkByIP('device_register', 10, 300);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $token = trim($body['token'] ?? '');
        $fingerprint = trim($body['fingerprint'] ?? '');

        if (empty($token) || empty($fingerprint) || strlen($fingerprint) < 32) {
            $this->json(['success' => false, 'message' => 'بيانات غير صالحة'], 400);
        }

        $emp = $this->employee->findByToken($token);
        if (!$emp) {
            $this->json(['success' => false, 'message' => 'موظف غير موجود'], 404);
        }

        $db = \App\Core\Database::getInstance();

        // Check if fingerprint is bound to another employee
        $bound = $this->employee->findBoundDevice($fingerprint, $emp['id']);

        if ($bound) {
            Logger::security('Device already bound to another employee', [
                'fingerprint' => substr($fingerprint, 0, 8) . '...',
                'requested_by' => $emp['id'],
                'bound_to' => $bound['id'],
            ]);
            $this->json(['success' => false, 'message' => 'هذا الجهاز مربوط بموظف آخر'], 409);
        }

        // Register device
        $this->employee->update($emp['id'], ['device_fingerprint' => $fingerprint]);

        Logger::info('Device registered', ['employee_id' => $emp['id']]);

        $this->json(['success' => true, 'message' => 'تم ربط الجهاز بنجاح']);
    }
}
