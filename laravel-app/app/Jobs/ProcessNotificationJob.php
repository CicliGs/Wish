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
use Psr\Log\LoggerInterface;

class ProcessNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        private readonly NotificationDTO $notificationDTO,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void {
        $this->logJobStart();

        try {
            $notification = $this->notificationService->create($this->notificationDTO);
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
     * Log job start
     */
    private function logJobStart(): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        $this->logger->info('ProcessNotificationJob: Processing notification job', $this->getLogContext([
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
    }

    /**
     * Log job error
     */
    private function logJobError(Exception $exception): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        $this->logger->error('ProcessNotificationJob: Failed to process notification job', $this->getLogContext([
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

        $this->logger->error('ProcessNotificationJob: Failed permanently', $this->getLogContext([
            'error' => $exception->getMessage(),
            'notification_data' => $this->notificationDTO->toArray(),
            'trace' => $this->shouldIncludeTrace() ? $exception->getTraceAsString() : null,
        ]));
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
            'job_id' => $this->job?->getJobId(),
        ];

        if (config('notifications.logging.context.include_notification_data', true)) {
            $context['notification_data'] = $this->notificationDTO->toArray();
        }

        return array_merge($context, $additionalData);
    }
}
