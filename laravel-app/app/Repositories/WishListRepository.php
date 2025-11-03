<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\DTOs\WishListStatisticsDTO;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use InvalidArgumentException;

/**
 * WishList repository implementation
 */
final class WishListRepository extends BaseRepository implements WishListRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(
        WishList $model,
        private readonly UserRepositoryInterface $userRepository,
        private readonly WishRepositoryInterface $wishRepository
    ) {
        parent::__construct($model);
    }

    /**
     * Find wish lists by user
     *
     * @return array<object>
     */
    public function findByUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $this->findByUserId($user->id);
    }

    /**
     * Find wish lists by user ID
     *
     * @return array<object>
     */
    public function findByUserId(int $userId): array
    {
        /** @var WishList $model */
        $model = $this->model;

        return $model->forUser($userId)->get()->all();
    }

    /**
     * Find public wish list by UUID
     */
    public function findPublicByUuid(string $uuid): ?object
    {
        /** @var WishList $model */
        $model = $this->model;

        return $model->public()->where('uuid', $uuid)->first();
    }

    /**
     * Find public wish lists
     *
     * @return array<object>
     */
    public function findPublic(): array
    {
        /** @var WishList $model */
        $model = $this->model;

        return $model->public()->get()->all();
    }

    /**
     * Find wish lists with wishes count
     *
     * @return array<object>
     */
    public function findWithWishesCount(object $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        /** @var WishList $model */
        $model = $this->model;
        $wishLists = $model->forUser($user->id)
            ->withCount('wishes')
            ->get()
            ->all();

        foreach ($wishLists as $wishList) {
            $wishList->reserved_wishes_count = $this->wishRepository->countReservedInWishList($wishList);
        }

        return $wishLists;
    }

    /**
     * Get wish list statistics for user
     */
    public function getStatistics(object $user): WishListStatisticsDTO
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        $wishLists = $this->findByUser($user);

        $totalWishes = 0;
        $totalReservedWishes = 0;
        $publicWishListsCount = 0;

        foreach ($wishLists as $wishList) {
            if (!$wishList instanceof WishList) {
                continue;
            }

            $wishes = $this->wishRepository->findByWishListId($wishList->id);
            $totalWishes += count($wishes);

            foreach ($wishes as $wish) {
                if ($wish instanceof Wish && isset($wish->is_reserved) && $wish->is_reserved) {
                    $totalReservedWishes++;
                }
            }

            if (!empty($wishList->uuid)) {
                $publicWishListsCount++;
            }
        }

        return new WishListStatisticsDTO(
            totalWishLists: count($wishLists),
            totalWishes: $totalWishes,
            totalReservedWishes: $totalReservedWishes,
            publicWishLists: $publicWishListsCount,
        );
    }

    /**
     * Check if user owns wish list
     */
    public function isOwnedBy(object $wishList, object $user): bool
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }

        return $wishList->user_id === $user->id;
    }

    /**
     * Find user for wish list
     */
    public function findUserForWishList(object $wishList): ?object
    {
        if (!$wishList instanceof WishList) {
            throw new InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        if (!$wishList->user_id) {
            return null;
        }

        $user = $this->userRepository->findById($wishList->user_id);

        return $user instanceof User ? $user : null;
    }
}
