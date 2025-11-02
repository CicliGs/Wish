<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;

readonly class WishListDTO implements BaseDTO
{
    public function __construct(
        public array      $wishLists,
        public array      $stats = [],
        public int        $userId = 0,
        public ?WishList  $wishList = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'wishLists' => $this->wishLists,
            'stats' => $this->stats,
            'userId' => $this->userId,
        ];

        if ($this->wishList) {
            $data['wishList'] = $this->wishList;
        }

        return $data;
    }

    public static function fromArray(array $data): static
    {
        return new self(
            wishLists: $data['wishLists'],
            stats: $data['stats'] ?? [],
            userId: $data['userId'] ?? 0,
            wishList: $data['wishList'] ?? null,
        );
    }

    /**
     * Create DTO from wish lists array and user ID.
     */
    public static function fromWishLists(array $wishLists, int $userId, array $stats = []): static
    {
        return new self(
            wishLists: $wishLists,
            stats: $stats,
            userId: $userId
        );
    }
}
