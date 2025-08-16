<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ReservationService
{
    /**
     * Reserve a wish for a user.
     */
    public function reserve(Wish $wish, int $userId): bool|string
    {
        if ($wish->is_reserved) {
            return __('messages.wish_already_reserved');
        }

        try {
            DB::transaction(function () use ($wish, $userId) {
                $this->createReservation($wish, $userId);
                $this->updateWishReservationStatus($wish, true);
            });

        } catch (Exception $e) {
            return __('messages.error_reserving_wish') . $e->getMessage();
        }

            return true;
    }

    /**
     * Unreserve a wish for a user.
     */
    public function unreserve(Wish $wish, int $userId): bool|string
    {
        $reservation = $this->findUserReservation($wish->id, $userId);

        if (!$reservation) {
            return __('messages.wish_not_reserved_by_user');
        }

        try {
            DB::transaction(function () use ($reservation, $wish) {
                $this->deleteReservation($reservation);
                $this->updateWishReservationStatus($wish, false);
            });

        } catch (Exception $e) {
            return __('messages.error_unreserving_wish') . $e->getMessage();
        }

            return true;
    }

    /**
     * Get user reservations.
     */
    public function getUserReservations(int $userId): Collection
    {
        return Reservation::where('user_id', $userId)
            ->with(['wish.wishList', 'wish.wishList.user'])
            ->get();
    }

    /**
     * Get wish list reservations.
     */
    public function getWishListReservations(int $wishListId): Collection
    {
        return Reservation::whereHas('wish', function ($query) use ($wishListId) {
            $query->where('wish_list_id', $wishListId);
        })->with(['wish', 'user'])->get();
    }

    /**
     * Check if a user has reserved a specific wish.
     */
    public function hasUserReservedWish(int $userId, int $wishId): bool
    {
        return Reservation::where('user_id', $userId)
            ->where('wish_id', $wishId)
            ->exists();
    }

    /**
     * Get user reservation statistics.
     */
    public function getUserReservationStatistics(int $userId): array
    {
        $reservations = $this->getUserReservations($userId);

        return [
            'total_reservations' => $reservations->count(),
            'total_value' => $reservations->sum(function ($reservation) {
                return $reservation->wish->price ?? 0;
            }),
            'total_reserved_wishes' => $reservations->count(), // Added for compatibility
        ];
    }

    /**
     * Get wish list reservation statistics.
     */
    public function getWishListReservationStatistics(int $wishListId): array
    {
        $reservations = $this->getWishListReservations($wishListId);

        return [
            'total_reservations' => $reservations->count(),
            'total_value' => $reservations->sum(function ($reservation) {
                return $reservation->wish->price ?? 0;
            }),
            'reserved_wishes' => $reservations->pluck('wish'),
        ];
    }

    /**
     * Create reservation.
     */
    private function createReservation(Wish $wish, int $userId): void
    {
        Reservation::create([
            'wish_id' => $wish->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Update wish reservation status.
     */
    private function updateWishReservationStatus(Wish $wish, bool $isReserved): void
    {
        $wish->update(['is_reserved' => $isReserved]);
    }

    /**
     * Find user reservation.
     */
    private function findUserReservation(int $wishId, int $userId): ?Reservation
    {
        return Reservation::where('wish_id', $wishId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Delete reservation.
     */
    private function deleteReservation(Reservation $reservation): void
    {
        $reservation->delete();
    }
}
