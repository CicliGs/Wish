<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishStatisticsDTO;
use App\Repositories\Contracts\WishRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Wish repository implementation
 */
class WishRepository extends BaseRepository implements WishRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(Wish $model)
    {
        parent::__construct($model);
    }

    /**
     * Find wishes by wish list
     */
    public function findByWishList(WishList $wishList): Collection
    {
        return $this->findByWishListId($wishList->id);
    }

    /**
     * Find wishes by wish list ID
     */
    public function findByWishListId(int $wishListId): Collection
    {
        return $this->model->forWishList($wishListId)->with('reservation.user')->get();
    }

    /**
     * Find reserved wishes by user
     */
    public function findReservedByUser(User $user): Collection
    {
        return $this->findReservedByUserId($user->id);
    }

    /**
     * Find reserved wishes by user ID
     */
    public function findReservedByUserId(int $userId): Collection
    {
        return $this->model->whereHas('reservation', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['wishList', 'wishList.user'])->get();
    }

    /**
     * Find available wishes in wish list
     */
    public function findAvailableInWishList(WishList $wishList): Collection
    {
        return $this->model->forWishList($wishList->id)
            ->where('is_reserved', false)
            ->get();
    }

    /**
     * Find wishes with reservations
     */
    public function findWithReservations(WishList $wishList): Collection
    {
        return $this->model->forWishList($wishList->id)
            ->with(['reservation', 'reservation.user'])
            ->get();
    }

    /**
     * Find wishes by price range
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])->get();
    }

    /**
     * Get wish statistics for wish list
     */
    public function getStatistics(WishList $wishList): WishStatisticsDTO
    {
        $wishesQuery = $this->model->forWishList($wishList->id);
        $wishes = $wishesQuery->get();

        return new WishStatisticsDTO(
            totalWishes: $wishes->count(),
            reservedWishes: $wishes->where('is_reserved', true)->count(),
            availableWishes: $wishes->where('is_reserved', false)->count(),
            totalPrice: $wishes->sum('price'),
            averagePrice: $wishes->avg('price'),
        );
    }

    /**
     * Check if wish is reserved
     */
    public function isReserved(Wish $wish): bool
    {
        return $wish->is_reserved;
    }

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(Wish $wish, User $user): bool
    {
        return $wish->reservation && $wish->reservation->user_id === $user->id;
    }
}
