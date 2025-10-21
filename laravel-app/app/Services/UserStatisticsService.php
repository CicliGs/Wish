<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\FriendRequest;

class UserStatisticsService
{
    /**
     * Get user wish count.
     *
     * @return int Number of wishes created by user
     */
    public function getWishCountForUser(User $user): int
    {
        return $user->wishes()->count();
    }

    /**
     * Get user reservation count.
     *
     * @return int Number of reservations made by user
     */
    public function getReservationCountForUser(User $user): int
    {
        return $user->reservations()->count();
    }

    /**
     * Get accepted friends count.
     *
     * @return int Number of accepted friends for user
     */
    public function getAcceptedFriendsCountForUser(User $user): int
    {
        return FriendRequest::where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->count();
    }
}
