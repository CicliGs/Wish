<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class AchievementsReceiver
{
    private const GIFT_MASTER_THRESHOLD = 50;
    private const RESERVE_MASTER_THRESHOLD = 50;
    private const SOCIAL_BUTTERFLY_THRESHOLD = 10;
    private const VETERAN_MONTHS = 1;

    public function __construct(
        private readonly UserStatisticsService $userStatisticsService
    ) {}

    /**
     * Check if user has at least one wish.
     */
    public function checkGift(User $user): bool
    {
        return $this->userStatisticsService->getWishCountForUser($user) > 0;
    }

    /**
     * Check if user has at least one reservation.
     */
    public function checkReserve(User $user): bool
    {
        return $this->userStatisticsService->getReservationCountForUser($user) > 0;
    }

    /**
     * Check if user has at least one accepted friend.
     */
    public function checkFriend(User $user): bool
    {
        return $this->userStatisticsService->getAcceptedFriendsCountForUser($user) > 0;
    }

    /**
     * Check if user has 50+ wishes.
     */
    public function checkGiftMaster(User $user): bool
    {
        return $this->userStatisticsService->getWishCountForUser($user) >= self::GIFT_MASTER_THRESHOLD;
    }

    /**
     * Check if user has 50+ reservations.
     */
    public function checkReserveMaster(User $user): bool
    {
        return $this->userStatisticsService->getReservationCountForUser($user) >= self::RESERVE_MASTER_THRESHOLD;
    }

    /**
     * Check if user has 10+ accepted friends.
     */
    public function checkSocialButterfly(User $user): bool
    {
        return $this->userStatisticsService->getAcceptedFriendsCountForUser($user) >= self::SOCIAL_BUTTERFLY_THRESHOLD;
    }

    /**
     * Check if user has been registered for at least one month.
     */
    public function checkVeteran(User $user): bool
    {
        $veteranDate = now()->subMonths(self::VETERAN_MONTHS);
        return $user->created_at->lt($veteranDate);
    }
}
