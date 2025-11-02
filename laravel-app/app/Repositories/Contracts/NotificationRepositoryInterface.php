<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;


interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Create new notification
     */
    public function create(array $data): object;

    /**
     * Find unread notifications for user
     * 
     * @return array<object>
     */
    public function findUnreadForUser(object $user): array;

    /**
     * Find unread notification by ID for user
     */
    public function findUnreadByIdForUser(object $user, int $notificationId): ?object;

    /**
     * Mark notification as read
     */
    public function markAsRead(object $notification): object;

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsReadForUser(object $user): int;
}

