<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiSecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limit kontrolü
        if (!$this->checkRateLimit($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Çok fazla istek gönderildi. Lütfen bekleyin.',
                'retry_after' => 60,
            ], 429);
        }

        // API Key doğrulama (public API için)
        if ($request->is('api/*') && !$this->validateApiKey($request)) {
            Log::warning('Invalid API key attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz API anahtarı.',
            ], 401);
        }

        // Request imza doğrulama (webhook'lar için)
        if ($request->is('api/webhooks/*') && !$this->validateSignature($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz imza.',
            ], 401);
        }

        // IP whitelist kontrolü (admin API için)
        if ($request->is('api/admin/*') && !$this->checkIpWhitelist($request)) {
            Log::warning('IP not in whitelist', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erişim reddedildi.',
            ], 403);
        }

        $response = $next($request);

        // Güvenlik header'ları ekle
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Rate limit kontrolü
     */
    protected function checkRateLimit(Request $request): bool
    {
        $key = 'api_rate_limit:' . $request->ip();
        $limit = config('api.rate_limit', 60); // Dakikada 60 istek
        $window = 60; // 1 dakika

        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            return false;
        }

        Cache::put($key, $current + 1, $window);
        return true;
    }

    /**
     * API Key doğrulama
     */
    protected function validateApiKey(Request $request): bool
    {
        // Bearer token veya X-API-Key header
        $apiKey = $request->bearerToken() ?? $request->header('X-API-Key');

        if (!$apiKey) {
            // Public endpoint'lere izin ver
            $publicEndpoints = config('api.public_endpoints', [
                'api/health',
                'api/status',
            ]);

            foreach ($publicEndpoints as $endpoint) {
                if ($request->is($endpoint)) {
                    return true;
                }
            }

            return false;
        }

        // API key'i veritabanından veya config'den doğrula
        $validKeys = config('api.keys', []);
        
        if (in_array($apiKey, $validKeys)) {
            return true;
        }

        // Veritabanında kontrol (api_keys tablosu varsa)
        if (\Schema::hasTable('api_keys')) {
            return \DB::table('api_keys')
                ->where('key', hash('sha256', $apiKey))
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->exists();
        }

        return false;
    }

    /**
     * Webhook imza doğrulama
     */
    protected function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        
        if (!$signature) {
            return false;
        }

        $secret = config('api.webhook_secret');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * IP whitelist kontrolü
     */
    protected function checkIpWhitelist(Request $request): bool
    {
        $whitelist = config('api.ip_whitelist', []);

        // Boş whitelist = tüm IP'lere izin
        if (empty($whitelist)) {
            return true;
        }

        $clientIp = $request->ip();

        foreach ($whitelist as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IP eşleşme kontrolü (CIDR desteği ile)
     */
    protected function ipMatches(string $ip, string $pattern): bool
    {
        // Tam eşleşme
        if ($ip === $pattern) {
            return true;
        }

        // CIDR notasyonu (örn: 192.168.1.0/24)
        if (str_contains($pattern, '/')) {
            [$subnet, $bits] = explode('/', $pattern);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask;
            return ($ip & $mask) === $subnet;
        }

        // Wildcard (örn: 192.168.1.*)
        if (str_contains($pattern, '*')) {
            $pattern = str_replace('.', '\.', $pattern);
            $pattern = str_replace('*', '.*', $pattern);
            return (bool) preg_match('/^' . $pattern . '$/', $ip);
        }

        return false;
    }

    /**
     * Güvenlik header'ları ekle
     */
    protected function addSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
