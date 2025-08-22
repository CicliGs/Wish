<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class PublicWishListDTO implements BaseDTO
{
    public function __construct(
        public WishList   $wishList,
        public User       $user,
        public Collection $wishes,
        public bool       $isGuest,
        public bool       $isFriend,
        public bool       $isOwner
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
        return new self(
            wishList: $data['wishList'],
            user: $data['user'],
            wishes: $data['wishes'],
            isGuest: $data['isGuest'],
            isFriend: $data['isFriend'],
            isOwner: $data['isOwner'],
        );
    }
}
