<?php
// =============================================================
// src/Controllers/Api/V1/AttendanceController.php - Thin API Controller
// =============================================================
// Controllers are thin: request → service → response
// All business logic lives in Services layer.
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Core\Logger;
use App\Services\AttendanceService;
use App\Services\ShiftService;
use App\Middleware\RedisRateLimiter;

class AttendanceController extends Controller
{
    private AttendanceService $service;
    private ShiftService $shiftService;
    private RedisRateLimiter $rateLimiter;

    public function __construct()
    {
        $this->service = new AttendanceService();
        $this->shiftService = new ShiftService();
        $this->rateLimiter = new RedisRateLimiter();
    }

    /**
     * POST /api/v1/check-in
     */
    public function checkIn(): void
    {
        $limit = $this->rateLimiter->checkByIP('checkin', 30, 60);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'طريقة طلب غير مسموحة'], 405);
        }

        $body = $this->jsonBody();
        if (empty($body)) {
            $this->json(['success' => false, 'message' => 'بيانات غير صالحة'], 400);
        }

        $v = $this->validate($body)
            ->required('token', 'الرمز المميز')
            ->string('token', 1, 255, 'الرمز المميز')
            ->required('latitude', 'خط العرض')
            ->latitude('latitude')
            ->required('longitude', 'خط الطول')
            ->longitude('longitude');

        if ($v->fails()) {
            $this->validationError($v);
        }

        $token = trim($body['token']);
        $lat = (float)$body['latitude'];
        $lon = (float)$body['longitude'];
        $accuracy = (float)($body['accuracy'] ?? 0);

        $employee = (new \App\Models\Employee())->findByToken($token);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'رمز غير صالح أو الموظف غير مفعّل'], 403);
        }

        // Time window check
        $timeCheck = $this->service->isWithinTimeWindow('in', $employee['branch_id'] ?? null);
        if (!$timeCheck['allowed']) {
            $this->json(['success' => false, 'message' => $timeCheck['message']]);
        }

        // Geofence check (with risk scoring)
        if (empty($employee['bypass_geofence'])) {
            $geoCheck = $this->service->validateGeofenceWithRisk(
                $employee['id'], $lat, $lon, $employee['branch_id'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $accuracy
            );

            if (!$geoCheck['allowed']) {
                $this->json([
                    'success' => false,
                    'message' => "⛔ لا يمكن تسجيل الحضور من خارج نطاق العمل.\n\n📍 المسافة الحالية: {$geoCheck['distance']} متر\n📏 الحد المسموح: {$geoCheck['radius']} متر",
                    'distance' => $geoCheck['distance'],
                    'radius' => $geoCheck['radius'],
                    'risk_score' => $geoCheck['risk_score'] ?? 0,
                ]);
            }
        }

        $result = $this->service->record($employee['id'], 'in', $lat, $lon, $accuracy);

        Logger::api('Check-in', [
            'employee_id' => $employee['id'],
            'success' => $result['success'],
        ]);

        $this->json(array_merge($result, [
            'employee_name' => $employee['name'],
            'timestamp' => date('Y-m-d H:i:s'),
            'distance' => $geoCheck['distance'] ?? 0,
        ]));
    }

    /**
     * POST /api/v1/check-out
     */
    public function checkOut(): void
    {
        $limit = $this->rateLimiter->checkByIP('checkout', 30, 60);
        if (!$limit['allowed']) {
            $this->rateLimiter->denyResponse($limit['retry_after']);
        }

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'طريقة طلب غير مسموحة'], 405);
        }

        $body = $this->jsonBody();
        if (empty($body)) {
            $this->json(['success' => false, 'message' => 'بيانات غير صالحة'], 400);
        }

        $v = $this->validate($body)
            ->required('token', 'الرمز المميز')
            ->string('token', 1, 255, 'الرمز المميز');

        if ($v->fails()) {
            $this->validationError($v);
        }

        $token = trim($body['token']);
        $lat = (float)($body['latitude'] ?? 0);
        $lon = (float)($body['longitude'] ?? 0);
        $accuracy = (float)($body['accuracy'] ?? 0);

        $employee = (new \App\Models\Employee())->findByToken($token);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'رمز غير صالح أو الموظف غير مفعّل'], 403);
        }

        $result = $this->service->record($employee['id'], 'out', $lat, $lon, $accuracy);

        Logger::api('Check-out', [
            'employee_id' => $employee['id'],
            'success' => $result['success'],
        ]);

        $this->json(array_merge($result, [
            'employee_name' => $employee['name'],
            'timestamp' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * GET /api/v1/attendance/today
     */
    public function todayStats(): void
    {
        $stats = $this->service->getDashboardStats();
        $this->json(['success' => true, 'data' => $stats]);
    }
}
