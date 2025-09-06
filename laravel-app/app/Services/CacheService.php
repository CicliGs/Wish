<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CacheService
{
    private const CACHE_KEYS_TTL = 86400;
    private const CACHE_KEYS_STORAGE = 'cache_keys';

    public function cacheStaticContent(string $key, string $content, ?int $ttl = null): bool
    {
        return $this->cache(CacheType::STATIC_CONTENT, $key, $content, $ttl);
    }

    public function getStaticContent(string $key): ?string
    {
        return $this->get(CacheType::STATIC_CONTENT, $key);
    }

    public function cacheImage(string $key, array $imageData, ?int $ttl = null): bool
    {
        return $this->cache(CacheType::IMAGES, $key, $imageData, $ttl);
    }

    public function getImage(string $key): ?array
    {
        return $this->get(CacheType::IMAGES, $key);
    }

    public function cacheAsset(string $key, array $assetData, ?int $ttl = null): bool
    {
        return $this->cache(CacheType::CSS_JS, $key, $assetData, $ttl);
    }

    public function getAsset(string $key): ?array
    {
        return $this->get(CacheType::CSS_JS, $key);
    }

    public function cacheAvatar(int $userId, array $avatarData, ?int $ttl = null): bool
    {
        return $this->cache(CacheType::AVATARS, $userId, $avatarData, $ttl);
    }

    public function getAvatar(int $userId): ?array
    {
        return $this->get(CacheType::AVATARS, $userId);
    }

    public function clearAllCache(): bool
    {
        return $this->executeSafely(function () {
            Cache::flush();
            return true;
        }, 'Failed to clear all cache');
    }

    public function clearCacheByType(CacheType $type): bool
    {
        return $this->executeSafely(function () use ($type) {
            $pattern = $this->buildCacheKey($type, '*');
            $keys = $this->getCacheKeysByPattern($pattern);
            $this->deleteKeys($keys);
            return true;
        }, "Failed to clear cache for type: {$type->value}");
    }

    public function clearUserCache(int $userId): bool
    {
        return $this->executeSafely(function () use ($userId) {
            $userKeys = $this->getUserCacheKeys($userId);
            $this->deleteKeys($userKeys);
            return true;
        }, "Failed to clear cache for user: $userId");
    }

    public function getCacheStats(): array
    {
        return $this->executeSafely(function () {
            return [
                'driver' => Config::get('cache.default'),
                'store' => Config::get('cache.stores.' . Config::get('cache.default') . '.driver'),
                'prefix' => Config::get('cache.prefix'),
                'ttl_settings' => $this->buildTtlSettings(),
                'description' => 'Caching static page elements for performance',
            ];
        }, 'Failed to get cache stats') ?? [];
    }

    public function hasCache(CacheType $type, string $key): bool
    {
        return $this->executeSafely(function () use ($type, $key) {
            return Cache::has($this->buildCacheKey($type, $key));
        }, 'Failed to check cache existence', [
            'type' => $type->value,
            'key' => $key
        ]) ?? false;
    }

    public function getCacheTTL(CacheType $type, string $key): ?int
    {
        return $this->executeSafely(function () use ($type, $key) {
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

    private function cache(CacheType $type, $key, $data, ?int $ttl = null): bool
    {
        return $this->executeSafely(function () use ($type, $key, $data, $ttl) {
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

    private function get(CacheType $type, $key)
    {
        return $this->executeSafely(function () use ($type, $key) {
            return Cache::get($this->buildCacheKey($type, $key));
        }, "Failed to get {$type->value}", [
            'key' => $key,
            'type' => $type->value
        ]);
    }

    private function buildCacheKey(CacheType $type, $key): string
    {
        return $type->getPrefix() . ":$key";
    }

    private function buildTtlSettings(): array
    {
        return [
            CacheType::STATIC_CONTENT->value => CacheType::STATIC_CONTENT->getTTL(),
            CacheType::IMAGES->value => CacheType::IMAGES->getTTL(),
            CacheType::CSS_JS->value => CacheType::CSS_JS->getTTL(),
            CacheType::AVATARS->value => CacheType::AVATARS->getTTL(),
        ];
    }

    private function trackCacheKey(string $cacheKey): void
    {
        $this->executeSafely(function () use ($cacheKey) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_KEYS_TTL);
            }
        }, 'Failed to track cache key', ['key' => $cacheKey]);
    }

    private function getCacheKeysByPattern(string $pattern): array
    {
        return $this->executeSafely(function () use ($pattern) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);
            return array_filter($keys, fn($key) => fnmatch($pattern, $key));
        }, 'Failed to get cache keys by pattern', ['pattern' => $pattern]) ?? [];
    }

    private function getUserCacheKeys(int $userId): array
    {
        return $this->executeSafely(function () use ($userId) {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);
            return array_filter($keys, function($key) use ($userId) {
                return str_contains($key, "user_$userId") ||
                       str_contains($key, "user_wishlists_$userId") ||
                       str_contains($key, "user_profile_$userId") ||
                       (str_contains($key, "wishes_list_") && str_contains($key, "_user_$userId"));
            });
        }, 'Failed to get user cache keys', ['user_id' => $userId]) ?? [];
    }

    private function deleteKeys(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    private function executeSafely(callable $operation, string $errorMessage, array $context = [])
    {
        try {
            return $operation();
        } catch (Exception $e) {
            $this->logError($errorMessage, array_merge($context, ['error' => $e->getMessage()]));
            return null;
        }
    }

    private function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }
}
