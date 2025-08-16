<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    private const TTL_PAGE_CACHE = 7200;
    private const TTL_FREQUENT_PAGES = 14400;
    private const TTL_STATIC_PAGES = 28800;
    private const TTL_USER_PROFILE = 3600;
    private const TTL_WISH_LISTS = 2400;
    private const TTL_FRIENDS_LIST = 1800;
    private const TTL_STATISTICS = 600;
    private const TTL_HEAVY_QUERIES = 1800;
    private const TTL_AVATARS = 14400;
    private const TTL_SEARCH_RESULTS = 900;
    private const TTL_AGGREGATE_DATA = 3600;

    private const AVATAR_STORAGE_PATH = 'avatars';
    private const MAX_AVATAR_SIZE = 2048;
    private const DEFAULT_SEARCH_LIMIT = 10;

    /**
     * Clear page cache by pattern with improved Redis handling
     */
    public function clearPageCache(string $pattern = 'page_cache:*'): bool
    {
        try {
            if (Config::get('cache.default') === 'redis') {
                $redis = Redis::connection();
                $iterator = null;
                $keys = [];
                
                do {
                    $result = $redis->scan($iterator, ['match' => $pattern, 'count' => 100]);
                    $iterator = $result[0];
                    $keys = array_merge($keys, $result[1]);
                } while ($iterator != 0);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                    Log::info('Cleared page cache', ['pattern' => $pattern, 'keys_count' => count($keys)]);
                }
                return true;
            }
            
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear page cache', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cache statistics with enhanced metrics
     */
    public function getCacheStats(): array
    {
        try {
            $stats = [
                'driver' => Config::get('cache.default'),
                'store' => Config::get('cache.stores.' . Config::get('cache.default') . '.driver'),
                'prefix' => Config::get('cache.prefix'),
                'ttl_settings' => [
                    'page_cache' => self::TTL_PAGE_CACHE,
                    'frequent_pages' => self::TTL_FREQUENT_PAGES,
                    'static_pages' => self::TTL_STATIC_PAGES,
                    'user_profile' => self::TTL_USER_PROFILE,
                    'wish_lists' => self::TTL_WISH_LISTS,
                    'friends_list' => self::TTL_FRIENDS_LIST,
                    'statistics' => self::TTL_STATISTICS,
                    'heavy_queries' => self::TTL_HEAVY_QUERIES,
                    'avatars' => self::TTL_AVATARS,
                    'search_results' => self::TTL_SEARCH_RESULTS,
                    'aggregate_data' => self::TTL_AGGREGATE_DATA,
                ],
                'ttl_improvements' => [
                    'page_cache_increased' => '2x (1h → 2h)',
                    'frequent_pages_new' => '4h for very frequent pages',
                    'static_pages_new' => '8h for static content',
                    'user_profile_increased' => '2x (30m → 1h)',
                    'wish_lists_increased' => '2x (20m → 40m)',
                    'friends_list_increased' => '2x (15m → 30m)',
                    'statistics_increased' => '2x (5m → 10m)',
                    'heavy_queries_increased' => '3x (10m → 30m)',
                    'avatars_increased' => '2x (2h → 4h)',
                ],
                'redis_host' => Config::get('database.redis.default.host'),
                'redis_port' => Config::get('database.redis.default.port'),
                'redis_database' => Config::get('database.redis.default.database'),
            ];

            if (Config::get('cache.default') === 'redis') {
                try {
                    $redisInfo = Redis::info();
                    $stats['redis_stats'] = [
                        'used_memory_human' => $redisInfo['used_memory_human'] ?? 'unknown',
                        'connected_clients' => $redisInfo['connected_clients'] ?? 0,
                        'total_commands_processed' => $redisInfo['total_commands_processed'] ?? 0,
                        'keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
                        'keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0,
                    ];
                } catch (\Exception $e) {
                    $stats['redis_stats'] = ['error' => $e->getMessage()];
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get Redis status and statistics
     */
    public function getRedisStatus(): array
    {
        try {
            $info = Redis::info();
            
            $dbSize = Redis::dbsize();
            $lastSaveTime = Redis::lastsave();
            $currentTime = time();
            
            return [
                'status' => 'connected',
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? 'unknown',
                'used_memory_peak_human' => $info['used_memory_peak_human'] ?? 'unknown',
                'uptime_in_seconds' => $info['uptime_in_seconds'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'total_connections_received' => $info['total_connections_received'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'db_size' => $dbSize,
                'last_save_time' => $lastSaveTime,
                'current_time' => $currentTime,
                'hit_rate' => $this->calculateHitRate($info),
                'memory_usage_percent' => $this->calculateMemoryUsage($info),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get Redis status', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ];
        }
    }

    /**
     * Test Redis connection
     */
    public function testRedisConnection(): array
    {
        try {
            $redis = Redis::connection();
            $ping = $redis->ping();
            
            $info = Redis::info();
            
            return [
                'status' => 'success',
                'message' => 'Redis connection successful',
                'ping' => $ping,
                'config' => [
                    'host' => Config::get('database.redis.default.host'),
                    'port' => Config::get('database.redis.default.port'),
                    'database' => Config::get('database.redis.default.database'),
                    'client' => Config::get('database.redis.client'),
                    'cache_database' => Config::get('database.redis.cache.database'),
                ],
                'redis_info' => [
                    'version' => $info['redis_version'] ?? 'unknown',
                    'uptime' => $info['uptime_in_seconds'] ?? 0,
                    'clients' => $info['connected_clients'] ?? 0,
                    'memory' => $info['used_memory_human'] ?? 'unknown',
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Redis connection test failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Redis connection failed: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'config' => [
                    'host' => Config::get('database.redis.default.host'),
                    'port' => Config::get('database.redis.default.port'),
                    'database' => Config::get('database.redis.default.database'),
                    'client' => Config::get('database.redis.client'),
                    'cache_database' => Config::get('database.redis.cache.database'),
                ]
            ];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(array $info): float
    {
        $hits = (int) ($info['keyspace_hits'] ?? 0);
        $misses = (int) ($info['keyspace_misses'] ?? 0);
        $total = $hits + $misses;
        
        if ($total === 0) {
            return 0.0;
        }
        
        return round(($hits / $total) * 100, 2);
    }

    /**
     * Calculate memory usage percentage
     */
    private function calculateMemoryUsage(array $info): float
    {
        $usedMemory = (int) ($info['used_memory'] ?? 0);
        $maxMemory = (int) ($info['maxmemory'] ?? 0);
        
        if ($maxMemory === 0) {
            return 0.0;
        }
        
        return round(($usedMemory / $maxMemory) * 100, 2);
    }

    /**
     * Get comprehensive cache overview for controller
     */
    public function getCacheOverview(): array
    {
        try {
            return [
                'general_cache' => $this->getCacheStats(),
                'redis_status' => $this->getRedisStatus(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache overview', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get combined statistics for views
     */
    public function getCombinedStats(array $dbStats = [], array $pageStats = []): array
    {
        $stats = $this->getCacheStats();
        
        return array_merge($stats, [
            'database_cache' => $dbStats,
            'page_cache' => $pageStats
        ]);
    }

    /**
     * Clear page cache with response data
     */
    public function clearPageCacheWithResponse(string $pattern = 'page_cache:*'): array
    {
        $success = $this->clearPageCache($pattern);
        
        return [
            'success' => $success,
            'message' => $success ? 'Cache cleared successfully' : 'Failed to clear cache'
        ];
    }

    /**
     * Clear user cache with response data
     */
    public function clearUserCacheWithResponse(int $userId): array
    {
        try {
            $patterns = [
                "user_profile:{$userId}",
                "user_wish_lists:{$userId}",
                "user_friends:{$userId}",
                "user_achievements:{$userId}",
                "user_stats:{$userId}"
            ];
            
            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }
            
            Log::info('Cleared user cache', ['user_id' => $userId]);
            return [
                'success' => true,
                'message' => 'User cache cleared successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to clear user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to clear user cache'
            ];
        }
    }

    /**
     * Clear all cache with response data
     */
    public function clearAllCacheWithResponse(): array
    {
        try {
            $this->clearPageCache();
            Cache::flush();
            
            return [
                'success' => true,
                'message' => 'All cache cleared successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to clear all cache', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to clear all cache'
            ];
        }
    }

    /**
     * Clear database cache with response data
     */
    public function clearDatabaseCacheWithResponse(): array
    {
        try {
            Cache::flush();
            
            return [
                'success' => true,
                'message' => 'Database cache cleared successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to clear database cache', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to clear database cache'
            ];
        }
    }

    /**
     * Clear cache by type with response data
     */
    public function clearCacheByTypeWithResponse(string $type): array
    {
        try {
            $this->clearPageCache("{$type}:*");
            
            return [
                'success' => true,
                'message' => "Cache type '{$type}' cleared successfully"
            ];
        } catch (\Exception $e) {
            Log::error('Failed to clear cache by type', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "Failed to clear cache type '{$type}'"
            ];
        }
    }

    /**
     * Validate cache type
     */
    public function validateCacheType(string $type): bool
    {
        $validTypes = [
            'page_cache',
            'user_profile',
            'wish_lists',
            'friends_list',
            'search_results',
            'aggregate_data',
            'heavy_queries',
            'statistics'
        ];
        
        return in_array($type, $validTypes);
    }
} 