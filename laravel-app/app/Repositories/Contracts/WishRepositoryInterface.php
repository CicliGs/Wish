<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\WishStatisticsDTO;

interface WishRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find wishes by wish list
     *
     * @return array<object>
     */
    public function findByWishList(object $wishList): array;

    /**
     * Find wishes by wish list ID
     *
     * @return array<object>
     */
    public function findByWishListId(int $wishListId): array;

    /**
     * Find reserved wishes by user
     * @return array<object>
     */
    public function findReservedByUser(object $user): array;

    /**
     * Find reserved wishes by user ID
     *
     * @return array<object>
     */
    public function findReservedByUserId(int $userId): array;

    /**
     * Find reserved wishes by user in specific wish list
     *
     * @return array<object>
     */
    public function findReservedByUserInWishList(object $user, object $wishList): array;

    /**
     * Find available wishes in wish list
     *
     * @return array<object>
     */
    public function findAvailableInWishList(object $wishList): array;

    /**
     * Find wishes with reservations
     *
     * @return array<object>
     */
    public function findWithReservations(object $wishList): array;

    /**
     * Find wishes by price range
     *
     * @return array<object>
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array;

    /**
     * Get wish statistics for wish list
     */
    public function getStatistics(object $wishList): WishStatisticsDTO;

    /**
     * Check if wish is reserved
     */
    public function isReserved(object $wish): bool;

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(object $wish, object $user): bool;

    /**
     * Count wishes by user ID
     */
    public function countByUserId(int $userId): int;

    /**
     * Count reserved wishes in wish list
     */
    public function countReservedInWishList(object $wishList): int;
}
