<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Notification;
use Illuminate\Support\Collection;

readonly class NotificationDisplayDTO implements BaseDTO
{
    public function __construct(
        public int $id,
        public string $senderName,
        public int $senderId,
        public string $wishTitle,
        public ?int $wishListId,
        public ?string $wishListTitle,
        public ?string $readAt,
        public string $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender_name' => $this->senderName,
            'sender_id' => $this->senderId,
            'wish_title' => $this->wishTitle,
            'wish_list_id' => $this->wishListId,
            'wish_list_title' => $this->wishListTitle,
            'read_at' => $this->readAt,
            'created_at' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            senderName: $data['sender_name'],
            senderId: $data['sender_id'],
            wishTitle: $data['wish_title'],
            wishListId: $data['wish_list_id'] ?? null,
            wishListTitle: $data['wish_list_title'] ?? null,
            readAt: $data['read_at'] ?? null,
            createdAt: $data['created_at']
        );
    }

    /**
     * Create a collection DTO from model notification
     */
    public static function fromNotification(Notification $notification): static
    {
        $friendId = $notification->getAttribute('friend_id');
        $isRead = $notification->getAttribute('is_read');
        $updatedAt = $notification->getAttribute('updated_at');
        $createdAt = $notification->getAttribute('created_at');

        return new self(
            id: $notification->id,
            senderName: $notification->friend->name ?? __('messages.unknown_sender'),
            senderId: $friendId ?? 0,
            wishTitle: $notification->wish->title ?? __('messages.unknown_wish'),
            wishListId: $notification->wish?->wish_list_id,
            wishListTitle: $notification->wish->wishList->title ?? __('messages.unknown_wishlist'),
            readAt: $isRead && $updatedAt ? $updatedAt->format('c') : null,
            createdAt: $createdAt ? $createdAt->format('c') : now()->format('c')
        );
    }

    /**
     * Create a collection DTO
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, Notification> $notifications
     *
     * @return Collection<int, static>
     */
    public static function fromNotificationCollection(\Illuminate\Database\Eloquent\Collection $notifications): Collection
    {
        return $notifications->map(fn(Notification $notification) => self::fromNotification($notification));
    }
}
