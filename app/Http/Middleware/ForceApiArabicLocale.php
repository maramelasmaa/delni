<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ForceApiArabicLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ar');

        return $next($request);
    }
}
