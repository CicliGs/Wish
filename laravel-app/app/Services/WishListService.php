<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishListDTO;
use App\DTOs\PublicWishListDTO;
use App\Models\WishList;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class WishListService
{
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    public function findWishListsByUser(int $userId): Collection
    {
        return WishList::forUser($userId)->with('wishes')->get();
    }

    /**
     * Create a new wish list.
     */
    public function create(array $data, int $userId): WishList
    {
        $data['user_id'] = $userId;

        $wishList = WishList::create($data);
        $this->cacheManager->clearUserCache($userId);

        return $wishList;
    }

    /**
     * Update an existing wish list.
     */
    public function update(WishList $wishList, array $data): WishList
    {
        $wasPublic = $wishList->is_public;
        $willBePublic = $data['is_public'] ?? $wasPublic;

        $wishList->update($data);
        $this->cacheManager->clearUserCache($wishList->user_id);

        if ($wasPublic !== $willBePublic && $wishList->uuid) {
            $publicCacheKey = "public_wishlist_" . $wishList->uuid;
            Cache::forget("static_content:" . $publicCacheKey);
        }

        return $wishList->fresh();
    }

    /**
     * Delete a wish list and clear related caches.
     */
    public function delete(WishList $wishList): bool
    {
        try {
            $this->clearRelatedCaches($wishList);

            $result = $wishList->delete();

            return $result;
        } catch (Exception $e) {
            $this->logError('Error deleting wish list', [
                'wish_list_id' => $wishList->id,
                'user_id' => $wishList->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Clear caches related to the wish list.
     */
    private function clearRelatedCaches(WishList $wishList): void
    {
        $this->cacheManager->clearUserCache($wishList->user_id);

        $this->cacheManager->clearWishListCache($wishList->id, $wishList->user_id);

        if ($wishList->uuid) {
            $this->clearPublicCache($wishList->uuid);
        }
    }

    /**
     * Clear public cache for wish list.
     */
    private function clearPublicCache(string $uuid): void
    {
        $publicCacheKey = "public_wishlist_" . $uuid;
        Cache::forget("static_content:" . $publicCacheKey);
    }

    /**
     * Centralized error logging method.
     */
    private function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    public function findPublicWishListByUuid(string $uuid): ?WishList
    {
        return WishList::public()->where('uuid', $uuid)->with('wishes')->first();
    }

    public function getUserStatistics(int $userId): array
    {
        $wishLists = $this->findWishListsByUser($userId);

        return [
            'total_wish_lists' => $wishLists->count(),
            'total_wishes' => $wishLists->sum(fn($wishList) => $wishList->wishes->count()),
            'total_reserved_wishes' => $wishLists->sum(fn($wishList) => $wishList->wishes->where('is_reserved', true)->count()),
            'public_wish_lists' => $wishLists->whereNotNull('uuid')->count(),
        ];
    }

    public function getPublicWishListData(string $uuid): PublicWishListDTO
    {
        $cacheKey = "public_wishlist_$uuid";
        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishList = $this->findPublicWishListByUuid($uuid);

        if (!$wishList) {
            throw new ModelNotFoundException();
        }

        $dto = PublicWishListDTO::fromWishList($wishList);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);

        return $dto;
    }

    public function getIndexData(int $userId): WishListDTO
    {
        $cacheKey = "user_wishlists_$userId";
        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishLists = $this->findWishListsByUser($userId);
        $stats = $this->getUserStatistics($userId);

        $dto = WishListDTO::fromWishLists($wishLists, $userId, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 3600);

        return $dto;
    }
}
