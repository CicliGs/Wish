<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class NotificationDTO implements BaseDTO
{
    public function __construct(
        public int    $userId,
        public int    $friendId,
        public int    $wishId,
        public string $friendName,
        public string $wishTitle,
        public string $message,
        public bool   $isRead = false
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'friend_id' => $this->friendId,
            'wish_id' => $this->wishId,
            'friend_name' => $this->friendName,
            'wish_title' => $this->wishTitle,
            'message' => $this->message,
            'is_read' => $this->isRead,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            userId: $data['user_id'],
            friendId: $data['friend_id'],
            wishId: $data['wish_id'],
            friendName: $data['friend_name'],
            wishTitle: $data['wish_title'],
            message: $data['message'],
            isRead: $data['is_read'] ?? false,
        );
    }

    /**
     * Create a DTO for notification about new wish friend
     */
    public static function forNewWish(
        int $userId,
        int $friendId,
        int $wishId,
        string $friendName,
        string $wishTitle
    ): static {
        $message = __('messages.friend_added_new_wish', [
            'friendName' => $friendName,
            'wishTitle' => $wishTitle
        ]);

        return new self(
            userId: $userId,
            friendId: $friendId,
            wishId: $wishId,
            friendName: $friendName,
            wishTitle: $wishTitle,
            message: $message,
            isRead: false
        );
    }
}
