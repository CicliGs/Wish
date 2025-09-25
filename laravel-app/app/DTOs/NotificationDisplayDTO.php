<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Notification;
use Illuminate\Support\Collection;

readonly class NotificationDisplayDTO implements BaseDTO
{
    public function __construct(
        public int $id,
        public string $message,
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
            'message' => $this->message,
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
            message: $data['message'],
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
        return new self(
            id: $notification->id,
            message: $notification->message,
            senderName: $notification->friend_name,
            senderId: $notification->friend_id,
            wishTitle: $notification->wish_title,
            wishListId: $notification->wish?->wish_list_id,
            wishListTitle: $notification->wish?->wishList?->title ?? 'Неизвестный список',
            readAt: $notification->is_read ? $notification->updated_at?->toISOString() : null,
            createdAt: $notification->created_at->toISOString()
        );
    }

    /**
     * Create a collection DTO
     */
    public static function fromNotificationCollection(\Illuminate\Database\Eloquent\Collection $notifications): \Illuminate\Support\Collection
    {
        return $notifications->map(fn(Notification $notification) => self::fromNotification($notification));
    }
}
