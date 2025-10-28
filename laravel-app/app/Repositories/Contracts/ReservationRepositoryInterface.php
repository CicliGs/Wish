<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishList;
use App\DTOs\ReservationStatisticsDTO;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reservation repository interface
 */
interface ReservationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find reservation by wish and user
     */
    public function findByWishAndUser(Wish $wish, User $user): ?Reservation;

    /**
     * Find reservations by user
     */
    public function findByUser(User $user): Collection;

    /**
     * Find reservations by wish list
     */
    public function findByWishList(WishList $wishList): Collection;

    /**
     * Find reservations by wish
     */
    public function findByWish(Wish $wish): Collection;

    /**
     * Find reservations with related data
     */
    public function findWithRelations(User|WishList $entity): Collection;

    /**
     * Get reservation statistics
     */
    public function getStatistics(User|WishList $entity): ReservationStatisticsDTO;

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(Wish $wish, User $user): bool;

    /**
     * Count reservations for user
     */
    public function countByUser(User $user): int;

    /**
     * Count reservations for wish list
     */
    public function countByWishList(WishList $wishList): int;
}
