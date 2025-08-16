<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\DatabaseCacheService;
use App\Services\PageCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CacheController extends Controller
{
    public function __construct(
        protected CacheService $cacheService,
        protected DatabaseCacheService $databaseCacheService,
        protected PageCacheService $pageCacheService
    ) {}

    /**
     * Show cache statistics
     */
    public function stats(): View
    {
        $dbStats = $this->databaseCacheService->getCacheStatistics();
        $pageStats = $this->pageCacheService->getPageCacheStats();
        $combinedStats = $this->cacheService->getCombinedStats($dbStats, $pageStats);
        
        return view('cache.stats', compact('combinedStats'));
    }

    /**
     * Show detailed cache statistics
     */
    public function detailedStats(): View
    {
        $dbStats = $this->databaseCacheService->getCacheStatistics();
        $pageStats = $this->pageCacheService->getPageCacheStats();
        $combinedStats = $this->cacheService->getCombinedStats($dbStats, $pageStats);
        
        return view('cache.detailed-stats', compact('combinedStats'));
    }

    /**
     * Clear page cache
     */
    public function clearPages(Request $request): JsonResponse
    {
        $pattern = $request->input('pattern', 'page_cache:*');
        $result = $this->cacheService->clearPageCacheWithResponse($pattern);

        return response()->json($result);
    }

    /**
     * Clear database query cache
     */
    public function clearDatabaseCache(): JsonResponse
    {
        $result = $this->cacheService->clearDatabaseCacheWithResponse();

        return response()->json($result);
    }

    /**
     * Clear specific type of cache
     */
    public function clearCacheByType(Request $request): JsonResponse
    {
        $type = $request->input('type');
        
        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Cache type is required'
            ], 400);
        }

        if (!$this->cacheService->validateCacheType($type)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cache type'
            ], 400);
        }

        $result = $this->cacheService->clearCacheByTypeWithResponse($type);

        return response()->json($result);
    }

    /**
     * Clear all cache
     */
    public function clearAll(): JsonResponse
    {
        $result = $this->cacheService->clearAllCacheWithResponse();

        return response()->json($result);
    }

    /**
     * Get cache status
     */
    public function status(): JsonResponse
    {
        $status = $this->cacheService->getRedisStatus();
        
        if ($status['status'] === 'error') {
            return response()->json($status, 500);
        }
        
        return response()->json($status);
    }

    /**
     * Test Redis connection
     */
    public function test(): JsonResponse
    {
        $result = $this->cacheService->testRedisConnection();
        
        if ($result['status'] === 'error') {
            return response()->json($result, 500);
        }
        
        return response()->json($result);
    }

    /**
     * Get comprehensive cache overview
     */
    public function overview(): JsonResponse
    {
        $overview = $this->cacheService->getCacheOverview();
        
        if (empty($overview)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache overview'
            ], 500);
        }

        return response()->json($overview);
    }
}
