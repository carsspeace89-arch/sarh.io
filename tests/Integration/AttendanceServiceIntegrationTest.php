<?php
// =============================================================
// tests/Integration/AttendanceServiceIntegrationTest.php
// =============================================================

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Services\AttendanceService;
use App\Models\Attendance;

class AttendanceServiceIntegrationTest extends TestCase
{
    private AttendanceService $service;

    protected function setUp(): void
    {
        $this->service = new AttendanceService();
    }

    /**
     * record() يرفض نوع تسجيل غير صالح
     */
    public function testRecordRejectsInvalidType(): void
    {
        $result = $this->service->record(1, 'break', 24.7, 46.6);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('غير صالح', $result['message']);
    }

    /**
     * record() يتطلب موظف موجود
     */
    public function testRecordRejectsNonexistentEmployee(): void
    {
        $result = $this->service->record(999999, 'in', 24.7, 46.6);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('غير موجود', $result['message']);
    }

    /**
     * validateGeofence() تتحقق من إمكانية الوصول
     */
    public function testValidateGeofenceReturnsArray(): void
    {
        $result = $this->service->validateGeofence(24.7136, 46.6753, null);
        $this->assertIsArray($result);
    }

    /**
     * isWithinTimeWindow() تُرجع نتيجة منظمة
     */
    public function testTimeWindowReturnsStructuredResult(): void
    {
        $result = $this->service->isWithinTimeWindow('in', null);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('allowed', $result);
    }

    /**
     * getDashboardStats() تُرجع إحصائيات
     */
    public function testGetDashboardStatsReturnsArray(): void
    {
        $stats = $this->service->getDashboardStats();
        $this->assertIsArray($stats);
    }
}
