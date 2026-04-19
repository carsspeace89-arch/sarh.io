<?php
// =============================================================
// src/Services/WhatsAppService.php - WhatsApp Business API Abstraction
// =============================================================
// Supports multiple providers: WhatsApp Business API, Twilio, 360dialog.
// Falls back to wa.me link generation when no API is configured.
// =============================================================

namespace App\Services;

use App\Core\Logger;

class WhatsAppService
{
    private static ?self $instance = null;
    private string $provider;
    private array $config;

    // Provider constants
    public const PROVIDER_NONE = 'none';
    public const PROVIDER_TWILIO = 'twilio';
    public const PROVIDER_360DIALOG = '360dialog';
    public const PROVIDER_WABA = 'waba';

    public function __construct(?array $config = null)
    {
        if ($config === null) {
            $cache = RedisCacheService::getInstance();
            $settings = $cache->getSettings();
            $config = [
                'provider' => $settings['whatsapp_provider'] ?? self::PROVIDER_NONE,
                'twilio_sid' => $settings['twilio_sid'] ?? '',
                'twilio_token' => $settings['twilio_token'] ?? '',
                'twilio_from' => $settings['twilio_from'] ?? '',
                'dialog_api_key' => $settings['360dialog_api_key'] ?? '',
                'dialog_namespace' => $settings['360dialog_namespace'] ?? '',
                'waba_token' => $settings['waba_token'] ?? '',
                'waba_phone_id' => $settings['waba_phone_id'] ?? '',
            ];
        }
        $this->provider = $config['provider'] ?? self::PROVIDER_NONE;
        $this->config = $config;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send a WhatsApp message via configured provider
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        $phone = $this->normalizePhone($phone);

        return match ($this->provider) {
            self::PROVIDER_TWILIO => $this->sendViaTwilio($phone, $message, $options),
            self::PROVIDER_360DIALOG => $this->sendVia360Dialog($phone, $message, $options),
            self::PROVIDER_WABA => $this->sendViaWABA($phone, $message, $options),
            default => $this->generateLink($phone, $message),
        };
    }

    /**
     * Send bulk messages (dispatches each to queue)
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        $queue = \App\Queue\QueueManager::getInstance();
        $dispatched = 0;

        foreach ($recipients as $recipient) {
            $phone = $recipient['phone'] ?? '';
            if (empty($phone)) continue;

            $personalMessage = $message;
            // Replace placeholders
            foreach ($recipient as $key => $value) {
                if (is_string($value)) {
                    $personalMessage = str_replace(":{$key}", $value, $personalMessage);
                }
            }

            $job = new \App\Queue\Jobs\SendNotificationJob('whatsapp', $phone, $personalMessage, $options);
            $queue->dispatch($job);
            $dispatched++;
        }

        return ['success' => true, 'dispatched' => $dispatched];
    }

    /**
     * Generate wa.me link (fallback when no API configured)
     */
    public function generateLink(string $phone, string $message): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $link = "https://wa.me/{$phone}?text=" . urlencode($message);

        return [
            'success' => true,
            'method' => 'link',
            'link' => $link,
            'message' => 'No WhatsApp API configured. Link generated.',
        ];
    }

    /**
     * Generate attendance link message for employee
     */
    public function generateAttendanceLink(string $phone, string $token, string $employeeName = ''): array
    {
        $siteUrl = defined('SITE_URL') ? SITE_URL : '';
        $url = $siteUrl . '/employee/attendance.php?token=' . $token;
        $message = "مرحباً";
        if ($employeeName) {
            $message .= " {$employeeName}";
        }
        $message .= "،\nهذا رابط تسجيل الحضور والانصراف الخاص بك:\n{$url}\n\nيرجى استخدامه يومياً لتسجيل حضورك وانصرافك.";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Check if API sending is available
     */
    public function isApiAvailable(): bool
    {
        return $this->provider !== self::PROVIDER_NONE;
    }

    /**
     * Get current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    // ==========================================
    // Provider implementations
    // ==========================================

    /**
     * Send via Twilio WhatsApp API
     */
    private function sendViaTwilio(string $phone, string $message, array $options): array
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->config['twilio_sid']}/Messages.json";

        $data = [
            'From' => "whatsapp:{$this->config['twilio_from']}",
            'To' => "whatsapp:+{$phone}",
            'Body' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_USERPWD => $this->config['twilio_sid'] . ':' . $this->config['twilio_token'],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('Twilio request failed', ['error' => $error, 'phone' => $phone]);
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;

        if (!$success) {
            Logger::warning('Twilio send failed', [
                'http_code' => $httpCode,
                'phone' => $phone,
                'error' => $result['message'] ?? 'unknown',
            ]);
        }

        return [
            'success' => $success,
            'method' => 'twilio',
            'sid' => $result['sid'] ?? null,
            'error' => $success ? null : ($result['message'] ?? 'Send failed'),
        ];
    }

    /**
     * Send via 360dialog WhatsApp API
     */
    private function sendVia360Dialog(string $phone, string $message, array $options): array
    {
        $url = 'https://waba.360dialog.io/v1/messages';

        $payload = [
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'D360-API-KEY: ' . $this->config['dialog_api_key'],
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('360dialog request failed', ['error' => $error, 'phone' => $phone]);
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;

        if (!$success) {
            Logger::warning('360dialog send failed', [
                'http_code' => $httpCode,
                'phone' => $phone,
            ]);
        }

        return [
            'success' => $success,
            'method' => '360dialog',
            'message_id' => $result['messages'][0]['id'] ?? null,
            'error' => $success ? null : ($result['errors'][0]['title'] ?? 'Send failed'),
        ];
    }

    /**
     * Send via WhatsApp Business API (Meta Cloud API)
     */
    private function sendViaWABA(string $phone, string $message, array $options): array
    {
        $phoneId = $this->config['waba_phone_id'];
        $url = "https://graph.facebook.com/v18.0/{$phoneId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['waba_token'],
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error('WABA request failed', ['error' => $error, 'phone' => $phone]);
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);
        $success = $httpCode >= 200 && $httpCode < 300;

        return [
            'success' => $success,
            'method' => 'waba',
            'message_id' => $result['messages'][0]['id'] ?? null,
            'error' => $success ? null : ($result['error']['message'] ?? 'Send failed'),
        ];
    }

    /**
     * Normalize phone number (strip non-digits, ensure country code)
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Saudi Arabia: convert 05xxxxxxxx to 9665xxxxxxxx
        if (str_starts_with($phone, '05') && strlen($phone) === 10) {
            $phone = '966' . substr($phone, 1);
        }
        return $phone;
    }
}
