<?php
// =============================================================
// src/Services/ShiftService.php - Shift Management Service
// =============================================================
// Centralized shift logic: detection, assignment, scheduling.
// Extracted from functions.php to eliminate scattered business logic.
// =============================================================

namespace App\Services;

use App\Core\Database;

class ShiftService
{
    private RedisCacheService $cache;

    public function __construct(?RedisCacheService $cache = null)
    {
        $this->cache = $cache ?? RedisCacheService::getInstance();
    }

    /**
     * Get branch schedule with active shift detection
     */
    public function getBranchSchedule(?int $branchId = null): array
    {
        if ($branchId) {
            $shifts = $this->cache->getBranchShifts($branchId);

            if (!empty($shifts)) {
                $active = $this->detectActiveShift($shifts);
                return [
                    'work_start_time' => $active['shift_start'],
                    'work_end_time' => $active['shift_end'],
                    'current_shift' => (int)$active['shift_number'],
                    'shifts' => $shifts,
                ];
            }
        }

        // Fallback to system defaults
        $settings = $this->cache->getSettings();
        $fallbackStart = $settings['work_start_time'] ?? '08:00';
        $fallbackEnd = $settings['work_end_time'] ?? '16:00';

        return [
            'work_start_time' => $fallbackStart,
            'work_end_time' => $fallbackEnd,
            'current_shift' => 1,
            'shifts' => [[
                'shift_number' => 1,
                'shift_start' => $fallbackStart,
                'shift_end' => $fallbackEnd,
            ]],
        ];
    }

    /**
     * Detect the currently active shift based on current time
     * Window: shift_start - 90 minutes to shift_end
     */
    public function detectActiveShift(array $shifts): array
    {
        $nowMin = (int)date('H') * 60 + (int)date('i');

        foreach ($shifts as $s) {
            $start = $this->timeToMinutes($s['shift_start']);
            $end = $this->timeToMinutes($s['shift_end']);
            $early = ($start - 90 + 1440) % 1440;

            if ($end < $early) {
                if ($nowMin >= $early || $nowMin <= $end) return $s;
            } else {
                if ($nowMin >= $early && $nowMin <= $end) return $s;
            }
        }

        // No active shift — return closest upcoming
        $best = null;
        $bestDist = 9999;
        foreach ($shifts as $s) {
            $start = $this->timeToMinutes($s['shift_start']);
            $dist = ($start - $nowMin + 1440) % 1440;
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $s;
            }
        }
        return $best ?: $shifts[0];
    }

    /**
     * Assign a time to its matching shift
     * Window: shift_start - 120m to shift_end + 60m
     */
    public function assignTimeToShift(string $time, array $shifts): int
    {
        $timeMin = $this->timeToMinutes($time);

        foreach ($shifts as $s) {
            $start = $this->timeToMinutes($s['shift_start']);
            $end = $this->timeToMinutes($s['shift_end']);
            $windowStart = ($start - 120 + 1440) % 1440;
            $windowEnd = ($end + 60) % 1440;

            if ($windowStart < $windowEnd) {
                if ($timeMin >= $windowStart && $timeMin <= $windowEnd) {
                    return (int)$s['shift_number'];
                }
            } else {
                if ($timeMin >= $windowStart || $timeMin <= $windowEnd) {
                    return (int)$s['shift_number'];
                }
            }
        }
        return 1;
    }

    /**
     * Get all branch shifts grouped by branch
     */
    public function getAllBranchShifts(): array
    {
        return $this->cache->remember('all_branch_shifts', 300, function () {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT id, branch_id, shift_number, shift_start, shift_end
                FROM branch_shifts WHERE is_active = 1
                ORDER BY branch_id, shift_number
            ");
            $result = [];
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['branch_id']][] = [
                    'id' => (int)$row['id'],
                    'num' => (int)$row['shift_number'],
                    'start' => substr($row['shift_start'], 0, 5),
                    'end' => substr($row['shift_end'], 0, 5),
                ];
            }
            return $result;
        });
    }

    /**
     * Build SQL filter for shift time window
     */
    public function buildShiftTimeFilter(int $shiftId, string $tableAlias = 'a'): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT shift_start, shift_end FROM branch_shifts WHERE id = ? AND is_active = 1");
        $stmt->execute([$shiftId]);
        $shift = $stmt->fetch();
        if (!$shift) return null;

        $startMin = $this->timeToMinutes($shift['shift_start']);
        $endMin = $this->timeToMinutes($shift['shift_end']);

        $windowStart = ($startMin - 120 + 1440) % 1440;
        $windowEnd = ($endMin + 60) % 1440;

        $wsTime = sprintf('%02d:%02d:00', intdiv($windowStart, 60), $windowStart % 60);
        $weTime = sprintf('%02d:%02d:00', intdiv($windowEnd, 60), $windowEnd % 60);

        $col = $tableAlias === '' ? 'TIME(timestamp)' : "TIME({$tableAlias}.timestamp)";

        if ($windowStart < $windowEnd) {
            return [
                'sql' => "{$col} BETWEEN ? AND ?",
                'params' => [$wsTime, $weTime],
            ];
        }
        return [
            'sql' => "({$col} >= ? OR {$col} <= ?)",
            'params' => [$wsTime, $weTime],
        ];
    }

    /**
     * Calculate late minutes for a check-in
     */
    public function calculateLateMinutes(int $employeeId, ?int $branchId = null): int
    {
        $schedule = $this->getBranchSchedule($branchId);
        $now = time();
        $referenceTimeStr = $schedule['work_start_time'];

        $workStart = strtotime(date('Y-m-d') . ' ' . $referenceTimeStr);

        // Midnight crossing
        if ($workStart > $now + 43200) {
            $workStart = strtotime(date('Y-m-d', strtotime('-1 day')) . ' ' . $referenceTimeStr);
        }

        if ($now > $workStart) {
            $rawLate = max(0, (int)round(($now - $workStart) / 60));
            $settings = $this->cache->getSettings();
            $graceMinutes = (int)($settings['late_grace_minutes'] ?? '0');
            return max(0, $rawLate - $graceMinutes);
        }

        return 0;
    }

    /**
     * Calculate early minutes for a check-in
     */
    public function calculateEarlyMinutes(?int $branchId = null): int
    {
        $schedule = $this->getBranchSchedule($branchId);
        $now = time();
        $workStart = strtotime(date('Y-m-d') . ' ' . $schedule['work_start_time']);

        if ($workStart > $now + 43200) {
            $workStart = strtotime(date('Y-m-d', strtotime('-1 day')) . ' ' . $schedule['work_start_time']);
        }

        if ($now < $workStart) {
            return max(0, (int)round(($workStart - $now) / 60));
        }

        return 0;
    }

    /**
     * Convert HH:MM to minutes since midnight
     */
    public function timeToMinutes(string $time): int
    {
        $p = explode(':', $time);
        return (int)$p[0] * 60 + (int)($p[1] ?? 0);
    }
}
