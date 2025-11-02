<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheType;
use App\Traits\ErrorHandlingTrait;
use Exception;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use App\Models\WishList;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Centralized cache management service
 * Coordinates cache clearing between different services
 */
class CacheManagerService
{
    use ErrorHandlingTrait;
    /**
     * @var array<int, WishList|null>
     */
    private array $wishListCache = [];

    /**
     * Create a new service instance.
     */
    public function __construct(
        public readonly CacheService $cacheService,
        private readonly CacheRepository $cache,
        private readonly WishListRepositoryInterface $wishListRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Clear all application cache
     */
    public function clearAllCache(): bool
    {
        return $this->withErrorHandling(function () {
            return $this->cacheService->clearAllCache();
        }, 'Failed to clear all cache', [], $this->logger) ?? false;
    }

    /**
     * Clear cache by specific type
     */
    public function clearCacheByType(CacheType $type): bool
    {
        return $this->withErrorHandling(function () use ($type) {
            return $this->cacheService->clearCacheByType($type);
        }, "Failed to clear cache for type: {$type->value}", [], $this->logger) ?? false;
    }

    /**
     * Clear all cache related to specific user
     */
    public function clearUserCache(int $userId): bool
    {
        return $this->withErrorHandling(function () use ($userId) {
            return $this->cacheService->clearUserCache($userId);
        }, "Failed to clear cache for user: $userId", [], $this->logger) ?? false;
    }

    /**
     * Clear wish list and wish cache with optimized logic
     */
    public function clearWishListCache(int $wishListId, int $userId): bool
    {
        return $this->clearWishRelatedCache($wishListId, $userId, 'wish list');
    }

    /**
     * Clear wish cache with optimized logic
     */
    public function clearWishCache(int $wishListId, int $userId): bool
    {
        return $this->clearWishRelatedCache($wishListId, $userId, 'wish');
    }

    /**
     * Clear reservation cache for multiple users
     */
    public function clearReservationCache(int $wishId, int $reserverId, int $ownerId): bool
    {
        return $this->withErrorHandling(function () use ($reserverId, $ownerId) {
            $this->clearMultipleUserCaches([$reserverId, $ownerId]);
            return true;
        }, "Failed to clear cache for reservation: wish $wishId", [], $this->logger) ?? false;
    }

    /**
     * Clear friendship cache between users
     */
    public function clearFriendshipCache(int $firstUserId, int $secondUserId): bool
    {
        return $this->withErrorHandling(function () use ($firstUserId, $secondUserId) {
            $this->clearMultipleUserCaches([$firstUserId, $secondUserId]);
            return true;
        }, "Failed to clear friendship cache between users: $firstUserId and $secondUserId", [], $this->logger) ?? false;
    }

    /**
     * Clear public wish list cache by UUID
     */
    public function clearPublicWishListCache(string $uuid): bool
    {
        return $this->withErrorHandling(function () use ($uuid) {
            $publicCacheKey = "static_content:public_wishlist_" . $uuid;
            $this->cache->forget($publicCacheKey);
            return true;
        }, "Failed to clear public wish list cache for UUID: $uuid", [], $this->logger) ?? false;
    }

    /**
     * Get cache configuration statistics
     */
    public function getCacheStats(): array
    {
        return $this->withErrorHandling(function () {
            return $this->cacheService->getCacheStats();
        }, 'Failed to get cache statistics', [], $this->logger) ?? [];
    }

    /**
     * Clear cache for wish list and related user data
     */
    private function clearWishRelatedCache(int $wishListId, int $userId, string $type): bool
    {
        return $this->withErrorHandling(function () use ($wishListId, $userId) {
            $this->cacheService->clearUserCache($userId);
            $this->clearWishListSpecificCaches($wishListId);
            return true;
        }, "Failed to clear cache for $type: $wishListId", [], $this->logger) ?? false;
    }

    /**
     * Clear cache for multiple users efficiently
     */
    private function clearMultipleUserCaches(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->cacheService->clearUserCache($userId);
        }
    }

    /**
     * Clear wish list specific cache entries
     */
    private function clearWishListSpecificCaches(int $wishListId): void
    {
        $wishList = $this->getWishList($wishListId);
        if (!$wishList) {
            return;
        }

        $cacheKeys = array_filter([
            $wishList->uuid ? "static_content:public_wishlist_" . $wishList->uuid : null,
            $wishList->user_id ? "static_content:wishes_list_{$wishListId}_user_{$wishList->user_id}" : null,
            $wishList->user_id ? "static_content:user_wishlists_{$wishList->user_id}" : null,
        ]);

        foreach ($cacheKeys as $key) {
            $this->cache->forget($key);
        }
    }

    /**
     * Get wish list with caching to avoid repeated database queries
     */
    private function getWishList(int $wishListId): ?WishList
    {
        if (!isset($this->wishListCache[$wishListId])) {
            $wishList = $this->wishListRepository->findById($wishListId);
            $this->wishListCache[$wishListId] = $wishList instanceof WishList ? $wishList : null;
        }

        return $this->wishListCache[$wishListId];
    }
}
