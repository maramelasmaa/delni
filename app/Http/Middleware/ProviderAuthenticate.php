<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProviderAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow auth routes (login, logout, profile) to pass through
        if ($request->routeIs('filament.provider.auth.*')) {
            return $next($request);
        }

        if (! Auth::check()) {
            return redirect()->route('filament.provider.auth.login');
        }

        return $next($request);
    }
}
