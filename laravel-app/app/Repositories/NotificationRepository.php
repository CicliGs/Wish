<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;

final class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Create new notification
     */
    public function create(array $data): object
    {
        $notification = parent::create($data);
        return $notification instanceof Notification ? $notification : throw new \RuntimeException('Failed to create notification');
    }

    /**
     * @return array<object>
     */
    public function findUnreadForUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->with(['friend', 'wish.wishList'])
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    public function findUnreadByIdForUser(object $user, int $notificationId): ?object
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        $notification = $this->model
            ->where('id', $notificationId)
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->first();

        return $notification instanceof Notification ? $notification : null;
    }

    public function markAsRead(object $notification): object
    {
        if (!$notification instanceof Notification) {
            throw new \InvalidArgumentException('Notification must be an instance of ' . Notification::class);
        }
        return $this->update($notification, ['is_read' => true]);
    }

    public function markAllAsReadForUser(object $user): int
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}

