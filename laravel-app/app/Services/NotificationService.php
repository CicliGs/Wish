<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\DTOs\NotificationDisplayDTO;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wish;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Creates a notification in the database
     */
    public function createNotification(NotificationDTO $notificationDTO): Notification
    {
        try {
            return Notification::create($notificationDTO->toArray());
        } catch (Exception $e) {
            $this->logError('Failed to create notification', [
                'error' => $e->getMessage(),
                'notification_data' => $notificationDTO->toArray(),
            ]);

            throw $e;
        }
    }

    /**
     * Gets user's friends list
     */
    public function getUserFriends(int $userId): array
    {
        try {
            $friendIds = $this->getFriendIds($userId);

            if ($friendIds->isEmpty()) {
                return [];
            }

            return User::whereIn('id', $friendIds)->get()->toArray();
        } catch (Exception $e) {
            $this->logError('Failed to get user friends', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get friend IDs for a user
     */
    private function getFriendIds(int $userId): Collection
    {
        return DB::table('friends')
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function ($friendship) use ($userId) {
                return $friendship->user_id == $userId ? $friendship->friend_id : $friendship->user_id;
            })
            ->unique()
            ->values();
    }

    /**
     * Gets user's unread notifications with DTO
     */
    public function getUnreadNotifications(int $userId): Collection
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
    public function markAsRead(int $notificationId): bool
    {
        try {
            $notification = Notification::find($notificationId);

            if (!$notification) {
                return false;
            }

            $notification->update(['is_read' => true]);
            return true;
        } catch (Exception $e) {
            $this->logError('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Marks all user's notifications as read
     */
    public function markAllAsRead(int $userId): int
    {
        try {
            return Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (Exception $e) {
            $this->logError('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Gets notification by ID for specific user
     */
    public function getNotificationForUser(int $notificationId, int $userId): ?Notification
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
        $notification = $this->getNotificationForUser($notificationId, $userId);

        if (!$notification) {
            return [
                'success' => false,
                'message' => 'Notification not found or not accessible'
            ];
        }

        $success = $this->markAsRead($notificationId);

        return [
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
        ];
    }

    /**
     * Centralized error logging method
     */
    private function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }
}
