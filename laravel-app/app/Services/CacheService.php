<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CacheType;
use App\Traits\ErrorHandlingTrait;
use Exception;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Psr\Log\LoggerInterface;

/**
 * Core cache service for application data caching
 * Handles static content caching with automatic key tracking
 */
class CacheService
{
    use ErrorHandlingTrait;

    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly CacheFactory $cacheFactory,
        private readonly ConfigRepository $config,
        private readonly LoggerInterface $logger
    ) {}
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
            $keys = $this->cache->get(self::CACHE_KEYS_STORAGE, []);
            $this->removeCacheKeys($keys);
            
            $store = $this->cacheFactory->store();
            if (method_exists($store, 'flush')) {
                $store->flush();
            }
            
            return true;
        }, 'Failed to clear all cache', [], $this->logger);
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
        }, "Failed to clear cache for type: {$type->value}", [], $this->logger);
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
        }, "Failed to clear cache for user: $userId", [], $this->logger);
    }

    /**
     * Get cache configuration statistics
     */
    public function getCacheStats(): array
    {
        return $this->withErrorHandling(function () {

            return [
                'driver' => $this->config->get('cache.default'),
                'store' => $this->config->get('cache.stores.' . $this->config->get('cache.default') . '.driver'),
                'prefix' => $this->config->get('cache.prefix'),
                'ttl_settings' => $this->buildTtlSettings(),
                'description' => 'Caching static page elements for performance',
            ];
        }, 'Failed to get cache stats', [], $this->logger) ?? [];
    }

    /**
     * Check if cache entry exists
     */
    public function hasCache(CacheType $type, string $key): bool
    {
        return $this->withErrorHandling(function () use ($type, $key) {

            return $this->cache->has($this->buildCacheKey($type, $key));
        }, 'Failed to check cache existence', [
            'type' => $type->value,
            'key' => $key
        ], $this->logger) ?? false;
    }

    /**
     * Get cache TTL for specific entry
     */
    public function getCacheTTL(CacheType $type, string $key): ?int
    {
        return $this->withErrorHandling(function () use ($type, $key) {
            $cacheKey = $this->buildCacheKey($type, $key);

            if (!$this->cache->has($cacheKey)) {
                return null;
            }

            return $type->getTTL();
        }, 'Failed to get cache TTL', [
            'type' => $type->value,
            'key' => $key
        ], $this->logger);
    }

    /**
     * Store data in cache with error handling
     */
    private function storeInCache(CacheType $type, string $key, mixed $data, ?int $ttl = null): bool
    {
        return $this->withErrorHandling(function () use ($type, $key, $data, $ttl) {
            $ttl = $ttl ?? $type->getTTL();
            $cacheKey = $this->buildCacheKey($type, $key);

            $this->cache->put($cacheKey, $data, $ttl);
            $this->trackCacheKey($cacheKey);

            return true;
        }, "Failed to cache {$type->value}", [
            'key' => $key,
            'type' => $type->value
        ], $this->logger) ?? false;
    }

    /**
     * Retrieve data from cache
     */
    private function retrieveFromCache(CacheType $type, string $key): mixed
    {
        return $this->withErrorHandling(function () use ($type, $key) {
            return $this->cache->get($this->buildCacheKey($type, $key));
        }, "Failed to get {$type->value}", [
            'key' => $key,
            'type' => $type->value
        ], $this->logger);
    }

    /**
     * Build cache key with type prefix
     */
    private function buildCacheKey(CacheType $type, string $key): string
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
            $keys = $this->cache->get(self::CACHE_KEYS_STORAGE, []);

            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                $this->cache->put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_KEYS_TTL);
            }
        }, 'Failed to track cache key', ['key' => $cacheKey], $this->logger);
    }

    /**
     * Get cache keys matching pattern
     */
    private function getCacheKeysByPattern(string $pattern): array
    {
        return $this->withErrorHandling(function () use ($pattern) {
            $keys = $this->cache->get(self::CACHE_KEYS_STORAGE, []);
            return array_filter($keys, fn($key) => fnmatch($pattern, $key));
        }, 'Failed to get cache keys by pattern', ['pattern' => $pattern], $this->logger) ?? [];
    }

    /**
     * Get all cache keys related to specific user
     */
    private function getUserCacheKeys(int $userId): array
    {
        return $this->withErrorHandling(function () use ($userId) {
            $keys = $this->cache->get(self::CACHE_KEYS_STORAGE, []);

            return array_filter($keys, function($key) use ($userId) {

                return str_contains($key, "user_$userId") ||
                       str_contains($key, "user_wishlists_$userId") ||
                       str_contains($key, "user_profile_$userId") ||
                       (str_contains($key, "wishes_list_") && str_contains($key, "_user_$userId"));
            });
        }, 'Failed to get user cache keys', ['user_id' => $userId], $this->logger) ?? [];
    }

    /**
     * Remove multiple cache keys from storage
     */
    private function removeCacheKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->cache->forget($key);
        }
    }
}
