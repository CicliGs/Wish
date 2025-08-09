<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;

class WishListDTO extends BaseDTO
{
    public function __construct(
        public readonly Collection $wishLists,
        public readonly array $stats = [],
        public readonly int $userId = 0,
        public readonly ?WishList $wishList = null
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
        return new static(
            wishLists: $data['wishLists'],
            stats: $data['stats'] ?? [],
            userId: $data['userId'] ?? 0,
            wishList: $data['wishList'] ?? null,
        );
    }
} 