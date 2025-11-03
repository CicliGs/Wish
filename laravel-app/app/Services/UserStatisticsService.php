<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\FriendRequestRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;

readonly class UserStatisticsService
{
    public function __construct(
        private FriendRequestRepositoryInterface $friendRequestRepository,
        private WishRepositoryInterface          $wishRepository,
        private ReservationRepositoryInterface   $reservationRepository
    ) {}

    /**
     * Get user wish count.
     *
     * @return int Number of wishes created by user
     */
    public function getWishCountForUser(User $user): int
    {
        return $this->wishRepository->countByUserId($user->id);
    }

    /**
     * Get user reservation count.
     *
     * @return int Number of reservations made by user
     */
    public function getReservationCountForUser(User $user): int
    {
        return $this->reservationRepository->countByUser($user);
    }

    /**
     * Get accepted friends count.
     *
     * @return int Number of accepted friends for user
     */
    public function getAcceptedFriendsCountForUser(User $user): int
    {
        return $this->friendRequestRepository->countAcceptedForUser($user);
    }
}
