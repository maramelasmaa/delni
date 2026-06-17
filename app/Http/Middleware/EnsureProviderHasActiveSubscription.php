<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('filament.provider.auth.*', 'filament.provider.pages.auth.*')) {
            return $next($request);
        }

        if ($request->is('provider/login*', 'provider/logout*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $accessEndsAt = $user->profile?->provider_access_ends_at;

        if ($accessEndsAt === null || $accessEndsAt->isPast()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->flash('subscription_expired', true);

            return redirect()->route('filament.provider.auth.login');
        }

        return $next($request);
    }
}
