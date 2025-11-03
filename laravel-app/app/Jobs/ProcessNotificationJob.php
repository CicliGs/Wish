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
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Container\Container;

class ProcessNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        private readonly NotificationDTO $notificationDTO
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(
        NotificationService $notificationService,
        LoggerInterface $logger,
        ConfigRepository $config
    ): void {
        $this->logJobStart($logger, $config);

        try {
            $notification = $notificationService->create($this->notificationDTO);
            $this->logJobSuccess($notification->id, $logger, $config);
        } catch (Exception $e) {
            $this->logJobError($e, $logger, $config);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception, Container $container): void
    {
        $logger = $container->make(LoggerInterface::class);
        $config = $container->make(ConfigRepository::class);
        $this->logJobFailure($exception, $logger, $config);
    }

    /**
     * Log job start
     */
    private function logJobStart(LoggerInterface $logger, ConfigRepository $config): void
    {
        if (!$this->shouldLog($config)) {
            return;
        }

        $logger->info('ProcessNotificationJob: Processing notification job', $this->getLogContext($config, [
            'user_id' => $this->notificationDTO->userId,
            'friend_id' => $this->notificationDTO->friendId,
            'wish_id' => $this->notificationDTO->wishId,
        ]));
    }

    /**
     * Log successful job completion
     */
    private function logJobSuccess(int $notificationId, LoggerInterface $logger, ConfigRepository $config): void
    {
        if (!$this->shouldLog($config)) {
            return;
        }
    }

    /**
     * Log job error
     */
    private function logJobError(Exception $exception, LoggerInterface $logger, ConfigRepository $config): void
    {
        if (!$this->shouldLog($config)) {
            return;
        }

        $logger->error('ProcessNotificationJob: Failed to process notification job', $this->getLogContext($config, [
            'error' => $exception->getMessage(),
            'notification_data' => $this->notificationDTO->toArray(),
            'trace' => $this->shouldIncludeTrace($config) ? $exception->getTraceAsString() : null,
        ]));
    }

    /**
     * Log permanent job failure
     */
    private function logJobFailure(Exception $exception, LoggerInterface $logger, ConfigRepository $config): void
    {
        if (!$this->shouldLog($config)) {
            return;
        }

        $logger->error('ProcessNotificationJob: Failed permanently', $this->getLogContext($config, [
            'error' => $exception->getMessage(),
            'notification_data' => $this->notificationDTO->toArray(),
            'trace' => $this->shouldIncludeTrace($config) ? $exception->getTraceAsString() : null,
        ]));
    }

    /**
     * Check if logging is enabled
     */
    private function shouldLog(ConfigRepository $config): bool
    {
        return $config->get('notifications.logging.enabled', true);
    }

    /**
     * Check if trace should be included in logs
     */
    private function shouldIncludeTrace(ConfigRepository $config): bool
    {
        return $config->get('notifications.logging.include_trace', false);
    }

    /**
     * Get log context with common fields
     */
    private function getLogContext(ConfigRepository $config, array $additionalData = []): array
    {
        $context = [
            'attempt' => $this->attempts(),
            'job_id' => $this->job?->getJobId(),
        ];

        if ($config->get('notifications.logging.context.include_notification_data', true)) {
            $context['notification_data'] = $this->notificationDTO->toArray();
        }

        return array_merge($context, $additionalData);
    }
}
