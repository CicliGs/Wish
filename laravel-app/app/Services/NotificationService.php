<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\DTOs\NotificationDisplayDTO;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification in the database.
     */
    public function create(NotificationDTO $notificationDTO): Notification
    {
        return Notification::create($notificationDTO->toArray());
    }

    /**
     * Get user's unread notifications with DTO.
     */
    public function getUnread(User $user): Collection
    {
        $notifications = Notification::with(['friend', 'wish.wishList'])
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return NotificationDisplayDTO::fromNotificationCollection($notifications);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        $notification->update(['is_read' => true]);

        return true;
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get user's unread notification by ID.
     */
    public function findUnread(User $user, int $notificationId): ?Notification
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->first();
    }
}
