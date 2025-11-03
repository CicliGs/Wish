<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use App\Models\WishList;

readonly class UserWishesDTO implements BaseDTO
{
    public function __construct(
        public User       $user,
        public array      $wishLists,
        public ?WishList  $selectedWishList = null,
        public array      $wishes = []
    ) {}

    public function toArray(): array
    {
        $data = [
            'user' => $this->user,
            'wishLists' => $this->wishLists,
            'wishes' => $this->wishes,
        ];

        if ($this->selectedWishList) {
            $data['selectedWishList'] = $this->selectedWishList;
        }

        return $data;
    }

    public static function fromArray(array $data): static
    {
        return new self(
            user: $data['user'],
            wishLists: $data['wishLists'],
            selectedWishList: $data['selectedWishList'] ?? null,
            wishes: $data['wishes'] ?? [],
        );
    }

    /**
     * Create DTO from user with wish lists only (for wish lists selection page).
     */
    public static function fromUserWishLists(User $user, array $wishLists): static
    {
        return new self(
            user: $user,
            wishLists: $wishLists,
            wishes: []
        );
    }

    /**
     * Create DTO from user with specific wish list selected (for wish list details page).
     */
    public static function fromUserWithSelectedWishList(User $user, array $wishLists, array $wishes, WishList $selectedWishList): static
    {
        return new self(
            user: $user,
            wishLists: $wishLists,
            selectedWishList: $selectedWishList,
            wishes: $wishes
        );
    }
}
