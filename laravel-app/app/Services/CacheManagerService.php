<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\WishList;

/**
 * Centralized cache management service
 * Coordinates cache clearing between different services
 */
class CacheManagerService
{
    private array $wishListCache = [];

    public function __construct(
        public readonly CacheService $cacheService
    ) {}

    /**
     * Clear all application cache
     */
    public function clearAllCache(): bool
    {
        return $this->withErrorHandling(function () {
            Log::info('CacheManager: Clearing all application cache');
            return $this->cacheService->clearAllCache();
        }, 'Failed to clear all cache') ?? false;
    }

    /**
     * Clear cache by specific type
     */
    public function clearCacheByType(CacheType $type): bool
    {
        return $this->withErrorHandling(function () use ($type) {
            Log::info("CacheManager: Clearing cache by type: {$type->value}");
            return $this->cacheService->clearCacheByType($type);
        }, "Failed to clear cache for type: {$type->value}") ?? false;
    }

    /**
     * Clear all cache related to specific user
     */
    public function clearUserCache(int $userId): bool
    {
        return $this->withErrorHandling(function () use ($userId) {
            Log::info("CacheManager: Clearing cache for user: $userId");
            return $this->cacheService->clearUserCache($userId);
        }, "Failed to clear cache for user: $userId") ?? false;
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
        return $this->withErrorHandling(function () use ($wishId, $reserverId, $ownerId) {
            Log::info("CacheManager: Clearing cache for reservation: wish $wishId, reserver $reserverId, owner $ownerId");

            $this->clearMultipleUserCaches([$reserverId, $ownerId]);

            return true;
        }, "Failed to clear cache for reservation: wish $wishId") ?? false;
    }

    /**
     * Clear friendship cache between users
     */
    public function clearFriendshipCache(int $firstUserId, int $secondUserId): bool
    {
        return $this->withErrorHandling(function () use ($firstUserId, $secondUserId) {
            Log::info("CacheManager: Clearing friendship cache between users: $firstUserId and $secondUserId");

            $this->clearMultipleUserCaches([$firstUserId, $secondUserId]);

            return true;
        }, "Failed to clear friendship cache between users: $firstUserId and $secondUserId") ?? false;
    }

    /**
     * Get cache configuration statistics
     */
    public function getCacheStats(): array
    {
        return $this->withErrorHandling(function () {
            return $this->cacheService->getCacheStats();
        }, 'Failed to get cache statistics') ?? [];
    }

    /**
     * Clear cache for wish list and related user data
     */
    private function clearWishRelatedCache(int $wishListId, int $userId, string $type): bool
    {
        return $this->withErrorHandling(function () use ($wishListId, $userId, $type) {
            Log::info("CacheManager: Clearing cache for $type: $wishListId, user: $userId");

            $this->cacheService->clearUserCache($userId);
            $this->clearWishListSpecificCaches($wishListId);

            return true;
        }, "Failed to clear cache for $type: $wishListId") ?? false;
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

        Log::info("CacheManager: Clearing specific cache keys", ['keys' => $cacheKeys]);
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get wish list with caching to avoid repeated database queries
     */
    private function getWishList(int $wishListId): ?WishList
    {
        if (!isset($this->wishListCache[$wishListId])) {
            $this->wishListCache[$wishListId] = WishList::find($wishListId);
        }

        return $this->wishListCache[$wishListId];
    }

    /**
     * Execute operation safely with error handling and logging
     */
    private function withErrorHandling(callable $operation, string $errorMessage, array $context = [])
    {
        try {
            return $operation();
        } catch (Exception $e) {
            $this->logError($errorMessage, array_merge($context, ['error' => $e->getMessage()]));
            return null;
        }
    }

    /**
     * Log error with context
     */
    private function logError(string $message, array $context = []): void
    {
        Log::error("CacheManager: $message", $context);
    }
}
