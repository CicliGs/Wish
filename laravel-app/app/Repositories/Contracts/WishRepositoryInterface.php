<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishStatisticsDTO;
use Illuminate\Database\Eloquent\Collection;

/**
 * Wish repository interface
 */
interface WishRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find wishes by wish list
     */
    public function findByWishList(WishList $wishList): Collection;

    /**
     * Find wishes by wish list ID
     */
    public function findByWishListId(int $wishListId): Collection;

    /**
     * Find reserved wishes by user
     */
    public function findReservedByUser(User $user): Collection;

    /**
     * Find reserved wishes by user ID
     */
    public function findReservedByUserId(int $userId): Collection;

    /**
     * Find available wishes in wish list
     */
    public function findAvailableInWishList(WishList $wishList): Collection;

    /**
     * Find wishes with reservations
     */
    public function findWithReservations(WishList $wishList): Collection;

    /**
     * Find wishes by price range
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): Collection;

    /**
     * Get wish statistics for wish list
     */
    public function getStatistics(WishList $wishList): WishStatisticsDTO;

    /**
     * Check if wish is reserved
     */
    public function isReserved(Wish $wish): bool;

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(Wish $wish, User $user): bool;
}
