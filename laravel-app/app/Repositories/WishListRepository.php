<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishListStatisticsDTO;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * WishList repository implementation
 */
class WishListRepository extends BaseRepository implements WishListRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(WishList $model)
    {
        parent::__construct($model);
    }

    /**
     * Find wish lists by user
     */
    public function findByUser(User $user): Collection
    {
        return $this->findByUserId($user->id);
    }

    /**
     * Find wish lists by user ID
     */
    public function findByUserId(int $userId): Collection
    {
        /** @var WishList $model */
        $model = $this->model;
        return $model->forUser($userId)->with('wishes')->get();
    }

    /**
     * Find public wish list by UUID
     */
    public function findPublicByUuid(string $uuid): ?WishList
    {
        /** @var WishList $model */
        $model = $this->model;
        return $model->public()->where('uuid', $uuid)->with('wishes')->first();
    }

    /**
     * Find public wish lists
     */
    public function findPublic(): Collection
    {
        /** @var WishList $model */
        $model = $this->model;
        return $model->public()->with('wishes')->get();
    }

    /**
     * Find wish lists with wishes count
     */
    public function findWithWishesCount(User $user): Collection
    {
        /** @var WishList $model */
        $model = $this->model;
        return $model->forUser($user->id)
            ->withCount('wishes')
            ->get();
    }

    /**
     * Get wish list statistics for user
     */
    public function getStatistics(User $user): WishListStatisticsDTO
    {
        $wishLists = $this->findByUser($user);

        return new WishListStatisticsDTO(
            totalWishLists: $wishLists->count(),
            totalWishes: $wishLists->sum(fn($wishList) => $wishList->wishes->count()),
            totalReservedWishes: $wishLists->sum(fn($wishList) => $wishList->wishes->where('is_reserved', true)->count()),
            publicWishLists: $wishLists->whereNotNull('uuid')->count(),
        );
    }

    /**
     * Check if user owns wish list
     */
    public function isOwnedBy(WishList $wishList, User $user): bool
    {
        return $wishList->user_id === $user->id;
    }
}
