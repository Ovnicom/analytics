<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $allowedRoutes = [
            'login',
            'logout',
            'password.request',
            'password.reset',
            '2fa.setup',
            '2fa.setup.confirm',
            '2fa.verify',
            '2fa.validate',
            'profile.edit',
            'profile.update',
        ];

        if (in_array($request->route()?->getName(), $allowedRoutes)) {
            return $next($request);
        }

        if (!$user->two_factor_secret || !$user->two_factor_confirmed) {
            return redirect()->route('2fa.setup');
        }

        if (!session('2fa_verified')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
