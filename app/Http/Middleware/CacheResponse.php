<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Cache'lenebilir HTTP response'lar için middleware
     */
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // Sadece GET istekleri cache'lenir
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Authenticated kullanıcılar için cache yapma
        if ($request->user()) {
            return $next($request);
        }

        // Cache key oluştur
        $cacheKey = 'response:' . md5($request->fullUrl());

        // Cache'den al
        $cachedResponse = cache()->get($cacheKey);
        
        if ($cachedResponse) {
            return response($cachedResponse['content'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Cache', 'HIT');
        }

        // Response'u al
        $response = $next($request);

        // Sadece başarılı response'ları cache'le
        if ($response->isSuccessful()) {
            cache()->put($cacheKey, [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
            ], $minutes * 60);
            
            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }
}
