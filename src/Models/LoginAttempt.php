<?php
// =============================================================
// src/Models/LoginAttempt.php - نموذج محاولات تسجيل الدخول
// =============================================================

namespace App\Models;

use App\Core\Model;

class LoginAttempt extends Model
{
    protected string $table = 'login_attempts';

    /**
     * تنظيف المحاولات القديمة
     */
    public function cleanup(int $windowMinutes): void
    {
        $this->query(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$windowMinutes]
        );
    }

    /**
     * عدد المحاولات من IP خلال فترة
     */
    public function countByIp(string $ip, int $windowMinutes): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$ip, $windowMinutes]
        );
        return (int)$stmt->fetchColumn();
    }

    /**
     * تسجيل محاولة فاشلة
     */
    public function recordFailed(string $ip, string $username): void
    {
        $this->create([
            'ip_address' => $ip,
            'username' => $username,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
