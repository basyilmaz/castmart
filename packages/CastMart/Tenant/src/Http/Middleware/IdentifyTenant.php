<?php

namespace CastMart\Tenant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CastMart\Tenant\Facades\Tenant;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Multi-tenant modu aktif değilse devam et
        if (!config('castmart-tenant.enabled', false)) {
            return $next($request);
        }

        $tenant = $this->identifyTenant($request);

        if (!$tenant) {
            // Tenant bulunamadı
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tenant not found',
                    'message' => 'Mağaza bulunamadı veya aktif değil.',
                ], 404);
            }

            return redirect()->route('tenant.not-found');
        }

        // Tenant aktif mi kontrol et
        if (!$tenant->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tenant inactive',
                    'message' => 'Mağaza askıya alınmış veya abonelik süresi dolmuş.',
                ], 403);
            }

            return redirect()->route('tenant.suspended');
        }

        // Tenant'ı ayarla
        Tenant::set($tenant);

        // Request'e tenant bilgisi ekle
        $request->merge(['_tenant' => $tenant]);

        return $next($request);
    }

    /**
     * Tenant'ı belirle
     */
    protected function identifyTenant(Request $request): ?\CastMart\Tenant\Models\Tenant
    {
        $method = config('castmart-tenant.identification', 'subdomain');

        return match ($method) {
            'subdomain' => $this->identifyBySubdomain($request),
            'domain' => $this->identifyByDomain($request),
            'path' => $this->identifyByPath($request),
            'header' => $this->identifyByHeader($request),
            default => null,
        };
    }

    /**
     * Subdomain ile tanımla
     */
    protected function identifyBySubdomain(Request $request): ?\CastMart\Tenant\Models\Tenant
    {
        $host = $request->getHost();
        $centralDomain = config('castmart-tenant.central_domain');

        // Ana domain ise tenant yok
        if ($host === $centralDomain || $host === 'www.' . $centralDomain) {
            return null;
        }

        // Subdomain'i çıkar
        $subdomain = str_replace('.' . $centralDomain, '', $host);
        
        // www kontrolü
        if (str_starts_with($subdomain, 'www.')) {
            $subdomain = substr($subdomain, 4);
        }

        if (empty($subdomain) || $subdomain === $host) {
            return null;
        }

        return Tenant::findBySubdomain($subdomain);
    }

    /**
     * Domain ile tanımla
     */
    protected function identifyByDomain(Request $request): ?\CastMart\Tenant\Models\Tenant
    {
        $domain = $request->getHost();

        // www'yi kaldır
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }

        return Tenant::findByDomain($domain);
    }

    /**
     * Path ile tanımla
     */
    protected function identifyByPath(Request $request): ?\CastMart\Tenant\Models\Tenant
    {
        $segments = $request->segments();
        
        if (empty($segments)) {
            return null;
        }

        $tenantSlug = $segments[0];
        
        return Tenant::findBySubdomain($tenantSlug);
    }

    /**
     * Header ile tanımla
     */
    protected function identifyByHeader(Request $request): ?\CastMart\Tenant\Models\Tenant
    {
        $tenantId = $request->header('X-Tenant-ID');

        if (!$tenantId) {
            return null;
        }

        return \CastMart\Tenant\Models\Tenant::active()->find($tenantId);
    }
}
