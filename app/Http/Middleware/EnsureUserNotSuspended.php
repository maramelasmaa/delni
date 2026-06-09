<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotSuspended
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

        if ($user->is_suspended) {
            // For POST review/flag routes, let form request validation handle suspension
            // to provide a 422 validation error instead of 403 authorization error
            if ($this->isReviewPostRoute($request)) {
                return $next($request);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, __('messages.account_suspended'));
        }

        return $next($request);
    }

    private function isReviewPostRoute(Request $request): bool
    {
        return $request->isMethod('post')
            && ($request->is('*/review') || $request->is('reviews/*'));
    }
}
