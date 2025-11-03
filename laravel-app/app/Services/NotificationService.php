<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\DTOs\NotificationDisplayDTO;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly WishRepositoryInterface $wishRepository,
        private readonly WishListRepositoryInterface $wishListRepository
    ) {}

    /**
     * Create a notification in the database.
     */
    public function create(NotificationDTO $notificationDTO): Notification
    {
        $result = $this->notificationRepository->create($notificationDTO->toArray());
        if (!($result instanceof Notification)) {
            throw new \RuntimeException('Failed to create notification');
        }
        return $result;
    }

    /**
     * Get user's unread notifications with DTO.
     */
    public function getUnread(User $user): Collection
    {
        $notifications = $this->notificationRepository->findUnreadForUser($user);

        return collect($notifications)->map(function ($notification) {
            if (!$notification instanceof Notification) {
                return null;
            }
            
            $friendId = $notification->friend_id ?? null;
            $friend = $friendId ? $this->userRepository->findById($friendId) : null;
            
            $wishId = $notification->wish_id ?? null;
            $wish = $wishId ? $this->wishRepository->findById($wishId) : null;
            
            $wishList = null;
            $wishListId = null;
            if ($wish instanceof \App\Models\Wish) {
                $wishListId = $wish->wish_list_id ?? null;
                if ($wishListId) {
                    $wishList = $this->wishListRepository->findById($wishListId);
                }
            }

            $isRead = (bool) ($notification->is_read ?? false);
            $updatedAt = $notification->updated_at;
            $createdAt = $notification->created_at;

            $wishTitle = __('messages.unknown_wish');
            if ($wish instanceof \App\Models\Wish && isset($wish->title)) {
                $wishTitle = $wish->title;
            }

            $wishListTitle = __('messages.unknown_wishlist');
            if ($wishList instanceof \App\Models\WishList && isset($wishList->title)) {
                $wishListTitle = $wishList->title;
            }

            $friendName = __('messages.unknown_sender');
            if ($friend instanceof User && isset($friend->name)) {
                $friendName = $friend->name;
            }

            return NotificationDisplayDTO::fromData(
                id: $notification->id,
                friendId: (int) ($friendId ?? 0),
                friendName: $friendName,
                wishId: (int) ($wishId ?? 0),
                wishTitle: $wishTitle,
                wishListId: $wishListId ? (int) $wishListId : null,
                wishListTitle: $wishListTitle,
                isRead: $isRead,
                updatedAt: $updatedAt instanceof \DateTimeInterface ? $updatedAt : null,
                createdAt: $createdAt instanceof \DateTimeInterface ? $createdAt : null
            );
        })->filter();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        $this->notificationRepository->markAsRead($notification);

        return true;
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }

    /**
     * Get user's unread notification by ID.
     */
    public function findUnread(User $user, int $notificationId): ?Notification
    {
        return $this->notificationRepository->findUnreadByIdForUser($user, $notificationId);
    }
}
