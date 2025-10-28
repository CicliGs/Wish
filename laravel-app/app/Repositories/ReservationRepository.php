<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishList;
use App\DTOs\ReservationStatisticsDTO;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reservation repository implementation
 */
class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(Reservation $model)
    {
        parent::__construct($model);
    }

    /**
     * Find reservation by wish and user
     */
    public function findByWishAndUser(Wish $wish, User $user): ?Reservation
    {
        return $this->model->where('wish_id', $wish->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Find reservations by user
     */
    public function findByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->with(['wish.wishList', 'wish.wishList.user'])
            ->get();
    }

    /**
     * Find reservations by wish list
     */
    public function findByWishList(WishList $wishList): Collection
    {
        return $this->model->whereHas('wish', function ($query) use ($wishList) {
            $query->where('wish_list_id', $wishList->id);
        })->with(['wish', 'user'])->get();
    }

    /**
     * Find reservations by wish
     */
    public function findByWish(Wish $wish): Collection
    {
        return $this->model->where('wish_id', $wish->id)
            ->with('user')
            ->get();
    }

    /**
     * Find reservations with related data
     */
    public function findWithRelations(User|WishList $entity): Collection
    {
        if ($entity instanceof User) {
            return $this->findByUser($entity);
        }

        return $this->findByWishList($entity);
    }

    /**
     * Get reservation statistics
     */
    public function getStatistics(User|WishList $entity): ReservationStatisticsDTO
    {
        if ($entity instanceof User) {
            $reservations = $this->findByUser($entity);
            return new ReservationStatisticsDTO(
                totalReservations: $reservations->count(),
                totalValue: $reservations->sum(fn($reservation) => $reservation->wish->price ?? 0),
                averagePrice: $reservations->avg(fn($reservation) => $reservation->wish->price ?? 0) ?? 0.0,
            );
        }

        $reservations = $this->findByWishList($entity);
        return new ReservationStatisticsDTO(
            totalReservations: $reservations->count(),
            totalValue: $reservations->sum(fn($reservation) => $reservation->wish->price ?? 0),
            averagePrice: $reservations->avg(fn($reservation) => $reservation->wish->price ?? 0) ?? 0.0,
            uniqueUsers: $reservations->pluck('user_id')->unique()->count(),
        );
    }

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(Wish $wish, User $user): bool
    {
        return $this->model->where('wish_id', $wish->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Count reservations for user
     */
    public function countByUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)->count();
    }

    /**
     * Count reservations for wish list
     */
    public function countByWishList(WishList $wishList): int
    {
        return $this->model->whereHas('wish', function ($query) use ($wishList) {
            $query->where('wish_list_id', $wishList->id);
        })->count();
    }
}
