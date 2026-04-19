<?php
// =============================================================
// src/Queue/Jobs/SendNotificationJob.php - Async Notification Sending
// =============================================================

namespace App\Queue\Jobs;

use App\Queue\Job;
use App\Core\Logger;

class SendNotificationJob extends Job
{
    protected int $maxAttempts = 3;
    protected int $retryDelay = 30;

    private string $channel;
    private string $recipient;
    private string $message;
    private array $options;

    public function __construct(string $channel, string $recipient, string $message, array $options = [])
    {
        $this->channel = $channel;
        $this->recipient = $recipient;
        $this->message = $message;
        $this->options = $options;
    }

    public function handle(): void
    {
        $provider = \App\Services\WhatsAppService::getInstance();

        switch ($this->channel) {
            case 'whatsapp':
                $result = $provider->sendMessage($this->recipient, $this->message, $this->options);
                break;
            default:
                Logger::warning('Unknown notification channel', ['channel' => $this->channel]);
                return;
        }

        if (!$result['success']) {
            throw new \RuntimeException("Notification send failed: " . ($result['error'] ?? 'unknown'));
        }

        Logger::queue('Notification sent', [
            'channel' => $this->channel,
            'recipient' => $this->recipient,
        ]);
    }
}
