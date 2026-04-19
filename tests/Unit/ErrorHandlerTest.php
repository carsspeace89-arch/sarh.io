<?php
// =============================================================
// tests/Unit/ErrorHandlerTest.php - اختبارات معالج الأخطاء
// =============================================================

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\ErrorHandler;

class ErrorHandlerTest extends TestCase
{
    public function testRegisterDoesNotThrow(): void
    {
        // Should be safe to call multiple times
        ErrorHandler::register();
        ErrorHandler::register();
        $this->assertTrue(true);
    }

    public function testHandleErrorThrowsForFatalErrors(): void
    {
        $this->expectException(\ErrorException::class);
        ErrorHandler::handleError(E_ERROR, 'Fatal error', __FILE__, __LINE__);
    }

    public function testHandleErrorReturnsForNotices(): void
    {
        // Notices should be logged but not thrown
        $result = ErrorHandler::handleError(E_NOTICE, 'Undefined variable', __FILE__, __LINE__);
        $this->assertTrue($result);
    }

    public function testHandleErrorReturnsForWarnings(): void
    {
        $result = ErrorHandler::handleError(E_WARNING, 'Minor issue', __FILE__, __LINE__);
        $this->assertTrue($result);
    }
}
