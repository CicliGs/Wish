<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class DatabaseCacheService
{
    private const TTL_HEAVY_QUERIES = 1800;
    private const TTL_USER_STATS = 1800;
    private const TTL_AGGREGATE_DATA = 3600;
    private const TTL_SEARCH_RESULTS = 900;
    private const TTL_COMPLEX_JOINS = 2400;
    private const TTL_COUNT_QUERIES = 1200;
    private const TTL_GROUP_BY_QUERIES = 1800;
    private const TTL_SUBQUERIES = 1500;

    /**
     * Get cache statistics for database queries
     */
    public function getCacheStatistics(): array
    {
        try {
            $stats = [
                'ttl_settings' => [
                    'heavy_queries' => self::TTL_HEAVY_QUERIES,
                    'complex_joins' => self::TTL_COMPLEX_JOINS,
                    'count_queries' => self::TTL_COUNT_QUERIES,
                    'group_by_queries' => self::TTL_GROUP_BY_QUERIES,
                    'subqueries' => self::TTL_SUBQUERIES,
                    'user_stats' => self::TTL_USER_STATS,
                    'aggregate_data' => self::TTL_AGGREGATE_DATA,
                    'search_results' => self::TTL_SEARCH_RESULTS,
                ],
                'ttl_improvements' => [
                    'heavy_queries_increased' => '3x (10m → 30m)',
                    'complex_joins_new' => '40m for complex JOINs',
                    'count_queries_new' => '20m for COUNT queries',
                    'group_by_queries_new' => '30m for GROUP BY',
                    'subqueries_new' => '25m for subqueries',
                    'user_stats_increased' => '2x (15m → 30m)',
                    'aggregate_data_increased' => '2x (30m → 1h)',
                    'search_results_increased' => '3x (5m → 15m)',
                ],
                'cache_driver' => config('cache.default'),
            ];

            if (config('cache.default') === 'redis') {
                try {
                    $redis = Redis::connection();
                    $keys = $redis->keys('db_query:*');
                    $stats['cached_queries_count'] = count($keys);
                    
                    $keys = $redis->keys('user_stats:*');
                    $stats['cached_user_stats_count'] = count($keys);
                    
                    $keys = $redis->keys('aggregate:*');
                    $stats['cached_aggregate_data_count'] = count($keys);
                    
                    $keys = $redis->keys('search:*');
                    $stats['cached_search_results_count'] = count($keys);
                    
                    $keys = $redis->keys('db_query:*');
                    $stats['total_cached_queries'] = count($keys);
                    
                    $redisInfo = $redis->info();
                    $stats['redis_stats'] = [
                        'used_memory_human' => $redisInfo['used_memory_human'] ?? 'unknown',
                        'keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
                        'keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0,
                    ];
                } catch (\Exception $e) {
                    $stats['redis_error'] = $e->getMessage();
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get cache statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
} 