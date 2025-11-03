<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CacheManagerService;
use App\Enums\CacheType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller as BaseController;

final class CacheController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Show cache statistics
     */
    public function stats(): View
    {
        $cacheStats = $this->cacheManager->getCacheStats();

        return view('cache.stats', compact('cacheStats'));
    }

    /**
     * Get cache status
     */
    public function status(): JsonResponse
    {
        $stats = $this->cacheManager->getCacheStats();

        if (empty($stats)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get cache status'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Clear static content cache
     */
    public function clearStaticContent(): JsonResponse
    {
        try {
            $success = $this->cacheManager->clearCacheByType(CacheType::STATIC_CONTENT);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear static content cache: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Static content cache cleared successfully' : 'Failed to clear static content cache'
        ]);
    }

    /**
     * Clear image cache
     */
    public function clearImageCache(): JsonResponse
    {
        try {
            $success = $this->cacheManager->clearCacheByType(CacheType::IMAGES);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear image cache: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Image cache cleared successfully' : 'Failed to clear image cache'
        ]);
    }

    /**
     * Clear asset cache (CSS/JS)
     */
    public function clearAssetCache(): JsonResponse
    {
        try {
            $success = $this->cacheManager->clearCacheByType(CacheType::CSS_JS);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear asset cache: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Asset cache cleared successfully' : 'Failed to clear asset cache'
        ]);
    }

    /**
     * Clear avatar cache
     */
    public function clearAvatarCache(): JsonResponse
    {
        try {
            $success = $this->cacheManager->clearCacheByType(CacheType::AVATARS);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear avatar cache: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Avatar cache cleared successfully' : 'Failed to clear avatar cache'
        ]);
    }

    /**
     * Clear all cache
     */
    public function clearAll(): JsonResponse
    {
        $success = $this->cacheManager->clearAllCache();

        return response()->json([
            'success' => $success,
            'message' => $success ? 'All cache cleared successfully' : 'Failed to clear all cache'
        ]);
    }

    /**
     * Clear cache by specific user
     */
    public function clearUserCache(int $userId): JsonResponse
    {
        try {
            $success = $this->cacheManager->clearUserCache($userId);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to clear cache for user $userId: " . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? "Cache for user $userId cleared successfully" : "Failed to clear cache for user $userId"
        ]);
    }
}
