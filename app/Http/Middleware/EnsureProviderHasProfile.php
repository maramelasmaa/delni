<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderHasProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->hasRole('provider')) {
            return $next($request);
        }

        if ($user->profile) {
            return $next($request);
        }

        return redirect()->route('filament.provider.resources.provider-profiles.create');
    }
}
