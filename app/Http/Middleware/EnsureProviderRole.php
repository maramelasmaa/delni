<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip role check for auth routes (login, logout, reset password, etc)
        // Match: filament.provider.auth.*, filament.provider.pages.auth.*, and any Livewire auth endpoint
        if ($request->routeIs('filament.provider.auth.*', 'filament.provider.pages.auth.*')) {
            return $next($request);
        }

        // Also skip if the request path contains /login, /logout, or /auth
        if ($request->is('provider/login*', 'provider/logout*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null || ! $user->hasRole('provider')) {
            throw new AuthorizationException(__('messages.provider_access_denied'));
        }

        return $next($request);
    }
}
