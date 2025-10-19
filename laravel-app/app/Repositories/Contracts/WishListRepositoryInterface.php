<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishListStatisticsDTO;
use Illuminate\Database\Eloquent\Collection;

/**
 * WishList repository interface
 */
interface WishListRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find wish lists by user
     */
    public function findByUser(User $user): Collection;

    /**
     * Find wish lists by user ID
     */
    public function findByUserId(int $userId): Collection;

    /**
     * Find public wish list by UUID
     */
    public function findPublicByUuid(string $uuid): ?WishList;

    /**
     * Find public wish lists
     */
    public function findPublic(): Collection;

    /**
     * Find wish lists with wishes count
     */
    public function findWithWishesCount(User $user): Collection;

    /**
     * Get wish list statistics for user
     */
    public function getStatistics(User $user): WishListStatisticsDTO;

    /**
     * Check if user owns wish list
     */
    public function isOwnedBy(WishList $wishList, User $user): bool;
}
