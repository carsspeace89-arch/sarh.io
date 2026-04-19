<?php
// =============================================================
// src/Services/ReportingService.php - محرك التقارير المركزي
// =============================================================

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Branch;
use App\Core\Database;
use App\Core\Logger;

class ReportingService
{
    private Attendance $attendance;
    private Employee $employee;
    private Branch $branch;

    public function __construct()
    {
        $this->attendance = new Attendance();
        $this->employee = new Employee();
        $this->branch = new Branch();
    }

    /**
     * تقرير الحضور اليومي
     */
    public function dailyReport(string $date, ?int $branchId = null): array
    {
        $db = Database::getInstance();
        $params = [$date, $date];
        $branchFilter = '';

        if ($branchId) {
            $branchFilter = 'AND e.branch_id = ?';
            $params[] = $branchId;
        }

        $records = $db->prepare("
            SELECT e.id, e.name, e.job_title, b.name AS branch_name,
                   MIN(CASE WHEN a.type = 'in' THEN TIME(a.timestamp) END) AS first_in,
                   MAX(CASE WHEN a.type = 'out' THEN TIME(a.timestamp) END) AS last_out,
                   SUM(CASE WHEN a.type = 'in' THEN a.late_minutes ELSE 0 END) AS late_minutes,
                   COUNT(CASE WHEN a.type = 'in' THEN 1 END) AS check_ins,
                   COUNT(CASE WHEN a.type = 'out' THEN 1 END) AS check_outs
            FROM employees e
            LEFT JOIN attendances a ON a.employee_id = e.id AND a.attendance_date = ?
            LEFT JOIN branches b ON e.branch_id = b.id
            WHERE e.is_active = 1 AND e.deleted_at IS NULL {$branchFilter}
            GROUP BY e.id
            ORDER BY b.name, e.name
        ");
        $records->execute($params);
        $data = $records->fetchAll();

        // Calculate summary
        $totalEmployees = count($data);
        $present = 0;
        $absent = 0;
        $late = 0;
        $totalLateMinutes = 0;

        foreach ($data as &$row) {
            $row['status'] = $row['first_in'] ? 'present' : 'absent';
            if ($row['first_in']) {
                $present++;
                if ($row['late_minutes'] > 0) {
                    $late++;
                    $totalLateMinutes += $row['late_minutes'];
                }
            } else {
                $absent++;
            }
        }

        return [
            'date' => $date,
            'branch_id' => $branchId,
            'summary' => [
                'total_employees' => $totalEmployees,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'attendance_rate' => $totalEmployees > 0 ? round(($present / $totalEmployees) * 100, 1) : 0,
                'total_late_minutes' => $totalLateMinutes,
            ],
            'records' => $data,
        ];
    }

    /**
     * تقرير شهري ملخص
     */
    public function monthlyReport(int $year, int $month, ?int $branchId = null): array
    {
        $db = Database::getInstance();
        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo = date('Y-m-t', strtotime($dateFrom));
        $workingDays = $this->countWorkingDays($dateFrom, $dateTo);

        $params = [$dateFrom, $dateTo, $dateFrom, $dateTo];
        $branchFilter = '';
        if ($branchId) {
            $branchFilter = 'AND e.branch_id = ?';
            $params[] = $branchId;
        }

        $data = $db->prepare("
            SELECT e.id, e.name, e.job_title, b.name AS branch_name,
                   COUNT(DISTINCT CASE WHEN a.type = 'in' THEN a.attendance_date END) AS days_attended,
                   SUM(CASE WHEN a.type = 'in' THEN a.late_minutes ELSE 0 END) AS total_late_minutes,
                   COUNT(CASE WHEN a.type = 'in' AND a.late_minutes > 0 THEN 1 END) AS late_days,
                   SUM(CASE WHEN a.type = 'in' THEN a.early_minutes ELSE 0 END) AS total_early_minutes
            FROM employees e
            LEFT JOIN attendances a ON a.employee_id = e.id
                AND a.attendance_date BETWEEN ? AND ?
            LEFT JOIN branches b ON e.branch_id = b.id
            WHERE e.is_active = 1 AND e.deleted_at IS NULL {$branchFilter}
            GROUP BY e.id
            ORDER BY b.name, e.name
        ");
        $data->execute($params);
        $records = $data->fetchAll();

        foreach ($records as &$row) {
            $attended = (int)$row['days_attended'];
            $row['absent_days'] = max(0, $workingDays - $attended);
            $row['attendance_rate'] = $workingDays > 0 ? round(($attended / $workingDays) * 100, 1) : 0;

            // Rating
            $rate = $row['attendance_rate'];
            if ($rate >= 95 && $row['late_days'] <= 1) {
                $row['rating'] = 'ممتاز';
            } elseif ($rate >= 85) {
                $row['rating'] = 'جيد';
            } elseif ($rate >= 70) {
                $row['rating'] = 'مقبول';
            } else {
                $row['rating'] = 'يحتاج مراجعة';
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'working_days' => $workingDays,
            'branch_id' => $branchId,
            'records' => $records,
        ];
    }

    /**
     * تقرير التأخير
     */
    public function lateReport(string $dateFrom, string $dateTo, ?int $branchId = null): array
    {
        $records = $this->attendance->getLateReport($dateFrom, $dateTo, $branchId);

        $totalLateMinutes = 0;
        $totalLateDays = 0;
        foreach ($records as $r) {
            $totalLateMinutes += $r['total_late_minutes'];
            $totalLateDays += $r['late_days'];
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => [
                'total_employees_late' => count($records),
                'total_late_minutes' => $totalLateMinutes,
                'total_late_days' => $totalLateDays,
                'avg_late_per_employee' => count($records) > 0 ? round($totalLateMinutes / count($records)) : 0,
            ],
            'records' => $records,
        ];
    }

    /**
     * تقرير مقارنة الفروع
     */
    public function branchComparisonReport(string $dateFrom, string $dateTo): array
    {
        $db = Database::getInstance();

        $data = $db->prepare("
            SELECT b.id, b.name,
                   COUNT(DISTINCT e.id) AS total_employees,
                   COUNT(DISTINCT CASE WHEN a.type = 'in' THEN CONCAT(a.employee_id, '-', a.attendance_date) END) AS total_check_ins,
                   COUNT(DISTINCT a.attendance_date) AS working_days,
                   AVG(CASE WHEN a.type = 'in' THEN a.late_minutes END) AS avg_late_minutes,
                   SUM(CASE WHEN a.type = 'in' AND a.late_minutes > 0 THEN 1 ELSE 0 END) AS late_count
            FROM branches b
            LEFT JOIN employees e ON e.branch_id = b.id AND e.is_active = 1 AND e.deleted_at IS NULL
            LEFT JOIN attendances a ON a.employee_id = e.id AND a.attendance_date BETWEEN ? AND ?
            WHERE b.is_active = 1
            GROUP BY b.id
            ORDER BY b.name
        ");
        $data->execute([$dateFrom, $dateTo]);
        $records = $data->fetchAll();

        foreach ($records as &$row) {
            $row['avg_late_minutes'] = round((float)$row['avg_late_minutes'], 1);
            $possibleAttendances = $row['total_employees'] * $row['working_days'];
            $row['attendance_rate'] = $possibleAttendances > 0
                ? round(($row['total_check_ins'] / $possibleAttendances) * 100, 1) : 0;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'branches' => $records,
        ];
    }

    /**
     * إحصائيات لوحة التحكم
     */
    public function dashboardStats(): array
    {
        $todayStats = $this->attendance->getTodayStats();
        $recentRecords = $this->attendance->getRecent(10);
        $absentToday = $this->employee->getAbsentToday(10);

        $totalEmployees = (int)($todayStats['total_employees'] ?? 0);
        $checkedIn = (int)($todayStats['checked_in'] ?? 0);

        return [
            'today' => [
                'total_employees' => $totalEmployees,
                'checked_in' => $checkedIn,
                'checked_out' => (int)($todayStats['checked_out'] ?? 0),
                'absent' => max(0, $totalEmployees - $checkedIn),
                'attendance_rate' => $totalEmployees > 0 ? round(($checkedIn / $totalEmployees) * 100, 1) : 0,
            ],
            'recent_records' => $recentRecords,
            'absent_today' => $absentToday,
        ];
    }

    /**
     * حساب أيام العمل (استبعاد الجمعة/السبت)
     */
    private function countWorkingDays(string $from, string $to): int
    {
        $start = new \DateTime($from);
        $end = new \DateTime($to);
        $days = 0;

        while ($start <= $end) {
            $dow = (int)$start->format('N'); // 1=Mon, 5=Fri, 6=Sat, 7=Sun
            // Saudi: Friday(5) and Saturday(6) are weekends
            if ($dow !== 5 && $dow !== 6) {
                $days++;
            }
            $start->modify('+1 day');
        }

        return $days;
    }
}
