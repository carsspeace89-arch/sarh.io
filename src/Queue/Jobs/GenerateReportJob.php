<?php
// =============================================================
// src/Queue/Jobs/GenerateReportJob.php - Async Report Generation
// =============================================================

namespace App\Queue\Jobs;

use App\Queue\Job;
use App\Core\Database;
use App\Core\Logger;

class GenerateReportJob extends Job
{
    protected int $maxAttempts = 2;
    protected int $retryDelay = 120;

    private string $reportType;
    private array $filters;
    private int $requestedBy;

    public function __construct(string $reportType, array $filters, int $requestedBy)
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
        $this->requestedBy = $requestedBy;
    }

    public function handle(): void
    {
        $exportService = new \App\Services\ExportService();

        $outputDir = dirname(__DIR__, 3) . '/storage/reports';
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0700, true);
        }

        $filename = $this->reportType . '_' . date('Y-m-d_His') . '_' . bin2hex(random_bytes(4));

        switch ($this->reportType) {
            case 'attendance_csv':
                $filePath = $outputDir . '/' . $filename . '.csv';
                $this->generateCsv($filePath);
                break;
            case 'attendance_json':
                $filePath = $outputDir . '/' . $filename . '.json';
                $this->generateJson($filePath);
                break;
            default:
                throw new \RuntimeException("Unknown report type: {$this->reportType}");
        }

        // Store reference for download
        $db = Database::getInstance();
        try {
            $db->prepare("
                INSERT INTO generated_reports (filename, report_type, filters, requested_by, file_path, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ")->execute([
                basename($filePath),
                $this->reportType,
                json_encode($this->filters),
                $this->requestedBy,
                $filePath,
            ]);
        } catch (\Throwable $e) {
            // Table may not exist yet - log but don't fail
            Logger::warning('Could not store report reference', ['error' => $e->getMessage()]);
        }

        Logger::queue('Report generated', [
            'type' => $this->reportType,
            'file' => basename($filePath),
            'requested_by' => $this->requestedBy,
        ]);
    }

    private function generateCsv(string $filePath): void
    {
        $attendance = new \App\Models\Attendance();
        $result = $attendance->getFilteredReport($this->filters, 1, 100000);

        $fp = fopen($filePath, 'w');
        fwrite($fp, "\xEF\xBB\xBF"); // BOM
        fputcsv($fp, ['الموظف', 'المسمى', 'الفرع', 'النوع', 'التاريخ', 'الوقت', 'التأخير']);

        $types = ['in' => 'حضور', 'out' => 'انصراف'];
        foreach ($result['data'] as $r) {
            fputcsv($fp, [
                $r['employee_name'],
                $r['job_title'] ?? '',
                $r['branch_name'] ?? '-',
                $types[$r['type']] ?? $r['type'],
                date('Y-m-d', strtotime($r['timestamp'])),
                date('H:i:s', strtotime($r['timestamp'])),
                $r['late_minutes'] ?? 0,
            ]);
        }
        fclose($fp);
    }

    private function generateJson(string $filePath): void
    {
        $attendance = new \App\Models\Attendance();
        $result = $attendance->getFilteredReport($this->filters, 1, 100000);

        file_put_contents($filePath, json_encode([
            'generated_at' => date('Y-m-d H:i:s'),
            'total_records' => $result['total'],
            'data' => $result['data'],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
