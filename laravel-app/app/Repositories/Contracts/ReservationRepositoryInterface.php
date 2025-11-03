<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\ReservationStatisticsDTO;

interface ReservationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find reservation by wish and user
     */
    public function findByWishAndUser(object $wish, object $user): ?object;

    /**
     * Find reservations by user
     *
     * @return array<object>
     */
    public function findByUser(object $user): array;

    /**
     * Find reservations by wish list
     *
     * @return array<object>
     */
    public function findByWishList(object $wishList): array;

    /**
     * Find reservations by wish
     *
     * @return array<object>
     */
    public function findByWish(object $wish): array;

    /**
     * Find reservations with related data
     *
     * @return array<object>
     */
    public function findWithRelations(object $entity): array;

    /**
     * Get reservation statistics
     */
    public function getStatistics(object $entity): ReservationStatisticsDTO;

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(object $wish, object $user): bool;

    /**
     * Count reservations for user
     */
    public function countByUser(object $user): int;

    /**
     * Count reservations for wish list
     */
    public function countByWishList(object $wishList): int;
}
