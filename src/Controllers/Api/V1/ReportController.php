<?php
// =============================================================
// src/Controllers/Api/V1/ReportController.php - API تقارير
// =============================================================

namespace App\Controllers\Api\V1;

use App\Core\Controller;
use App\Services\ReportingService;
use App\Services\ExportService;
use App\Services\RbacService;

class ReportController extends Controller
{
    private ReportingService $reportingService;
    private ExportService $exportService;
    private RbacService $rbac;

    public function __construct()
    {
        $this->reportingService = new ReportingService();
        $this->exportService = new ExportService();
        $this->rbac = new RbacService();
    }

    /**
     * GET /api/v1/reports/daily?date=YYYY-MM-DD&branch_id=X
     */
    public function daily(): void
    {
        $this->rbac->requirePermission('view_reports');

        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->validationError('date', 'تنسيق التاريخ غير صحيح');
            return;
        }

        $branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
        $report = $this->reportingService->dailyReport($date, $branchId);

        $this->json(['status' => 'success', 'data' => $report]);
    }

    /**
     * GET /api/v1/reports/monthly?year=YYYY&month=MM&branch_id=X
     */
    public function monthly(): void
    {
        $this->rbac->requirePermission('view_reports');

        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));

        if ($month < 1 || $month > 12 || $year < 2020 || $year > 2050) {
            $this->validationError('month', 'الشهر أو السنة غير صحيح');
            return;
        }

        $branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
        $report = $this->reportingService->monthlyReport($year, $month, $branchId);

        $this->json(['status' => 'success', 'data' => $report]);
    }

    /**
     * GET /api/v1/reports/late?from=YYYY-MM-DD&to=YYYY-MM-DD&branch_id=X
     */
    public function late(): void
    {
        $this->rbac->requirePermission('view_reports');

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $this->validationError('date', 'تنسيق التاريخ غير صحيح');
            return;
        }

        $branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;
        $report = $this->reportingService->lateReport($from, $to, $branchId);

        $this->json(['status' => 'success', 'data' => $report]);
    }

    /**
     * GET /api/v1/reports/branches?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function branches(): void
    {
        $this->rbac->requirePermission('view_reports');

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $this->validationError('date', 'تنسيق التاريخ غير صحيح');
            return;
        }

        $report = $this->reportingService->branchComparisonReport($from, $to);
        $this->json(['status' => 'success', 'data' => $report]);
    }

    /**
     * GET /api/v1/reports/dashboard
     */
    public function dashboard(): void
    {
        $this->rbac->requirePermission('view_reports');

        $stats = $this->reportingService->dashboardStats();
        $this->json(['status' => 'success', 'data' => $stats]);
    }

    /**
     * GET /api/v1/reports/export?format=csv|json&from=&to=&branch_id=&employee_id=
     */
    public function export(): void
    {
        $this->rbac->requirePermission('export_data');

        $format = $_GET['format'] ?? 'csv';
        if (!in_array($format, ['csv', 'json'], true)) {
            $this->validationError('format', 'التنسيق غير مدعوم، استخدم csv أو json');
            return;
        }

        $filters = [
            'from' => $_GET['from'] ?? null,
            'to' => $_GET['to'] ?? null,
            'branch_id' => isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null,
            'employee_id' => isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null,
        ];

        if ($format === 'csv') {
            $this->exportService->exportCsv($filters);
        } else {
            $this->exportService->exportJson($filters);
        }
    }
}
