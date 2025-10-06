<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MarkNotificationAsReadRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
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
        $notifications = $this->notificationService->getUnread(Auth::user());

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(fn($dto) => $dto->toArray()),
            'count' => $notifications->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(MarkNotificationAsReadRequest $request): JsonResponse
    {
        $notificationId = $request->input('notification_id');
        $notification = $this->notificationService->findUnread(Auth::user(), $notificationId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or not accessible'
            ], 404);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all user's notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'count' => $count
        ]);
    }
}
