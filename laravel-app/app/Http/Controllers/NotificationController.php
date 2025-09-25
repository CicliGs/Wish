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
        $userId = Auth::id();
        $notifications = $this->notificationService->getUnreadNotificationsForUser($userId);

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
        $result = $this->notificationService->markNotificationAsReadForUser(
            $request->input('notification_id'),
            Auth::id()
        );

        $statusCode = $result['success'] ? 200 : 404;
        return response()->json($result, $statusCode);
    }

    /**
     * Mark all user's notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = Auth::id();
        $count = $this->notificationService->markAllNotificationsAsReadForUser($userId);

        return response()->json([
            'success' => true,
            'message' => 'Marked {$count} notifications as read',
            'count' => $count
        ]);
    }
}
