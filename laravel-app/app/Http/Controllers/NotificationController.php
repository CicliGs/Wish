<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Get user's unread notifications
     */
    public function getUnreadNotifications(): JsonResponse
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getUnreadNotifications($userId);

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(fn($dto) => $dto->toArray()),
            'count' => $notifications->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id'
        ]);

        $notificationId = $request->input('notification_id');
        $userId = Auth::id();

        $notification = $this->notificationService->getNotificationForUser($notificationId, $userId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or not accessible'
            ], 404);
        }

        $success = $this->notificationService->markAsRead($notificationId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
        ]);
    }

    /**
     * Mark all user's notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = Auth::id();
        $count = $this->notificationService->markAllAsRead($userId);

        return response()->json([
            'success' => true,
            'message' => 'Marked {$count} notifications as read',
            'count' => $count
        ]);
    }
}
