<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Optimization Settings
    |--------------------------------------------------------------------------
    |
    | This file contains optimized TTL settings for different types of content
    | to improve performance and reduce database load.
    |
    */

    'ttl' => [
        'pages' => [
            'static' => env('CACHE_TTL_STATIC_PAGES', 28800),
            'frequent' => env('CACHE_TTL_FREQUENT_PAGES', 14400),
            'dynamic' => env('CACHE_TTL_DYNAMIC_PAGES', 7200),
            'user' => env('CACHE_TTL_USER_PAGES', 3600),
            'search' => env('CACHE_TTL_SEARCH_PAGES', 900),
        ],

        'database' => [
            'heavy_queries' => env('CACHE_TTL_HEAVY_QUERIES', 1800),
            'complex_joins' => env('CACHE_TTL_COMPLEX_JOINS', 2400),
            'count_queries' => env('CACHE_TTL_COUNT_QUERIES', 1200),
            'group_by_queries' => env('CACHE_TTL_GROUP_BY_QUERIES', 1800),
            'subqueries' => env('CACHE_TTL_SUBQUERIES', 1500),
            'aggregate_data' => env('CACHE_TTL_AGGREGATE_DATA', 3600),
            'search_results' => env('CACHE_TTL_SEARCH_RESULTS', 900),
        ],

        'user_content' => [
            'profiles' => env('CACHE_TTL_USER_PROFILES', 3600),
            'wish_lists' => env('CACHE_TTL_WISH_LISTS', 2400),
            'friends' => env('CACHE_TTL_FRIENDS', 1800),
            'achievements' => env('CACHE_TTL_ACHIEVEMENTS', 7200),
            'statistics' => env('CACHE_TTL_STATISTICS', 600),
        ],

        'media' => [
            'avatars' => env('CACHE_TTL_AVATARS', 14400),
            'images' => env('CACHE_TTL_IMAGES', 28800),
            'files' => env('CACHE_TTL_FILES', 86400),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Strategy Settings
    |--------------------------------------------------------------------------
    |
    | Settings for different caching strategies and optimizations
    |
    */

    'strategy' => [
        'auto_page_type_detection' => env('CACHE_AUTO_PAGE_TYPE_DETECTION', true),
        
        'auto_query_type_detection' => env('CACHE_AUTO_QUERY_TYPE_DETECTION', true),
        
        'cache_slow_queries' => env('CACHE_SLOW_QUERIES', true),
        'slow_query_threshold' => env('CACHE_SLOW_QUERY_THRESHOLD', 100),
        
        'cache_complex_queries' => env('CACHE_COMPLEX_QUERIES', true),
        'cache_join_queries' => env('CACHE_JOIN_QUERIES', true),
        'cache_count_queries' => env('CACHE_COUNT_QUERIES', true),
        'cache_group_by_queries' => env('CACHE_GROUP_BY_QUERIES', true),
        'cache_subqueries' => env('CACHE_SUBQUERIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Invalidation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for cache invalidation and cleanup
    |
    */

    'invalidation' => [
        'auto_cleanup' => env('CACHE_AUTO_CLEANUP', true),
        
        'cleanup_ttl' => env('CACHE_CLEANUP_TTL', 86400),
        
        'patterns' => [
            'pages' => 'page_cache:*',
            'database' => 'db_query:*',
            'user_content' => 'user_*:*',
            'search' => 'search_*:*',
            'aggregate' => 'aggregate:*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for cache performance monitoring
    |
    */

    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),
        
        'log_hits' => env('CACHE_LOG_HITS', true),
        
        'log_misses' => env('CACHE_LOG_MISSES', true),
        
        'log_slow_operations' => env('CACHE_LOG_SLOW_OPERATIONS', true),
        'slow_operation_threshold' => env('CACHE_SLOW_OPERATION_THRESHOLD', 50),
    ],

]; 