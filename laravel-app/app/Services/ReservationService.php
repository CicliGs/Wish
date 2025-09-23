<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReservationService
{
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Reserve a wish for a user.
     */
    public function reserveWishForUser(Wish $wish, int $userId): bool|string
    {
        Log::info('ReservationService: Attempting to reserve wish', ['wish_id' => $wish->id, 'user_id' => $userId]);
        
        if ($wish->is_reserved) {
            Log::warning('ReservationService: Wish already reserved', ['wish_id' => $wish->id, 'user_id' => $userId]);
            return __('messages.wish_already_reserved');
        }

        try {
            DB::transaction(function () use ($wish, $userId) {
                $this->createReservationRecord($wish, $userId);
                $this->markWishAsReserved($wish);
            });

            $this->cacheManager->clearReservationCache($wish->id, $userId, $wish->wishList->user_id);

            Log::info('ReservationService: Wish reserved successfully', ['wish_id' => $wish->id, 'user_id' => $userId]);
        } catch (Exception $e) {
            Log::error('ReservationService: Failed to reserve wish', ['wish_id' => $wish->id, 'user_id' => $userId, 'error' => $e->getMessage()]);
            return __('messages.error_reserving_wish') . $e->getMessage();
        }

        return true;
    }

    /**
     * Unreserve a wish for a user.
     */
    public function unreserveWishForUser(Wish $wish, int $userId): bool|string
    {
        Log::info('ReservationService: Attempting to unreserve wish', ['wish_id' => $wish->id, 'user_id' => $userId]);
        
        $reservation = $this->findReservationByUserAndWish($wish->id, $userId);

        if (!$reservation) {
            Log::warning('ReservationService: Reservation not found', ['wish_id' => $wish->id, 'user_id' => $userId]);
            return __('messages.wish_not_reserved_by_user');
        }

        try {
            DB::transaction(function () use ($reservation, $wish) {
                $this->deleteReservationRecord($reservation);
                $this->markWishAsAvailable($wish);
            });

            $this->cacheManager->clearReservationCache($wish->id, $userId, $wish->wishList->user_id);

            Log::info('ReservationService: Wish unreserved successfully', ['wish_id' => $wish->id, 'user_id' => $userId]);
        } catch (Exception $e) {
            Log::error('ReservationService: Failed to unreserve wish', ['wish_id' => $wish->id, 'user_id' => $userId, 'error' => $e->getMessage()]);
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
            'total_reserved_wishes' => $reservations->count(),
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
     * Create reservation record in database.
     */
    private function createReservationRecord(Wish $wish, int $userId): void
    {
        Reservation::create([
            'wish_id' => $wish->id,
            'user_id' => $userId,
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
     * Find reservation by user and wish.
     */
    private function findReservationByUserAndWish(int $wishId, int $userId): ?Reservation
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
}
