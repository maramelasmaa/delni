<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Review;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnsureReviewEligible
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (Carbon::parse($user->created_at)->diffInHours(Carbon::now()) < 24) {
            return redirect()->back()
                ->with('error', __('messages.public.account_too_new'));
        }

        $dailyCount = Review::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [
                Carbon::today()->startOfDay(),
                Carbon::today()->endOfDay(),
            ])
            ->count();

        if ($dailyCount >= 10) {
            return redirect()->back()
                ->with('error', __('messages.public.review_daily_limit_reached'));
        }

        return $next($request);
    }
}
