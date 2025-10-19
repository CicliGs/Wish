<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class WishListStatisticsDTO implements BaseDTO
{
    public function __construct(
        public int $totalWishLists,
        public int $totalWishes,
        public int $totalReservedWishes,
        public int $publicWishLists
    ) {}

    public function toArray(): array
    {
        return [
            'total_wish_lists' => $this->totalWishLists,
            'total_wishes' => $this->totalWishes,
            'total_reserved_wishes' => $this->totalReservedWishes,
            'public_wish_lists' => $this->publicWishLists,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            totalWishLists: $data['total_wish_lists'] ?? 0,
            totalWishes: $data['total_wishes'] ?? 0,
            totalReservedWishes: $data['total_reserved_wishes'] ?? 0,
            publicWishLists: $data['public_wish_lists'] ?? 0,
        );
    }

    /**
     * Create DTO from wish list statistics array.
     */
    public static function fromWishListStats(array $stats): static
    {
        return new self(
            totalWishLists: $stats['total_wish_lists'] ?? 0,
            totalWishes: $stats['total_wishes'] ?? 0,
            totalReservedWishes: $stats['total_reserved_wishes'] ?? 0,
            publicWishLists: $stats['public_wish_lists'] ?? 0,
        );
    }
}
