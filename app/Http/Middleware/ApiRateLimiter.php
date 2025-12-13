<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;

class ApiRateLimiter
{
    /**
     * Rate limiting middleware
     */
    public function handle(Request $request, Closure $next, string $limiterKey = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiterKey);
        $maxAttempts = $this->getMaxAttempts($limiterKey);
        $decayMinutes = $this->getDecayMinutes($limiterKey);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $maxAttempts,
            RateLimiter::remaining($key, $maxAttempts)
        );
    }

    /**
     * İstek imzası oluştur
     */
    protected function resolveRequestSignature(Request $request, string $limiterKey): string
    {
        $identifier = $request->user()?->id ?? $request->ip();
        
        return sha1($limiterKey . '|' . $identifier);
    }

    /**
     * Maksimum deneme sayısı
     */
    protected function getMaxAttempts(string $limiterKey): int
    {
        $limits = [
            'api' => 60,           // 60 istek/dakika
            'auth' => 5,           // 5 deneme/dakika (login)
            'password' => 3,       // 3 deneme/dakika (şifre sıfırlama)
            'otp' => 3,            // 3 deneme/dakika (OTP)
            'search' => 30,        // 30 arama/dakika
            'checkout' => 10,      // 10 checkout/dakika
            'webhook' => 100,      // 100 webhook/dakika
        ];

        return $limits[$limiterKey] ?? 60;
    }

    /**
     * Bekleme süresi (dakika)
     */
    protected function getDecayMinutes(string $limiterKey): int
    {
        $decays = [
            'api' => 1,
            'auth' => 5,           // 5 dakika bekle
            'password' => 15,      // 15 dakika bekle
            'otp' => 5,
            'search' => 1,
            'checkout' => 1,
            'webhook' => 1,
        ];

        return $decays[$limiterKey] ?? 1;
    }

    /**
     * Rate limit aşıldı response
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Çok fazla istek gönderdiniz. Lütfen bekleyin.',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Rate limit header'ları ekle
     */
    protected function addRateLimitHeaders(Response $response, int $maxAttempts, int $remaining): Response
    {
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);
    }
}
