<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;

readonly class UserWishesDTO implements BaseDTO
{
    public function __construct(
        public User        $user,
        public Collection  $wishLists,
        public ?WishList   $selectedWishList = null,
        public ?Collection $wishes = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'user' => $this->user,
            'wishLists' => $this->wishLists,
        ];

        if ($this->selectedWishList) {
            $data['selectedWishList'] = $this->selectedWishList;
        }

        if ($this->wishes) {
            $data['wishes'] = $this->wishes;
        }

        return $data;
    }

    public static function fromArray(array $data): static
    {
        return new self(
            user: $data['user'],
            wishLists: $data['wishLists'],
            selectedWishList: $data['selectedWishList'] ?? null,
            wishes: $data['wishes'] ?? null,
        );
    }

    /**
     * Create DTO from user with wish lists only (for wish lists selection page).
     */
    public static function fromUserWishLists(User $user, Collection $wishLists): static
    {
        return new self(
            user: $user,
            wishLists: $wishLists
        );
    }

    /**
     * Create DTO from user with specific wish list selected (for wish list details page).
     */
    public static function fromUserWithSelectedWishList(User $user, Collection $wishLists, Collection $wishes, WishList $selectedWishList): static
    {
        return new self(
            user: $user,
            wishLists: $wishLists,
            wishes: $wishes,
            selectedWishList: $selectedWishList
        );
    }
}
