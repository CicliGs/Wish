<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wish;
use Illuminate\Support\Facades\Auth;

class WishPolicy
{

    /**
     * Determine whether the user can view the wish.
     */
    public function view(User $user, Wish $wish): bool
    {
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
     * Check if user is the owner of the wish (through wish list ownership).
     */
    private function isOwner(User $user, Wish $wish): bool
    {
        return $user->id === $wish->wishList->user_id;
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

        return $user->id === $wish->reservation->user_id;
    }
}
