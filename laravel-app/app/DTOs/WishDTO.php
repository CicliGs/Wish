<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;

class WishDTO extends BaseDTO
{
    public function __construct(
        public readonly WishList $wishList,
        public readonly Collection $wishes,
        public readonly array $stats = [],
        public readonly int $userId = 0,
        public readonly ?Wish $wish = null
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
        return new static(
            wishList: $data['wishList'],
            wishes: $data['wishes'],
            stats: $data['stats'] ?? [],
            userId: $data['userId'] ?? 0,
            wish: $data['wish'] ?? null,
        );
    }
} 