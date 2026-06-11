<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiter for chatbot API.
 *
 * Limits:
 * - Guests: 30 messages/hour
 * - Authenticated: 60 messages/day
 */
class ChatbotRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        $limit = auth()->check() ? 60 : 30;
        $window = auth()->check() ? 1440 : 60; // minutes

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $retryAfter = RateLimiter::availableIn($key);
            $minutes = ceil($retryAfter / 60);

            return response()->json([
                'error' => auth()->check()
                    ? "تم الوصول لحد الاستخدام اليومي ({$limit} رسالة). يرجى المحاولة بعد {$minutes} دقيقة."
                    : "تم الوصول لحد الاستخدام بالساعة ({$limit} رسالة). يرجى المحاولة بعد {$minutes} دقيقة.",
                'limit' => $limit,
                'window_minutes' => $window,
                'retry_after_seconds' => $retryAfter,
                'retry_after_minutes' => $minutes,
            ], 429);
        }

        RateLimiter::hit($key, $window * 60); // Convert to seconds

        return $next($request);
    }

    /**
     * Get rate limit key for this request.
     */
    private function getKey(Request $request): string
    {
        if (auth()->check()) {
            return "chatbot:user:{$request->user()->id}";
        }

        return "chatbot:ip:{$request->ip()}";
    }
}
