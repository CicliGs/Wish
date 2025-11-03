<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Bus\PendingDispatch;

final readonly class NotificationJobDispatcher
{
    public function __construct(
        private ConfigRepository $config
    ) {}

    public function dispatch(NotificationDTO $notificationDTO): PendingDispatch
    {
        $queueConfig = $this->config->get('notifications.queue', []);

        $queueName = $queueConfig['name'] ?? 'notifications';
        $connection = $queueConfig['connection'] ?? 'sync';

        return ProcessNotificationJob::dispatch($notificationDTO)
            ->onQueue($queueName)
            ->onConnection($connection);
    }
}

