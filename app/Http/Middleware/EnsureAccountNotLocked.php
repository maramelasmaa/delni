<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountNotLocked
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip auth routes
        if ($request->routeIs('filament.*.auth.*')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->locked_until !== null && Carbon::parse($user->locked_until)->isFuture()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.account_locked')], 401);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.provider.auth.login')
                ->with('error', __('messages.account_locked'));
        }

        return $next($request);
    }
}
