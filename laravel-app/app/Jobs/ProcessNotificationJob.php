<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\NotificationDTO;
use App\Services\NotificationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_TRIES = 3;
    private const DEFAULT_MAX_EXCEPTIONS = 3;

    protected $timeout;
    protected $tries;
    protected $maxExceptions;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly NotificationDTO $notificationDTO
    ) {
        $this->configureJob();
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $this->logJobStart();

        try {
            $notification = $notificationService->createNotification($this->notificationDTO);
            $this->logJobSuccess($notification->id);
        } catch (Exception $e) {
            $this->logJobError($e);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        $this->logJobFailure($exception);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        return $this->calculateExponentialBackoff();
    }

    /**
     * Configure job settings
     */
    private function configureJob(): void
    {
        $config = config('notifications.queue');

        $this->onQueue($config['name']);
        $this->onConnection($config['connection']);
        $this->timeout = $config['timeout'] ?? self::DEFAULT_TIMEOUT;
        $this->tries = $config['tries'] ?? self::DEFAULT_TRIES;
        $this->maxExceptions = $config['max_exceptions'] ?? self::DEFAULT_MAX_EXCEPTIONS;
    }

    /**
     * Log job start
     */
    private function logJobStart(): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        Log::info('Processing notification job', $this->getLogContext([
            'user_id' => $this->notificationDTO->userId,
            'friend_id' => $this->notificationDTO->friendId,
            'wish_id' => $this->notificationDTO->wishId,
        ]));
    }

    /**
     * Log successful job completion
     */
    private function logJobSuccess(int $notificationId): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        Log::info('Notification processed successfully', $this->getLogContext([
            'notification_id' => $notificationId,
            'user_id' => $this->notificationDTO->userId,
            'friend_id' => $this->notificationDTO->friendId,
            'wish_id' => $this->notificationDTO->wishId,
        ]));
    }

    /**
     * Log job error
     */
    private function logJobError(Exception $exception): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        Log::error('Failed to process notification job', $this->getLogContext([
            'error' => $exception->getMessage(),
            'notification_data' => $this->notificationDTO->toArray(),
            'trace' => $this->shouldIncludeTrace() ? $exception->getTraceAsString() : null,
        ]));
    }

    /**
     * Log permanent job failure
     */
    private function logJobFailure(Exception $exception): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        Log::error('ProcessNotificationJob failed permanently', $this->getLogContext([
            'error' => $exception->getMessage(),
            'notification_data' => $this->notificationDTO->toArray(),
            'trace' => $this->shouldIncludeTrace() ? $exception->getTraceAsString() : null,
        ]));
    }

    /**
     * Calculate exponential backoff delay
     */
    private function calculateExponentialBackoff(): int
    {
        $config = config('notifications.queue.backoff');
        $baseDelay = $config['base_delay'] ?? 60;
        $maxDelay = $config['max_delay'] ?? 3600;
        $exponent = $this->attempts();

        $delay = $baseDelay * (2 ** $exponent);

        return min($delay, $maxDelay);
    }

    /**
     * Check if logging is enabled
     */
    private function shouldLog(): bool
    {
        return config('notifications.logging.enabled', true);
    }

    /**
     * Check if trace should be included in logs
     */
    private function shouldIncludeTrace(): bool
    {
        return config('notifications.logging.include_trace', false);
    }

    /**
     * Get log context with common fields
     */
    private function getLogContext(array $additionalData = []): array
    {
        $context = [
            'attempt' => $this->attempts(),
            'job_id' => $this->job->getJobId(),
        ];

        if (config('notifications.logging.context.include_notification_data', true)) {
            $context['notification_data'] = $this->notificationDTO->toArray();
        }

        return array_merge($context, $additionalData);
    }
}
