<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;

readonly class WishDTO implements BaseDTO
{
    public function __construct(
        public WishList   $wishList,
        public Collection $wishes,
        public array      $stats = [],
        public int        $userId = 0,
        public ?Wish      $wish = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'wishList' => $this->wishList,
            'wishes' => $this->wishes,
            'stats' => $this->stats,
            'userId' => $this->userId,
        ];

        if ($this->wish) {
            $data['wish'] = $this->wish;
        }

        return $data;
    }

    public static function fromArray(array $data): static
    {
        return new self(
            wishList: $data['wishList'],
            wishes: $data['wishes'],
            stats: $data['stats'] ?? [],
            userId: $data['userId'] ?? 0,
            wish: $data['wish'] ?? null,
        );
    }

    /**
     * Create DTO from wish list data.
     */
    public static function fromWishListData(WishList $wishList, Collection $wishes, int $userId, array $stats = []): static
    {
        return new self(
            wishList: $wishList,
            wishes: $wishes,
            stats: $stats,
            userId: $userId
        );
    }
}
