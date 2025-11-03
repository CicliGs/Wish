<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MarkNotificationAsReadRequest;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Controller as BaseController;

final class NotificationController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly Guard $auth
    ) {}

    /**
     * Get user's unread notifications
     */
    public function getUnreadNotifications(): JsonResponse
    {
        $notifications = $this->notificationService->getUnread($this->auth->user());

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(fn($dto) => $dto->toArray()),
            'count' => $notifications->count()
        ]);
    }

    /**
     * Mark notification as read
     *
     * @throws ModelNotFoundException
     */
    public function markAsRead(MarkNotificationAsReadRequest $request): JsonResponse
    {
        $notificationId = $request->input('notification_id');
        $notification = $this->notificationService->findUnread($this->auth->user(), $notificationId);

        if (!$notification) {
            throw new ModelNotFoundException('Notification not found or not accessible');
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
        $count = $this->notificationService->markAllAsRead($this->auth->user());

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'count' => $count
        ]);
    }
}
