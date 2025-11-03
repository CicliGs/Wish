<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishStatisticsDTO;
use App\Repositories\Contracts\WishRepositoryInterface;
use InvalidArgumentException;

/**
 * Wish repository implementation
 */
final class WishRepository extends BaseRepository implements WishRepositoryInterface
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
     *
     * @return array<object>
     */
    public function findByWishList(object $wishList): array
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }

        return $this->findByWishListId($wishList->id);
    }

    /**
     * Find wishes by wish list ID
     *
     * @return array<object>
     */
    public function findByWishListId(int $wishListId): array
    {
        /** @var Wish $model */
        $model = $this->model;

        return $model->forWishList($wishListId)->with('reservation.user')->get()->all();
    }

    /**
     * Find reserved wishes by user
     *
     * @return array<object>
     */
    public function findReservedByUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $this->findReservedByUserId($user->id);
    }

    /**
     * Find reserved wishes by user ID
     *
     * @return array<object>
     */
    public function findReservedByUserId(int $userId): array
    {
        return $this->model->whereHas('reservation', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['wishList', 'wishList.user'])->get()->all();
    }

    /**
     * Find reserved wishes by user in specific wish list
     *
     * @return array<object>
     */
    public function findReservedByUserInWishList(object $user, object $wishList): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }

        return $this->model->where('wish_list_id', $wishList->id)
            ->whereHas('reservation', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['wishList', 'wishList.user'])
            ->get()
            ->all();
    }

    /**
     * Find available wishes in wish list
     *
     * @return array<object>
     */
    public function findAvailableInWishList(object $wishList): array
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        /** @var Wish $model */
        $model = $this->model;

        return $model->forWishList($wishList->id)
            ->where('is_reserved', false)
            ->get()
            ->all();
    }

    /**
     * Find wishes with reservations
     *
     * @return array<object>
     */
    public function findWithReservations(object $wishList): array
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        /** @var Wish $model */
        $model = $this->model;

        return $model->forWishList($wishList->id)
            ->with(['reservation', 'reservation.user'])
            ->get()
            ->all();
    }

    /**
     * Find wishes by price range
     *
     * @return array<object>
     */
    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        return $this->model->whereBetween('price', [$minPrice, $maxPrice])->get()->all();
    }

    /**
     * Get wish statistics for wish list
     */
    public function getStatistics(object $wishList): WishStatisticsDTO
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        /** @var Wish $model */
        $model = $this->model;
        $wishesQuery = $model->forWishList($wishList->id);
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
    public function isReserved(object $wish): bool
    {
        if (!$wish instanceof Wish) {
            throw new InvalidArgumentException('Wish must be an instance of ' . Wish::class);
        }

        return $wish->is_reserved;
    }

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(object $wish, object $user): bool
    {
        if (!$wish instanceof Wish) {
            throw new InvalidArgumentException('Wish must be an instance of ' . Wish::class);
        }
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $wish->reservation && $wish->reservation->user_id === $user->id;
    }

    /**
     * Count wishes by user ID
     */
    public function countByUserId(int $userId): int
    {
        return $this->model->whereHas('wishList', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
    }

    /**
     * Count reserved wishes in wish list
     */
    public function countReservedInWishList(object $wishList): int
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }

        return $this->model
            ->where('wish_list_id', $wishList->id)
            ->where('is_reserved', true)
            ->count();
    }
}
