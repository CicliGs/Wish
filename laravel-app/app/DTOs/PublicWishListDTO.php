<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\WishList;
use App\Models\User;

readonly class PublicWishListDTO implements BaseDTO
{
    public function __construct(
        public WishList   $wishList,
        public User       $user,
        public array      $wishes,
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

    /**
     * Create DTO from WishList with authentication context.
     * 
     * @deprecated Use fromData() instead. This method uses direct model relationships.
     */
    public static function fromWishList(WishList $wishList, ?User $currentUser = null): static
    {
        $isGuest = $currentUser === null;
        $isFriend = false;
        $isOwner = $currentUser && $currentUser->id === $wishList->user_id;
        
        $wishes = $wishList->wishes;
        $wishesArray = $wishes->all();
        
        return new self(
            wishList: $wishList,
            user: $wishList->user,
            wishes: $wishesArray,
            isGuest: $isGuest,
            isFriend: $isFriend,
            isOwner: $isOwner
        );
    }

    /**
     * Create DTO from data loaded through repositories.
     */
    public static function fromData(
        WishList $wishList,
        User $user,
        array $wishes,
        bool $isGuest,
        bool $isFriend,
        bool $isOwner
    ): static {
        return new self(
            wishList: $wishList,
            user: $user,
            wishes: $wishes,
            isGuest: $isGuest,
            isFriend: $isFriend,
            isOwner: $isOwner
        );
    }
}
