<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;

class UserWishesDTO extends BaseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly Collection $wishLists,
        public readonly ?WishList $selectedWishList = null,
        public readonly ?Collection $wishes = null
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
        return new static(
            user: $data['user'],
            wishLists: $data['wishLists'],
            selectedWishList: $data['selectedWishList'] ?? null,
            wishes: $data['wishes'] ?? null,
        );
    }
} 