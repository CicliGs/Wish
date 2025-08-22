<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WishList;

class WishListPolicy
{
    /**
     * Determine whether the user can view any wish lists.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the wish list.
     */
    public function view(User $user, WishList $wishList): bool
    {
        return $this->isOwner($user, $wishList) || $wishList->is_public;
    }

    /**
     * Determine whether the user can create wish lists.
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the wish list.
     */
    public function update(User $user, WishList $wishList): bool
    {
        return $this->isOwner($user, $wishList);
    }

    /**
     * Determine whether the user can delete the wish list.
     */
    public function delete(User $user, WishList $wishList): bool
    {
        return $this->isOwner($user, $wishList);
    }

    /**
     * Determine whether the user can restore the wish list.
     */
    public function restore(User $user, WishList $wishList): bool
    {
        return $this->isOwner($user, $wishList);
    }

    /**
     * Determine whether the user can permanently delete the wish list.
     */
    public function forceDelete(User $user, WishList $wishList): bool
    {
        return $this->isOwner($user, $wishList);
    }

    /**
     * Check if user is the owner of the wish list.
     */
    private function isOwner(User $user, WishList $wishList): bool
    {
        $userId = $user->id;
        $wishListUserId = $wishList->user_id;

        return $userId === $wishListUserId;
    }
}
