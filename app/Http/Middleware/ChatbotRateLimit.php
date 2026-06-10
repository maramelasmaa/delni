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
 * - Guests: 10 messages/hour
 * - Authenticated: 50 messages/day
 */
class ChatbotRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        $limit = auth()->check() ? 50 : 10;
        $window = auth()->check() ? 1440 : 60; // minutes

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'error' => 'تم تجاوز الحد المسموح من الرسائل. يرجى المحاولة لاحقاً.',
                'retry_after' => RateLimiter::availableIn($key),
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
