<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reservation;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function reserve(Wish $wish, int $userId): bool|string
    {
        if ($wish->is_reserved) {
            return 'Этот подарок уже забронирован!';
        }

        try {
            DB::beginTransaction();

            Reservation::create([
                'wish_id' => $wish->id,
                'user_id' => $userId,
            ]);

            $wish->update(['is_reserved' => true]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            //TODO прокинуть exception
            return 'Ошибка при бронировании: '.$e->getMessage();
        }

        return true;
    }

    public function unreserve(Wish $wish, int $userId): bool|string
    {
        $reservation = Reservation::where('wish_id', $wish->id)
            ->where('user_id', $userId)
            ->first();

        if (! $reservation) {
            return 'Вы не бронировали этот подарок!';
        }

        try {
            DB::beginTransaction();

            $reservation->delete();
            $wish->update(['is_reserved' => false]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            //TODO прокинуть exception
            return 'Ошибка при снятии брони: '.$e->getMessage();
        }
        return true;
    }

    public function getUserReservations(int $userId): Collection
    {
        return Reservation::where('user_id', $userId)
            ->with(['wish.wishList', 'wish.wishList.user'])
            ->get();
    }

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

    public function getUserReservationStatistics(int $userId): array
    {
        $reservations = Reservation::where('user_id', $userId)->with('wish');

        return [
            'total_reserved_wishes' => $reservations->count(),
            'active_reservations' => $reservations->whereHas('wish', function ($query) {
                $query->where('is_reserved', true);
            })->count(),
            'total_value' => $reservations->get()->sum('wish.price'),
        ];
    }

    public function getWishListReservationStatistics(int $wishListId): array
    {
        $reservations = Reservation::whereHas('wish', function ($query) use ($wishListId) {
            $query->where('wish_list_id', $wishListId);
        })->with('wish');

        return [
            'total_reservations' => $reservations->count(),
            'unique_users' => $reservations->distinct('user_id')->count(),
            'total_value' => $reservations->get()->sum('wish.price'),
        ];
    }
}
