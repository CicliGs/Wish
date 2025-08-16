<?php

declare(strict_types=1);

namespace App\Services;

class PageCacheService
{
    private const TTL_FREQUENT_PAGES = 14400;
    private const TTL_STATIC_PAGES = 28800;
    private const TTL_DYNAMIC_PAGES = 7200;
    private const TTL_USER_PAGES = 3600;
    private const TTL_SEARCH_PAGES = 900;

    /**
     * Get page cache statistics
     */
    public function getPageCacheStats(): array
    {
        return [
            'ttl_settings' => [
                'frequent_pages' => self::TTL_FREQUENT_PAGES,
                'static_pages' => self::TTL_STATIC_PAGES,
                'dynamic_pages' => self::TTL_DYNAMIC_PAGES,
                'user_pages' => self::TTL_USER_PAGES,
                'search_pages' => self::TTL_SEARCH_PAGES,
            ],
            'ttl_improvements' => [
                'frequent_pages_new' => '4h for very frequent pages',
                'static_pages_new' => '8h for static content',
                'dynamic_pages_increased' => '2x (1h → 2h)',
                'user_pages_increased' => '2x (30m → 1h)',
                'search_pages_increased' => '3x (5m → 15m)',
            ],
        ];
    }
} 