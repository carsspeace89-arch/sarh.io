<?php
// =============================================================
// src/Services/AttendanceService.php - Attendance Service (Refactored)
// =============================================================
// Centralized attendance business logic. Uses ShiftService for
// shift detection and GeofenceService for location validation.
// Controllers MUST call this service; no direct DB access.
// =============================================================

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Branch;
use App\Core\Database;
use App\Core\Logger;

class AttendanceService
{
    private Attendance $attendance;
    private Employee $employee;
    private Branch $branch;
    private ShiftService $shiftService;
    private GeofenceService $geofenceService;

    public function __construct(
        ?ShiftService $shiftService = null,
        ?GeofenceService $geofenceService = null
    ) {
        $this->attendance = new Attendance();
        $this->employee = new Employee();
        $this->branch = new Branch();
        $this->shiftService = $shiftService ?? new ShiftService();
        $this->geofenceService = $geofenceService ?? new GeofenceService();
    }

    /**
     * Record check-in or check-out with full validation
     */
    public function record(int $employeeId, string $type, float $lat, float $lon, float $accuracy = 0): array
    {
        $validTypes = ['in', 'out'];
        if (!in_array($type, $validTypes, true)) {
            return ['success' => false, 'message' => 'نوع تسجيل غير صالح'];
        }

        // Duplicate prevention
        if ($this->attendance->hasRecentRecord($employeeId, $type, 5)) {
            return ['success' => false, 'message' => 'تم التسجيل مسبقاً خلال آخر 5 دقائق'];
        }

        $emp = $this->employee->find($employeeId);
        if (!$emp) {
            return ['success' => false, 'message' => 'الموظف غير موجود'];
        }

        $branchId = $emp['branch_id'] ?? null;
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Calculate late/early minutes via ShiftService
        $lateMinutes = 0;
        $earlyMinutes = 0;
        if ($type === 'in') {
            $lateMinutes = $this->shiftService->calculateLateMinutes($employeeId, $branchId);
            $earlyMinutes = $this->shiftService->calculateEarlyMinutes($branchId);
        }

        // Determine shift
        $schedule = $this->shiftService->getBranchSchedule($branchId);
        $shifts = $schedule['shifts'] ?? [];
        $shiftId = null;
        if (!empty($shifts)) {
            $nowTime = date('H:i');
            $shiftNum = $this->shiftService->assignTimeToShift($nowTime, $shifts);
            foreach ($shifts as $s) {
                if ((int)$s['shift_number'] === $shiftNum) {
                    if (isset($s['id'])) {
                        $shiftId = (int)$s['id'];
                    } elseif ($branchId) {
                        $db = Database::getInstance();
                        $stmt = $db->prepare("SELECT id FROM branch_shifts WHERE branch_id = ? AND shift_number = ? AND is_active = 1 LIMIT 1");
                        $stmt->execute([$branchId, $shiftNum]);
                        $row = $stmt->fetch();
                        if ($row) $shiftId = (int)$row['id'];
                    }
                    break;
                }
            }
        }

        // Link checkout to last checkin
        $closedByCheckinId = null;
        if ($type === 'out') {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT id FROM attendances
                WHERE employee_id = ? AND type = 'in' AND attendance_date = CURDATE()
                ORDER BY timestamp DESC LIMIT 1
            ");
            $stmt->execute([$employeeId]);
            $ciRow = $stmt->fetch();
            if ($ciRow) $closedByCheckinId = (int)$ciRow['id'];
        }

        // Perform risk-scored geofence validation
        $riskResult = $this->geofenceService->validateWithRiskScore(
            $employeeId, $lat, $lon, $branchId, $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? null, $accuracy
        );

        $id = $this->attendance->create([
            'employee_id' => $employeeId,
            'type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'attendance_date' => date('Y-m-d'),
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'latitude' => $lat,
            'longitude' => $lon,
            'location_accuracy' => $accuracy,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => 'manual',
            'shift_id' => $shiftId,
            'closed_by_checkin_id' => $closedByCheckinId,
        ]);

        $messages = [
            'in' => 'تم تسجيل الدخول بنجاح',
            'out' => 'تم تسجيل الانصراف بنجاح',
        ];

        Logger::info('Attendance recorded', [
            'employee_id' => $employeeId,
            'type' => $type,
            'late_minutes' => $lateMinutes,
            'risk_score' => $riskResult['risk_score'] ?? 0,
            'shift_id' => $shiftId,
        ]);

        return [
            'success' => true,
            'message' => $messages[$type] ?? 'تم التسجيل',
            'late_minutes' => $lateMinutes,
            'early_minutes' => $earlyMinutes,
            'record_id' => $id,
            'risk_score' => $riskResult['risk_score'] ?? 0,
            'risk_level' => $riskResult['risk_level'] ?? 'low',
        ];
    }

    /**
     * Validate geofence (delegates to GeofenceService)
     */
    public function validateGeofence(float $empLat, float $empLon, ?int $branchId = null): array
    {
        return $this->geofenceService->isWithinGeofence($empLat, $empLon, $branchId);
    }

    /**
     * Full risk-scored geofence validation
     */
    public function validateGeofenceWithRisk(
        int $employeeId,
        float $lat,
        float $lon,
        ?int $branchId,
        string $ip,
        ?string $deviceFingerprint = null,
        float $accuracy = 0
    ): array {
        return $this->geofenceService->validateWithRiskScore(
            $employeeId, $lat, $lon, $branchId, $ip, $deviceFingerprint, $accuracy
        );
    }

    /**
     * Check time window via ShiftService
     */
    public function isWithinTimeWindow(string $type, ?int $branchId = null): array
    {
        $schedule = $this->shiftService->getBranchSchedule($branchId);
        $nowMin = (int)date('H') * 60 + (int)date('i');

        if ($type === 'in') {
            $startStr = $schedule['work_start_time'];
            $startMin = $this->shiftService->timeToMinutes($startStr);
            // Allow 90 min early
            $windowStart = ($startMin - 90 + 1440) % 1440;
            $endStr = $schedule['work_end_time'];
            $windowEnd = $this->shiftService->timeToMinutes($endStr);
        } elseif ($type === 'out') {
            $startStr = $schedule['work_start_time'];
            $windowStart = $this->shiftService->timeToMinutes($startStr);
            $endStr = $schedule['work_end_time'];
            $endMin = $this->shiftService->timeToMinutes($endStr);
            // Allow 90 min after end
            $windowEnd = ($endMin + 90) % 1440;
        } else {
            return ['allowed' => true, 'message' => 'مسموح'];
        }

        if ($windowEnd < $windowStart) {
            $allowed = ($nowMin >= $windowStart || $nowMin <= $windowEnd);
        } else {
            $allowed = ($nowMin >= $windowStart && $nowMin <= $windowEnd);
        }

        if ($allowed) {
            return ['allowed' => true, 'message' => 'ضمن الوقت المسموح'];
        }

        return [
            'allowed' => false,
            'message' => "خارج وقت التسجيل",
        ];
    }

    /**
     * Dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $stats = $this->attendance->getTodayStats();
        $recent = $this->attendance->getRecent(15);
        $absent = $this->employee->getAbsentToday(10);
        $branchCount = $this->branch->count(['is_active' => 1]);

        return [
            'stats' => $stats,
            'recent' => $recent,
            'absent' => $absent,
            'branch_count' => $branchCount,
        ];
    }

    private function getClientIP(): string
    {
        if (function_exists('getClientIP')) {
            return getClientIP();
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
