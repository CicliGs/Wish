<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wish;

class WishPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Wish $wish): bool
    {
        return $user->ownsWishList($wish->wishList);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Wish $wish): bool
    {
        return $user->ownsWishList($wish->wishList);
    }

    public function delete(User $user, Wish $wish): bool
    {
        return $user->ownsWishList($wish->wishList);
    }

    public function reserve(User $user, Wish $wish): bool
    {
        return ! $user->ownsWishList($wish->wishList) && $wish->isAvailable();
    }

    public function unreserve(User $user, Wish $wish): bool
    {
        // Проверяем, что желание забронировано
        if (!$wish->is_reserved) {
            return false;
        }
        
        // Проверяем, что у желания есть резервация
        if (!$wish->reservation) {
            return false;
        }
        
        // Проверяем, что текущий пользователь забронировал это желание
        return $wish->reservation->user_id === $user->id;
    }

    public function restore(User $user, Wish $wish): bool
    {
        return $user->ownsWishList($wish->wishList);
    }

    public function forceDelete(User $user, Wish $wish): bool
    {
        return $user->ownsWishList($wish->wishList);
    }
}
