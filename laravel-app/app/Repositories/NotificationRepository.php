<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;
use App\Exceptions\NotificationCreationFailedException;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use InvalidArgumentException;

/**
 * Repository for managing notification operations.
 */
final class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new notification.
     */
    public function create(array $data): object
    {
        $notification = parent::create($data);

        return $notification instanceof Notification ? $notification : throw new NotificationCreationFailedException();
    }

    /**
     * Find all unread notifications for a user.
     * Includes related friend and wish data with ordering by creation date.
     */
    public function findUnreadForUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $this->model
            ->with(['friend', 'wish.wishList'])
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * Find a specific unread notification by ID for a user.
     */
    public function findUnreadByIdForUser(object $user, int $notificationId): ?object
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        $notification = $this->model
            ->where('id', $notificationId)
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->first();

        return $notification instanceof Notification ? $notification : null;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(object $notification): object
    {
        if (!$notification instanceof Notification) {
            throw new InvalidArgumentException('Notification must be an instance of ' . Notification::class);
        }

        return $this->update($notification, ['is_read' => true]);
    }

    /**
     * Mark all unread notifications as read for a user.
     */
    public function markAllAsReadForUser(object $user): int
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $this->model
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}

