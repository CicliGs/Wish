<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishList;
use App\Exceptions\WishAlreadyReservedException;
use App\Exceptions\WishNotReservedByUserException;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\ConnectionInterface;

class ReservationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        protected ReservationRepositoryInterface $reservationRepository,
        protected WishRepositoryInterface $wishRepository,
        protected WishListRepositoryInterface $wishListRepository,
        private readonly ConnectionInterface $db
    ) {}

    /**
     * Reserve a wish.
     *
     * @throws WishAlreadyReservedException
     */
    public function reserve(Wish $wish, User $user): void
    {
        if ($this->wishRepository->isReserved($wish)) {
            throw new WishAlreadyReservedException();
        }

        $this->db->transaction(function () use ($wish, $user) {
            $this->createReservationRecord($wish, $user);
            $this->markWishAsReserved($wish);
        });

        $this->clearWishCache($wish, $user);
    }

    /**
     * Unreserve a wish.
     *
     * @throws WishNotReservedByUserException
     */
    public function unreserve(Wish $wish, User $user): void
    {
        $reservation = $this->reservationRepository->findByWishAndUser($wish, $user);

        if (!$reservation || !($reservation instanceof Reservation)) {
            throw new WishNotReservedByUserException();
        }

        $this->db->transaction(function () use ($reservation, $wish) {
            $this->deleteReservationRecord($reservation);
            $this->markWishAsAvailable($wish);
        });

        $this->clearWishCache($wish, $user);
    }

    /**
     * Get reservations for user or wish list.
     */
    public function getReservations(User|WishList $entity): Collection
    {
        return collect($this->reservationRepository->findWithRelations($entity));
    }

    /**
     * Get reservation statistics for user or wish list.
     */
    public function getStatistics(User|WishList $entity): array
    {
        return $this->reservationRepository->getStatistics($entity)->toArray();
    }

    /**
     * Create reservation record in database.
     */
    private function createReservationRecord(Wish $wish, User $user): void
    {
        $this->reservationRepository->create([
            'wish_id' => $wish->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Mark wish as reserved in database.
     */
    private function markWishAsReserved(Wish $wish): void
    {
        $this->wishRepository->update($wish, ['is_reserved' => true]);
    }

    /**
     * Mark wish as available in database.
     */
    private function markWishAsAvailable(Wish $wish): void
    {
        $this->wishRepository->update($wish, ['is_reserved' => false]);
    }

    /**
     * Delete reservation record from database.
     */
    private function deleteReservationRecord(Reservation $reservation): void
    {
        $this->reservationRepository->delete($reservation);
    }

    /**
     * Clear cache related to wish reservation.
     */
    private function clearWishCache(Wish $wish, User $user): void
    {
        $wishList = $this->wishListRepository->findById($wish->wish_list_id);
        $wishListUserId = ($wishList instanceof WishList && isset($wishList->user_id)) ? $wishList->user_id : 0;
        $this->cacheManager->clearReservationCache($wish->id, $user->id, $wishListUserId);
    }
}
