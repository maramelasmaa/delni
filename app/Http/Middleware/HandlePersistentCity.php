<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\City;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlePersistentCity
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If request has ?clear_city=1, clear city from session and redirect to clean URL
        if ($request->has('clear_city')) {
            $request->session()->forget('active_city_slug');
            $url = $request->fullUrlWithQuery(['clear_city' => null]);

            return redirect($url);
        }

        // 2. If request has ?city=..., validate, store in session and redirect to clean URL
        if ($citySlug = $request->query('city')) {
            $city = City::where('slug', $citySlug)->where('is_active', true)->first();
            if ($city) {
                $request->session()->put('active_city_slug', $city->slug);
            }
            $url = $request->fullUrlWithQuery(['city' => null]);

            return redirect($url);
        }

        return $next($request);
    }
}
