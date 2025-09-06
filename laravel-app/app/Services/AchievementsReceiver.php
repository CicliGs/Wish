<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wish;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\DB;

class AchievementsReceiver
{
    private const GIFT_MASTER_THRESHOLD = 50;
    private const RESERVE_MASTER_THRESHOLD = 50;
    private const SOCIAL_BUTTERFLY_THRESHOLD = 10;
    private const VETERAN_MONTHS = 1;

    /**
     * First gift (has at least one wish).
     */
    public function checkGift(User $user): bool
    {
        return $this->getUserWishCount($user) > 0;
    }

    /**
     * First reservation (has at least one reservation).
     */
    public function checkReserve(User $user): bool
    {
        return $user->reservations()->count() > 0;
    }

    /**
     * First friend (has at least one friend).
     */
    public function checkFriend(User $user): bool
    {
        return $this->hasAcceptedFriends($user);
    }

    /**
     * Gift master (50+ added gifts).
     */
    public function checkGiftMaster(User $user): bool
    {
        return $this->getUserWishCount($user) >= self::GIFT_MASTER_THRESHOLD;
    }

    /**
     * Reservation master (50+ reserved gifts).
     */
    public function checkReserveMaster(User $user): bool
    {
        return $user->reservations()->count() >= self::RESERVE_MASTER_THRESHOLD;
    }

    /**
     * Social butterfly (10+ friends).
     */
    public function checkSocialButterfly(User $user): bool
    {
        return $this->getAcceptedFriendsCount($user) >= self::SOCIAL_BUTTERFLY_THRESHOLD;
    }

    /**
     * Site veteran (one month of site registration).
     */
    public function checkVeteran(User $user): bool
    {
        $veteranDate = now()->subMonths(self::VETERAN_MONTHS);

        return $user->created_at->lt($veteranDate);
    }

    /**
     * Get user wish count.
     */
    private function getUserWishCount(User $user): int
    {
        $wishListIds = $user->wishLists()->pluck('id')->toArray();

        return Wish::whereIn('wish_list_id', $wishListIds)->count();
    }

    /**
     * Check if user has accepted friends.
     */
    private function hasAcceptedFriends(User $user): bool
    {
        return DB::table('friends')
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('friend_id', $user->id);
            })
            ->exists();
    }

    /**
     * Get accepted friends count.
     */
    private function getAcceptedFriendsCount(User $user): int
    {
        return DB::table('friends')
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('friend_id', $user->id);
            })
            ->count();
    }
}
