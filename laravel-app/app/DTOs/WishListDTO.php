<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;

readonly class WishListDTO implements BaseDTO
{
    public function __construct(
        public Collection $wishLists,
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
}
