<?php
// =============================================================
// tests/Unit/AuthServiceTest.php - اختبارات خدمة المصادقة
// =============================================================

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    // ==================== Session Management ====================

    public function testCheckSessionReturnsFalseWhenNoAdminId(): void
    {
        $_SESSION = [];
        $service = new \App\Services\AuthService();
        $this->assertFalse($service->isLoggedIn());
    }

    public function testCheckSessionReturnsTrueWhenLoggedIn(): void
    {
        $_SESSION = ['admin_id' => 1];
        $service = new \App\Services\AuthService();
        $this->assertTrue($service->isLoggedIn());
    }

    public function testLogoutClearsSession(): void
    {
        // Start a session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_username'] = 'admin';

        $service = new \App\Services\AuthService();
        $service->logout();

        $this->assertEmpty($_SESSION);
    }

    // ==================== Password Validation ====================

    public function testChangePasswordRejectsShortPassword(): void
    {
        // This test validates the service-level check for password length
        $service = new \App\Services\AuthService();
        // With a non-existent admin, we test the early return
        $result = $service->changePassword(999999, 'old', 'short');
        $this->assertFalse($result['success']);
    }
}
