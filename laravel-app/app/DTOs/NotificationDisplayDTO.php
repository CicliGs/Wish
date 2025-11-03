<?php

declare(strict_types=1);

namespace App\DTOs;

use DateTimeInterface;

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
     * Create DTO from data loaded through repositories.
     */
    public static function fromData(
        int                $id,
        int                $friendId,
        string             $friendName,
        int                $wishId,
        string             $wishTitle,
        ?int               $wishListId,
        string             $wishListTitle,
        bool               $isRead,
        ?DateTimeInterface $updatedAt,
        ?DateTimeInterface $createdAt
    ): static {
        return new self(
            id: $id,
            senderName: $friendName,
            senderId: $friendId,
            wishTitle: $wishTitle,
            wishListId: $wishListId,
            wishListTitle: $wishListTitle,
            readAt: $isRead && $updatedAt ? $updatedAt->format('c') : null,
            createdAt: $createdAt ? $createdAt->format('c') : now()->format('c')
        );
    }

}
