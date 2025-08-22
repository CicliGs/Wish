<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wish;

class WishPolicy
{
    /**
     * Determine whether the user can view any wishes.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the wish.
     */
    public function view(User $user, Wish $wish): bool
    {
        // User can view wishes from their own wish lists or public wish lists
        return $this->isOwner($user, $wish) || $wish->wishList->is_public;
    }

    /**
     * Determine whether the user can create wishes.
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the wish.
     */
    public function update(User $user, Wish $wish): bool
    {
        return $this->isOwner($user, $wish);
    }

    /**
     * Determine whether the user can delete the wish.
     */
    public function delete(User $user, Wish $wish): bool
    {
        return $this->isOwner($user, $wish);
    }

    /**
     * Determine whether the user can reserve the wish.
     */
    public function reserve(User $user, Wish $wish): bool
    {
        // Cannot reserve own wishes and wish must be available
        return !$this->isOwner($user, $wish) && $this->isAvailable($wish);
    }

    /**
     * Determine whether the user can unreserve the wish.
     */
    public function unreserve(User $user, Wish $wish): bool
    {
        return $this->isReservedByUser($user, $wish);
    }

    /**
     * Determine whether the user can restore the wish.
     */
    public function restore(User $user, Wish $wish): bool
    {
        return $this->isOwner($user, $wish);
    }

    /**
     * Determine whether the user can permanently delete the wish.
     */
    public function forceDelete(User $user, Wish $wish): bool
    {
        return $this->isOwner($user, $wish);
    }

    /**
     * Check if user is the owner of the wish (through wish list ownership).
     */
    private function isOwner(User $user, Wish $wish): bool
    {
        $userId = $user->id;
        $wishListUserId = $wish->wishList->user_id;

        return $userId === $wishListUserId;
    }

    /**
     * Check if wish is available for reservation.
     */
    private function isAvailable(Wish $wish): bool
    {
        return !$wish->is_reserved && $wish->reservation === null;
    }

    /**
     * Check if wish is reserved by the specific user.
     */
    private function isReservedByUser(User $user, Wish $wish): bool
    {
        if (!$wish->is_reserved || !$wish->reservation) {
            return false;
        }

        $userId = $user->id;
        $reservationUserId = $wish->reservation->user_id;

        return $userId === $reservationUserId;
    }
}
