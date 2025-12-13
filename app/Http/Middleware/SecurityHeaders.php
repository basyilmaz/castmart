<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Güvenlik header'ları ekle
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $csp = $this->buildCsp();
        $response->headers->set('Content-Security-Policy', $csp);

        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Type Options (MIME sniffing koruması)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Frame Options (clickjacking koruması)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS (sadece production'da)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Cache Control (hassas sayfalar için)
        if ($this->isSensitivePage($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    /**
     * Content Security Policy oluştur
     */
    protected function buildCsp(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://www.google-analytics.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.iyzipay.com https://*.trendyol.com wss:",
            "frame-src 'self' https://www.youtube.com https://player.vimeo.com",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ];

        return implode('; ', $policies);
    }

    /**
     * Hassas sayfa kontrolü
     */
    protected function isSensitivePage(Request $request): bool
    {
        $sensitivePaths = [
            'admin/*',
            'customer/account/*',
            'checkout/*',
            'api/auth/*',
        ];

        foreach ($sensitivePaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
