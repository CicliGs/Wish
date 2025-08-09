<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PublicWishListDTO extends BaseDTO
{
    public function __construct(
        public readonly WishList $wishList,
        public readonly User $user,
        public readonly Collection $wishes,
        public readonly bool $isGuest,
        public readonly bool $isFriend,
        public readonly bool $isOwner
    ) {}

    public function toArray(): array
    {
        return [
            'wishList' => $this->wishList,
            'user' => $this->user,
            'wishes' => $this->wishes,
            'isGuest' => $this->isGuest,
            'isFriend' => $this->isFriend,
            'isOwner' => $this->isOwner,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            wishList: $data['wishList'],
            user: $data['user'],
            wishes: $data['wishes'],
            isGuest: $data['isGuest'],
            isFriend: $data['isFriend'],
            isOwner: $data['isOwner'],
        );
    }
} 