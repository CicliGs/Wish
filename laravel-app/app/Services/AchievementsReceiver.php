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
    public function checkFirstGiftAchievement(User $user): bool
    {
        return $this->getWishCountForUser($user) > 0;
    }

    /**
     * Compatibility: config('achievements') expects method name 'checkGift'.
     */
    public function checkGift(User $user): bool
    {
        return $this->checkFirstGiftAchievement($user);
    }

    /**
     * First reservation (has at least one reservation).
     */
    public function checkFirstReservationAchievement(User $user): bool
    {
        return $user->reservations()->count() > 0;
    }

    /**
     * Compatibility: expected checker method 'checkReserve'.
     */
    public function checkReserve(User $user): bool
    {
        return $this->checkFirstReservationAchievement($user);
    }

    /**
     * First friend (has at least one friend).
     */
    public function checkFirstFriendAchievement(User $user): bool
    {
        return $this->userHasAcceptedFriends($user);
    }

    /**
     * Compatibility: expected checker method 'checkFriend'.
     */
    public function checkFriend(User $user): bool
    {
        return $this->checkFirstFriendAchievement($user);
    }

    /**
     * Gift master (50+ added gifts).
     */
    public function checkGiftMasterAchievement(User $user): bool
    {
        return $this->getWishCountForUser($user) >= self::GIFT_MASTER_THRESHOLD;
    }

    /**
     * Compatibility: expected checker method 'checkGiftMaster'.
     */
    public function checkGiftMaster(User $user): bool
    {
        return $this->checkGiftMasterAchievement($user);
    }

    /**
     * Reservation master (50+ reserved gifts).
     */
    public function checkReservationMasterAchievement(User $user): bool
    {
        return $user->reservations()->count() >= self::RESERVE_MASTER_THRESHOLD;
    }

    /**
     * Compatibility: expected checker method 'checkReserveMaster'.
     */
    public function checkReserveMaster(User $user): bool
    {
        return $this->checkReservationMasterAchievement($user);
    }

    /**
     * Social butterfly (10+ friends).
     */
    public function checkSocialButterflyAchievement(User $user): bool
    {
        return $this->getAcceptedFriendsCountForUser($user) >= self::SOCIAL_BUTTERFLY_THRESHOLD;
    }

    /**
     * Compatibility: expected checker method 'checkSocialButterfly'.
     */
    public function checkSocialButterfly(User $user): bool
    {
        return $this->checkSocialButterflyAchievement($user);
    }

    /**
     * Site veteran (one month of site registration).
     */
    public function checkSiteVeteranAchievement(User $user): bool
    {
        $veteranDate = now()->subMonths(self::VETERAN_MONTHS);

        return $user->created_at->lt($veteranDate);
    }

    /**
     * Compatibility: expected checker method 'checkVeteran'.
     */
    public function checkVeteran(User $user): bool
    {
        return $this->checkSiteVeteranAchievement($user);
    }

    /**
     * Get user wish count.
     *
     * @return int Number of wishes created by user
     */
    private function getWishCountForUser(User $user): int
    {
        $wishListIds = $user->wishLists()->pluck('id')->toArray();

        return Wish::whereIn('wish_list_id', $wishListIds)->count();
    }

    /**
     * Check if user has accepted friends.
     *
     * @return bool True if user has at least one accepted friend
     */
    private function userHasAcceptedFriends(User $user): bool
    {
        return DB::table('friend_requests')
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->exists();
    }

    /**
     * Get accepted friends count.
     *
     * @return int Number of accepted friends for user
     */
    private function getAcceptedFriendsCountForUser(User $user): int
    {
        return DB::table('friend_requests')
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->count();
    }
}
