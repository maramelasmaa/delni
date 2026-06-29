<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasAnyRole(['super_admin', 'app_review_moderator'])) {
            abort(403, 'Unauthorized access to admin resources.');
        }

        return $next($request);
    }
}
