<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CacheService
{
    private const CACHE_KEYS_TTL = 86400; // 24 hours
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
        try {
            Cache::flush();
            Log::info('All cache cleared');
            return true;
        } catch (Exception $e) {
            $this->logError('Failed to clear all cache', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getCacheStats(): array
    {
        try {
            return [
                'driver' => Config::get('cache.default'),
                'store' => Config::get('cache.stores.' . Config::get('cache.default') . '.driver'),
                'prefix' => Config::get('cache.prefix'),
                'ttl_settings' => $this->buildTtlSettings(),
                'description' => 'Caching static page elements for performance',
            ];
        } catch (Exception $e) {
            $this->logError('Failed to get cache stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function hasCache(CacheType $type, string $key): bool
    {
        try {
            return Cache::has($this->buildCacheKey($type, $key));
        } catch (Exception $e) {
            $this->logError('Failed to check cache existence', [
                'type' => $type->value,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getCacheTTL(CacheType $type, string $key): ?int
    {
        try {
            $cacheKey = $this->buildCacheKey($type, $key);

            if (!Cache::has($cacheKey)) {
                return null;
            }

            // Возвращаем настроенный TTL для данного типа кеша
            return $type->getTTL();
        } catch (Exception $e) {
            $this->logError('Failed to get cache TTL', [
                'type' => $type->value,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function cache(CacheType $type, $key, $data, ?int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? $type->getTTL();
            $cacheKey = $this->buildCacheKey($type, $key);

            Cache::put($cacheKey, $data, $ttl);
            $this->trackCacheKey($cacheKey);

            Log::info("$type->value cached", [
                'key' => $key,
                'ttl' => $ttl,
                'type' => $type->value
            ]);

            return true;
        } catch (Exception $e) {
            $this->logError("Failed to cache $type->value", [
                'key' => $key,
                'type' => $type->value,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function get(CacheType $type, $key)
    {
        try {
            return Cache::get($this->buildCacheKey($type, $key));
        } catch (Exception $e) {
            $this->logError("Failed to get $type->value", [
                'key' => $key,
                'type' => $type->value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function buildCacheKey(CacheType $type, $key): string
    {
        return $type->getPrefix() . ":$key";
    }

    private function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    private function trackCacheKey(string $cacheKey): void
    {
        try {
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);

            if (!in_array($cacheKey, $keys, true)) {
                $keys[] = $cacheKey;
                Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_KEYS_TTL);
            }
        } catch (Exception $e) {
            Log::warning('Failed to track cache key', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function clearCacheByType(CacheType $type): bool
    {
        try {
            $prefix = $type->getPrefix();
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);
            $keysToDelete = $this->filterKeysByPrefix($keys, $prefix);

            $this->deleteKeys($keysToDelete);
            $this->updateCacheKeys(array_diff($keys, $keysToDelete));

            Log::info("Cache cleared for type: $type->value", [
                'deleted_keys' => count($keysToDelete)
            ]);

            return true;
        } catch (Exception $e) {
            $this->logError("Failed to clear cache for type: $type->value", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function clearUserCache(int $userId): bool
    {
        try {
            $userPrefix = "user_$userId";
            $keys = Cache::get(self::CACHE_KEYS_STORAGE, []);
            $keysToDelete = $this->filterKeysByPattern($keys, $userPrefix);

            $this->deleteKeys($keysToDelete);
            $this->updateCacheKeys(array_diff($keys, $keysToDelete));

            Log::info("Cache cleared for user: $userId", [
                'deleted_keys' => count($keysToDelete)
            ]);

            return true;
        } catch (Exception $e) {
            $this->logError("Failed to clear cache for user: $userId", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
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

    private function filterKeysByPrefix(array $keys, string $prefix): array
    {
        return array_filter($keys, fn($key) => str_starts_with($key, $prefix));
    }

    private function filterKeysByPattern(array $keys, string $pattern): array
    {
        return array_filter($keys, fn($key) => str_contains($key, $pattern));
    }

    private function deleteKeys(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    private function updateCacheKeys(array $keys): void
    {
        Cache::put(self::CACHE_KEYS_STORAGE, $keys, self::CACHE_KEYS_TTL);
    }
}
