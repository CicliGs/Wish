<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishListDTO;
use App\DTOs\PublicWishListDTO;
use App\Models\User;
use App\Models\WishList;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WishListService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        protected WishListRepositoryInterface $wishListRepository
    ) {}

    /**
     * Find wish lists by user.
     */
    public function findWishLists(User $user): Collection
    {
        return $this->wishListRepository->findByUser($user);
    }

    /**
     * Create a new wish list.
     */
    public function create(array $data, User $user): Model
    {
        $data['user_id'] = $user->id;

        $wishList = $this->wishListRepository->create($data);
        $this->cacheManager->clearUserCache($user->id);

        return $wishList;
    }

    /**
     * Update an existing wish list.
     */
    public function update(WishList $wishList, array $data): Model
    {
        $wasPublic = $wishList->is_public;
        $willBePublic = $data['is_public'] ?? $wasPublic;

        $wishList = $this->wishListRepository->update($wishList, $data);
        $this->cacheManager->clearUserCache($wishList->user_id);

        if ($wasPublic !== $willBePublic && $wishList->uuid) {
            $this->cacheManager->clearPublicWishListCache($wishList->uuid);
        }

        return $wishList;
    }

    /**
     * Delete a wish list and clear related caches.
     */
    public function delete(WishList $wishList): bool
    {
        $this->clearRelatedCaches($wishList);

        return $this->wishListRepository->delete($wishList);
    }

    /**
     * Find public wish list by UUID.
     */
    public function findPublicByUuid(string $uuid): ?WishList
    {
        return $this->wishListRepository->findPublicByUuid($uuid);
    }

    /**
     * Get user statistics.
     */
    public function getStatistics(User $user): array
    {
        return $this->wishListRepository->getStatistics($user)->toArray();
    }

    /**
     * Get public wish list data.
     */
    public function getPublicData(string $uuid, ?User $currentUser = null): PublicWishListDTO
    {
        $wishList = $this->findPublicByUuid($uuid);

        if (!$wishList) {
            throw new ModelNotFoundException();
        }

        return PublicWishListDTO::fromWishList($wishList, $currentUser);
    }

    /**
     * Get index data with caching.
     */
    public function getIndexData(User $user): WishListDTO
    {
        $cacheKey = "user_wishlists_{$user->id}";
        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishLists = $this->findWishLists($user);
        $stats = $this->getStatistics($user);

        $dto = WishListDTO::fromWishLists($wishLists, $user->id, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 3600);

        return $dto;
    }

    /**
     * Clear caches related to the wish list.
     */
    private function clearRelatedCaches(WishList $wishList): void
    {
        $this->cacheManager->clearUserCache($wishList->user_id);
        $this->cacheManager->clearWishListCache($wishList->id, $wishList->user_id);

        if ($wishList->uuid) {
            $this->cacheManager->clearPublicWishListCache($wishList->uuid);
        }
    }
}
