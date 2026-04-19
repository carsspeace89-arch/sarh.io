<?php
// =============================================================
// src/Queue/Job.php - Base Job class
// =============================================================

namespace App\Queue;

abstract class Job
{
    protected int $maxAttempts = 3;
    protected int $retryDelay = 60;

    /**
     * Execute the job
     */
    abstract public function handle(): void;

    /**
     * Called when all attempts are exhausted
     */
    public function failed(\Throwable $e): void
    {
        \App\Core\Logger::error('Job failed permanently', [
            'job' => static::class,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * Serialize job data for storage (JSON-safe)
     */
    public function serialize(): string
    {
        return json_encode([
            'class' => static::class,
            'properties' => $this->toArray(),
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Get serializable properties (override in subclasses)
     */
    protected function toArray(): array
    {
        $props = [];
        $ref = new \ReflectionClass($this);
        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);
            $val = $prop->getValue($this);
            if (is_scalar($val) || is_array($val) || is_null($val)) {
                $props[$prop->getName()] = $val;
            }
        }
        return $props;
    }

    /**
     * Deserialize job from storage (safe JSON-based)
     */
    public static function deserialize(string $data): self
    {
        $decoded = json_decode($data, true);
        if (!is_array($decoded) || empty($decoded['class'])) {
            throw new \RuntimeException('Failed to deserialize job: invalid data');
        }

        $class = $decoded['class'];

        // Whitelist: only allow known job classes
        $allowedClasses = [
            \App\Queue\Jobs\AutoCheckoutJob::class,
            \App\Queue\Jobs\GenerateReportJob::class,
            \App\Queue\Jobs\SendNotificationJob::class,
        ];

        if (!in_array($class, $allowedClasses, true)) {
            throw new \RuntimeException('Failed to deserialize job: unknown class ' . $class);
        }

        if (!class_exists($class) || !is_subclass_of($class, self::class)) {
            throw new \RuntimeException('Failed to deserialize job: invalid class ' . $class);
        }

        $job = new $class();

        // Restore properties safely
        if (!empty($decoded['properties']) && is_array($decoded['properties'])) {
            $ref = new \ReflectionClass($job);
            foreach ($decoded['properties'] as $name => $value) {
                if ($ref->hasProperty($name)) {
                    $prop = $ref->getProperty($name);
                    $prop->setAccessible(true);
                    $prop->setValue($job, $value);
                }
            }
        }

        return $job;
    }
}
