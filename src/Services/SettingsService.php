<?php
// =============================================================
// src/Services/SettingsService.php - خدمة الإعدادات المُخزنة مؤقتاً
// =============================================================

namespace App\Services;

use App\Core\Database;

class SettingsService
{
    private static ?self $instance = null;
    private array $cache = [];
    private bool $loaded = false;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * جلب إعداد واحد
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureLoaded();
        return $this->cache[$key] ?? $default;
    }

    /**
     * جلب عدة إعدادات دفعة واحدة
     */
    public function getMany(array $keys): array
    {
        $this->ensureLoaded();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->cache[$key] ?? null;
        }
        return $result;
    }

    /**
     * تحديث إعداد
     */
    public function set(string $key, string $value): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->execute([$key, $value]);
        $this->cache[$key] = $value;
    }

    /**
     * تحديث عدة إعدادات دفعة واحدة
     */
    public function setMany(array $settings): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, (string)$value]);
            $this->cache[$key] = (string)$value;
        }
    }

    /**
     * مسح الكاش لإعادة التحميل
     */
    public function clearCache(): void
    {
        $this->cache = [];
        $this->loaded = false;
    }

    /**
     * جلب كل الإعدادات
     */
    public function all(): array
    {
        $this->ensureLoaded();
        return $this->cache;
    }

    /**
     * التحميل الأولي — استعلام واحد فقط لكل الإعدادات
     */
    private function ensureLoaded(): void
    {
        if ($this->loaded) return;

        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            $this->cache = $rows ?: [];
        } catch (\Throwable $e) {
            $this->cache = [];
        }

        $this->loaded = true;
    }
}
