<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * Core cache service for application data caching
 * Handles static content caching with automatic key tracking
 */
class CacheService
{
    private const CACHE_KEYS_TTL = 86400;
    private const CACHE_KEYS_STORAGE = 'cache_keys';

    /**
     * Cache static content with optional TTL
     */
    public function cacheStaticContent(string $key, string $content, ?int $ttl = null): bool
    {
        return $this->storeInCache(CacheType::STATIC_CONTENT, $key, $content, $ttl);
    }

    /**
     * Retrieve cached static content
     */
    public function getStaticContent(string $key): ?string
    {
        return $this->retrieveFromCache(CacheType::STATIC_CONTENT, $key);
    }

    /**
     * Clear all application cache
     */
    public function clearAllCache(): bool
    {
        return $this->withErrorHandling(function () {
            Cache::flush();
            return true;
        }, 'Failed to clear all cache');
    }

    /**
     * Clear cache by specific type
     */
    public function clearCacheByType(CacheType $type): bool
    {
        return $this->withErrorHandling(function () use ($type) {
            $pattern = $this->buildCacheKey($type, '*');
            $keys = $this->getCacheKeysByPattern($pattern);
            $this->removeCacheKeys($keys);
            return true;
        }, "Failed to clear cache for type: {$type->value}");
    }

    /**
     * Clear all cache related to specific user
     */
    public function clearUserCache(int $userId): bool
    {
        return $this->withErrorHandling(function () use ($userId) {
            $userKeys = $this->getUserCacheKeys($userId);
            $this->removeCacheKeys($userKeys);
            return true;
        }, "Failed to clear cache for user: $userId");
    }

    /**
     * Get cache configuration statistics
     */
    public function getCacheStats(): array
    {
        return $this->withErrorHandling(function () {
            return [
                'driver' => Config::get('cache.default'),
                'store' => Config::get('cache.stores.' . Config::get('cache.default') . '.driver'),
                'prefix' => Config::get('cache.prefix'),
                'ttl_settings' => $this->buildTtlSettings(),
                'description' => 'Caching static page elements for performance',
            ];
        }, 'Failed to get cache stats') ?? [];
    }

    /**
     * Check if cache entry exists
     */
    public function hasCache(CacheType $type, string $key): bool
    {
        return $this->withErrorHandling(function () use ($type, $key) {
            return Cache::has($this->buildCacheKey($type, $key));
        }, 'Failed to check cache existence', [
            'type' => $type->value,
            'key' => $key
        ]) ?? false;
    }

    /**
     * Get cache TTL for specific entry
     */
    public function getCacheTTL(CacheType $type, string $key): ?int
    {
        return $this->withErrorHandling(function () use ($type, $key) {
            $cacheKey = $this->buildCacheKey($type, $key);

            if (!Cache::has($cacheKey)) {
                return null;
            }

            return $type->getTTL();
        }, 'Failed to get cache TTL', [
            'type' => $type->value,
            'key' => $key
        ]);
    }

    /**
     * Store data in cache with automatic key tracking
     */
    private function storeInCache(CacheType $type, $key, $data, ?int $ttl = null): bool
    {
        return $this->withErrorHandling(function () use ($type, $key, $data, $ttl) {
            $ttl = $ttl ?? $type->getTTL();
            $cacheKey = $this->buildCacheKey($type, $key);

            Cache::put($cacheKey, $data, $ttl);
            $this->trackCacheKey($cacheKey);

            return true;
        }, "Failed to cache {$type->value}", [
            'key' => $key,
            'type' => $type->value
        ]) ?? false;
    }

    /**
     * Retrieve data from cache
     */
    private function retrieveFromCache(CacheType $type, $key)
    {
        return $this->withErrorHandling(function () use ($type, $key) {

            return Cache::get($this->buildCacheKey($type, $key));
        }, "Failed to get {$type->value}", [
            'key' => $key,
            'type' => $type->value
        ]);
    }

    /**
     * Build cache key with type prefix
     */
    private function buildCacheKey(CacheType $type, $key): string
    {
        return $type->getPrefix() . ":$key";
    }

    /**
     * Build TTL settings array for statistics
     */
    private function buildTtlSettings(): array
    {
        return [
            CacheType::STATIC_CONTENT->value => CacheType::STATIC_CONTENT->getTTL(),
            CacheType::IMAGES->value => CacheType::IMAGES->getTTL(),
            CacheType::CSS_JS->value => CacheType::CSS_JS->getTTL(),
            CacheType::AVATARS->value => CacheType::AVATARS->getTTL(),
        ];
    }

    /**
     * Track cache key for selective clearing
     */
    private function trackCacheKey(string $cacheKey): void
    {
        $this->withErrorHandling(function () use ($cacheKey) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_KEYS_TTL);
            }
        }, 'Failed to track cache key', ['key' => $cacheKey]);
    }

    /**
     * Get cache keys matching pattern
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        return $this->withErrorHandling(function () use ($pattern) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);
            return array_filter($keys, fn($key) => fnmatch($pattern, $key));
        }, 'Failed to get cache keys by pattern', ['pattern' => $pattern]) ?? [];
    }

    /**
     * Get all cache keys related to specific user
     */
    private function getUserCacheKeys(int $userId): array
    {
        return $this->withErrorHandling(function () use ($userId) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

            return array_filter($keys, function($key) use ($userId) {
                return str_contains($key, "user_$userId") ||
                       str_contains($key, "user_wishlists_$userId") ||
                       str_contains($key, "user_profile_$userId") ||
                       (str_contains($key, "wishes_list_") && str_contains($key, "_user_$userId"));
            });
        }, 'Failed to get user cache keys', ['user_id' => $userId]) ?? [];
    }

    /**
     * Remove multiple cache keys from storage
     */
    private function removeCacheKeys(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Execute operation with error handling and logging
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
        Log::error($message, $context);
    }
}
