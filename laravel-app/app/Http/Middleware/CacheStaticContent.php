<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CacheService;
use Symfony\Component\HttpFoundation\Response;

class CacheStaticContent
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'static_page_';

    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldCache($request)) {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request);
        $cachedContent = $this->cacheService->getStaticContent($cacheKey);

        if ($cachedContent) {
            return response($cachedContent)->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        if ($this->isResponseCacheable($response)) {
            $this->cacheResponse($cacheKey, $response);
        }

        return $response;
    }

    private function shouldCache(Request $request): bool
    {
        return $request->isMethod('GET') &&
               auth()->guest() &&
               !$request->hasHeader('Authorization') &&
               !$request->is('cache/*') &&
               !$request->is('admin/*');
    }

    private function generateCacheKey(Request $request): string
    {
        return self::CACHE_PREFIX . hash('xxh3', $request->fullUrl());
    }

    private function isResponseCacheable(Response $response): bool
    {
        if (!$response->isSuccessful()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }

    private function cacheResponse(string $cacheKey, Response $response): void
    {
        $content = $response->getContent();
        if ($content !== false) {
            $this->cacheService->cacheStaticContent($cacheKey, $content, self::CACHE_TTL);
            $response->headers->set('X-Cache', 'MISS');
        }
    }
}
