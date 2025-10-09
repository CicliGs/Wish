<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservationService
{
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Reserve a wish.
     *
     * @throws RuntimeException
     */
    public function reserve(Wish $wish, User $user): void
    {
        if ($wish->is_reserved) {
            throw new RuntimeException(__('messages.wish_already_reserved'));
        }

        DB::transaction(function () use ($wish, $user) {
            $this->createReservationRecord($wish, $user);
            $this->markWishAsReserved($wish);
        });

        $this->clearWishCache($wish, $user);
    }

    /**
     * Unreserve a wish.
     *
     * @throws RuntimeException
     */
    public function unreserve(Wish $wish, User $user): void
    {
        $reservation = $this->findReservation($wish->id, $user->id);

        if (!$reservation) {
            throw new RuntimeException(__('messages.wish_not_reserved_by_user'));
        }

        DB::transaction(function () use ($reservation, $wish) {
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
        if ($entity instanceof User) {
            return Reservation::where('user_id', $entity->id)
                ->with(['wish.wishList', 'wish.wishList.user'])
                ->get();
        }

        return Reservation::whereHas('wish', function ($query) use ($entity) {
            $query->where('wish_list_id', $entity->id);
        })->with(['wish', 'user'])->get();
    }

    /**
     * Get reservation statistics for user or wish list.
     */
    public function getStatistics(User|WishList $entity): array
    {
        $reservations = $this->getReservations($entity);

        $stats = [
            'total_reservations' => $reservations->count(),
            'total_value' => $this->calculateTotalValue($reservations),
        ];

        if ($entity instanceof User) {
            $stats['total_reserved_wishes'] = $reservations->count();
        } else {
            $stats['reserved_wishes'] = $reservations->pluck('wish');
        }

        return $stats;
    }

    /**
     * Create reservation record in database.
     */
    private function createReservationRecord(Wish $wish, User $user): void
    {
        Reservation::create([
            'wish_id' => $wish->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Mark wish as reserved in database.
     */
    private function markWishAsReserved(Wish $wish): void
    {
        $wish->update(['is_reserved' => true]);
    }

    /**
     * Mark wish as available in database.
     */
    private function markWishAsAvailable(Wish $wish): void
    {
        $wish->update(['is_reserved' => false]);
    }

    /**
     * Calculate total value of reservations.
     */
    private function calculateTotalValue(Collection $reservations): float
    {
        return $reservations->sum(fn($reservation) => $reservation->wish->price ?? 0);
    }

    /**
     * Find reservation.
     */
    private function findReservation(int $wishId, int $userId): ?Reservation
    {
        return Reservation::where('wish_id', $wishId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Delete reservation record from database.
     */
    private function deleteReservationRecord(Reservation $reservation): void
    {
        $reservation->delete();
    }

    /**
     * Clear cache related to wish reservation.
     */
    private function clearWishCache(Wish $wish, User $user): void
    {
        $wishListUserId = $wish->wishList->user_id ?? 0;
        $this->cacheManager->clearReservationCache($wish->id, $user->id, $wishListUserId);
    }
}
