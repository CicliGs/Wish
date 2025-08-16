<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PageCache
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            return $next($request);
        }

        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('api/*')) {
            return $next($request);
        }

        $cacheKey = 'page_cache:' . md5($request->fullUrl());

        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            return response($cachedResponse['content'])
                ->header('Content-Type', $cachedResponse['content_type'])
                ->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $response instanceof Response) {
            $content = $response->getContent();
            $contentType = $response->headers->get('Content-Type');

            $ttl = Config::get('cache.ttl', 300);

            Cache::put($cacheKey, [
                'content' => $content,
                'content_type' => $contentType,
            ], $ttl);

            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }
} 