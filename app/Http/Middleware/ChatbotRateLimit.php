<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiter for chatbot API with email-based bucketing for guests.
 *
 * Rate Limiting Strategy:
 * - Authenticated users: 60 messages/day per user_id
 * - Guests with email: 60 messages/day per email (allows family members on same IP)
 * - Guests without email: 30 messages/hour per guest token
 *
 * Design Notes:
 * - Email-based limiting solves "family of 6 on same IP" problem
 * - Email:token mapping cached for 24h to persist guest identification
 * - Guests without email get lower limits to discourage abuse
 * - Offering email reduces friction vs. forcing registration
 *
 * How it works:
 * 1. Auth users: simple per-user-id bucketing (60/day)
 * 2. Guests with email: per-email bucketing (60/day) - same as auth
 * 3. Guests without email: generate token, cache mapping (30/hour)
 *    - First time: token generated, mapping cached 24h
 *    - Returning guest: token from cache lookup, same bucket used
 *
 * Limitations:
 * - Cache-reliant: tokens lost if cache is cleared
 * - Email not verified: guests can provide any email
 * - Family members need different emails to get 60/day each
 */
class ChatbotRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getKey($request);
        $guestHasEmail = $request->filled('email');
        $limit = auth()->check() || $guestHasEmail ? 60 : 30;
        $window = auth()->check() || $guestHasEmail ? 1440 : 60; // minutes

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $retryAfter = RateLimiter::availableIn($key);
            $minutes = ceil($retryAfter / 60);

            return response()->json(
                $this->getRateLimitResponse($limit, $window, $retryAfter, $minutes, $guestHasEmail),
                429,
            );
        }

        RateLimiter::hit($key, $window * 60); // Convert to seconds

        return $next($request);
    }

    /**
     * Get rate limit key for this request.
     *
     * Priority:
     * 1. Auth users: user_id (60/day)
     * 2. Guests with email: email (60/day, shareable family limit)
     * 3. Guests without email: token (30/hour, minimal limit)
     */
    private function getKey(Request $request): string
    {
        if (auth()->check()) {
            return "chatbot:user:{$request->user()->id}";
        }

        if ($request->filled('email')) {
            return "chatbot:email:{$request->input('email')}";
        }

        return $this->getOrCreateGuestToken($request);
    }

    /**
     * Get or create guest token for IP-based rate limiting.
     *
     * On first request: generate token, cache IP→token mapping (24h)
     * On return: lookup token from cache, use same bucket
     */
    private function getOrCreateGuestToken(Request $request): string
    {
        $ip = $request->ip();
        $cacheKey = "chatbot:guest:ip:{$ip}";

        // Try to get existing token for this IP
        $token = Cache::get($cacheKey);

        if (!$token) {
            // Generate new token for this guest (IP)
            $token = 'guest_'.Str::random(32);
            Cache::put($cacheKey, $token, now()->addDay());
        }

        return "chatbot:token:{$token}";
    }

    /**
     * Build rate limit response with appropriate messaging and next steps.
     *
     * If guest is at 30/hour limit: offer email input to upgrade to 60/day.
     * If auth user or email guest is at 60/day limit: show login/account upsell.
     */
    private function getRateLimitResponse(
        int $limit,
        int $window,
        int $retryAfter,
        int $minutes,
        bool $guestHasEmail,
    ): array {
        if ($limit === 30 && !$guestHasEmail) {
            // Guest without email hit 30/hour limit
            return [
                'error' => "وصلت لحد الساعة ({$limit} رسالة). أدخل بريدك الإلكتروني للحصول على 60 رسالة يومياً.",
                'email_prompt' => true,
                'prompt_message' => 'أدخل بريدك الإلكتروني لزيادة الحد إلى 60 رسالة في اليوم',
                'fallback_option' => 'المتابعة بدون بريد إلكتروني (30 رسالة/ساعة)',
                'limit' => $limit,
                'window_minutes' => $window,
                'retry_after_seconds' => $retryAfter,
                'retry_after_minutes' => $minutes,
            ];
        }

        // Authenticated or email-verified guest at 60/day limit
        return [
            'error' => "وصلت للحد اليومي ({$limit} رسالة). سجّل الدخول لزيادة الحد إلى رسائل غير محدودة.",
            'upsell_message' => auth()->check()
                ? 'للمزيد من الرسائل، ترقّ حسابك الآن'
                : 'سجّل الدخول للحصول على المزيد من الرسائل',
            'signup_url' => route('register'),
            'login_url' => route('login'),
            'limit' => $limit,
            'window_minutes' => $window,
            'retry_after_seconds' => $retryAfter,
            'retry_after_minutes' => $minutes,
        ];
    }
}
