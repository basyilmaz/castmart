<?php

namespace CastMart\Tenant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CastMart\Tenant\Facades\Tenant;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Multi-tenant modu aktif değilse devam et
        if (!config('castmart-tenant.enabled', false)) {
            return $next($request);
        }

        if (!Tenant::check()) {
            return $next($request);
        }

        $tenant = Tenant::current();
        $limit = Tenant::checkLimit($tenant, $feature);

        if (!$limit['allowed']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'limit_exceeded',
                    'message' => $this->getLimitMessage($feature),
                    'current' => $limit['current'],
                    'limit' => $limit['limit'],
                    'upgrade_url' => route('tenant.upgrade'),
                ], 403);
            }

            return redirect()->back()->with('error', $this->getLimitMessage($feature));
        }

        return $next($request);
    }

    /**
     * Limit mesajını getir
     */
    protected function getLimitMessage(string $feature): string
    {
        $messages = [
            'max_products' => 'Ürün limitinize ulaştınız. Planınızı yükselterek daha fazla ürün ekleyebilirsiniz.',
            'max_orders_per_month' => 'Aylık sipariş limitinize ulaştınız. Planınızı yükselterek limiti artırabilirsiniz.',
            'max_users' => 'Kullanıcı limitinize ulaştınız. Planınızı yükselterek daha fazla kullanıcı ekleyebilirsiniz.',
        ];

        return $messages[$feature] ?? 'Plan limitinize ulaştınız. Lütfen planınızı yükseltin.';
    }
}
