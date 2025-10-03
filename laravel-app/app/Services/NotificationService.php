<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\DTOs\NotificationDisplayDTO;
use App\Models\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Creates a notification in the database
     */
    public function createNotificationFromDTO(NotificationDTO $notificationDTO): Notification
    {
        return Notification::create($notificationDTO->toArray());
    }

    /**
     * Gets user's unread notifications with DTO
     */
    public function getUnreadNotificationsForUser(int $userId): Collection
    {
        $notifications = Notification::with(['friend', 'wish.wishList'])
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return NotificationDisplayDTO::fromNotificationCollection($notifications);
    }

    /**
     * Marks a notification as read
     */
    public function markNotificationAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->update(['is_read' => true]);

        return true;
    }

    /**
     * Marks all user's notifications as read
     */
    public function markAllNotificationsAsReadForUser(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Gets notification by ID for specific user
     */
    public function getUnreadNotificationForUser(int $notificationId, int $userId): ?Notification
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->first();
    }

    /**
     * Mark notification as read for specific user with validation
     */
    public function markNotificationAsReadForUser(int $notificationId, int $userId): array
    {
        $notification = $this->getUnreadNotificationForUser($notificationId, $userId);

        if (!$notification) {
            return [
                'success' => false,
                'message' => 'Notification not found or not accessible'
            ];
        }

        $success = $this->markNotificationAsRead($notificationId);

        return [
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
        ];
    }
}
