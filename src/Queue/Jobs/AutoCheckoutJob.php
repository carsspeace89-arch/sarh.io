<?php
// =============================================================
// src/Queue/Jobs/AutoCheckoutJob.php - Auto Checkout Background Job
// =============================================================

namespace App\Queue\Jobs;

use App\Queue\Job;
use App\Core\Database;
use App\Core\Logger;
use App\Models\Attendance;

class AutoCheckoutJob extends Job
{
    protected int $maxAttempts = 2;
    protected int $retryDelay = 30;

    private int $checkinId;
    private int $employeeId;
    private string $attendanceDate;
    private ?int $shiftId;
    private string $checkoutTimestamp;
    private float $latitude;
    private float $longitude;
    private int $shiftNum;
    private int $graceMinutes;

    public function __construct(
        int $checkinId,
        int $employeeId,
        string $attendanceDate,
        ?int $shiftId,
        string $checkoutTimestamp,
        float $latitude,
        float $longitude,
        int $shiftNum,
        int $graceMinutes
    ) {
        $this->checkinId = $checkinId;
        $this->employeeId = $employeeId;
        $this->attendanceDate = $attendanceDate;
        $this->shiftId = $shiftId;
        $this->checkoutTimestamp = $checkoutTimestamp;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->shiftNum = $shiftNum;
        $this->graceMinutes = $graceMinutes;
    }

    public function handle(): void
    {
        $db = Database::getInstance();
        $attendance = new Attendance();

        // Double-entry guard
        $guard = $db->prepare("
            SELECT id FROM attendances
            WHERE employee_id = ? AND type = 'out'
              AND attendance_date = ? AND timestamp > (SELECT timestamp FROM attendances WHERE id = ?)
            LIMIT 1
        ");
        $guard->execute([$this->employeeId, $this->attendanceDate, $this->checkinId]);
        if ($guard->fetch()) {
            Logger::queue('Auto-checkout skipped (manual checkout exists)', [
                'employee_id' => $this->employeeId,
                'checkin_id' => $this->checkinId,
            ]);
            return;
        }

        $attendance->create([
            'employee_id' => $this->employeeId,
            'type' => 'out',
            'timestamp' => $this->checkoutTimestamp,
            'attendance_date' => $this->attendanceDate,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_accuracy' => 0,
            'ip_address' => 'AUTO',
            'user_agent' => 'QUEUE-WORKER',
            'notes' => "انصراف تلقائي - وردية {$this->shiftNum} (بعد {$this->graceMinutes} دقيقة سماح)",
            'status' => 'auto_checkout',
            'shift_id' => $this->shiftId,
            'closed_by_checkin_id' => $this->checkinId,
        ]);

        Logger::queue('Auto-checkout completed', [
            'employee_id' => $this->employeeId,
            'checkin_id' => $this->checkinId,
            'shift' => $this->shiftNum,
        ]);
    }
}
