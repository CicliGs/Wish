<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\WishListStatisticsDTO;

interface WishListRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find wish lists by user
     *
     * @return array<object>
     */
    public function findByUser(object $user): array;

    /**
     * Find wish lists by user ID
     *
     * @return array<object>
     */
    public function findByUserId(int $userId): array;

    /**
     * Find public wish list by UUID
     */
    public function findPublicByUuid(string $uuid): ?object;

    /**
     * Find public wish lists
     *
     * @return array<object>
     */
    public function findPublic(): array;

    /**
     * Find wish lists with wishes count
     *
     * @return array<object>
     */
    public function findWithWishesCount(object $user): array;

    /**
     * Get wish list statistics for user
     */
    public function getStatistics(object $user): WishListStatisticsDTO;

    /**
     * Check if user owns wish list
     */
    public function isOwnedBy(object $wishList, object $user): bool;

    /**
     * Find user for wish list
     */
    public function findUserForWishList(object $wishList): ?object;
}
