<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ar');
        $request->session()->put('locale', 'ar');
        cookie()->queue('locale', 'ar', 60 * 24 * 365);

        return $next($request);
    }
}
