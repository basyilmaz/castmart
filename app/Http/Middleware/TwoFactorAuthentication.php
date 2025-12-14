<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TwoFactorAuthService;

class TwoFactorAuthentication
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $guard = 'customer'): Response
    {
        $user = $this->getUser($guard);

        if (!$user) {
            return $next($request);
        }

        // 2FA etkin değilse devam et
        if (!$user->two_factor_enabled) {
            return $next($request);
        }

        // 2FA zaten doğrulandıysa devam et
        if ($this->twoFactorService->isTwoFactorConfirmed($guard, $user->id)) {
            return $next($request);
        }

        // 2FA doğrulama sayfasına yönlendir
        if ($guard === 'admin') {
            return redirect()->route('admin.two-factor.challenge');
        }

        return redirect()->route('customer.two-factor.challenge');
    }

    /**
     * Guard'a göre kullanıcı al
     */
    protected function getUser(string $guard)
    {
        if ($guard === 'admin') {
            return auth()->guard('admin')->user();
        }

        return auth()->guard('customer')->user();
    }
}
