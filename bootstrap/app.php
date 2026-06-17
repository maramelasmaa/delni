<?php

use App\Http\Middleware\EnsureAccountNotLocked;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureProviderHasActiveSubscription;
use App\Http\Middleware\EnsureProviderHasProfile;
use App\Http\Middleware\EnsureProviderRole;
use App\Http\Middleware\EnsureReviewEligible;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserNotSuspended;
use App\Http\Middleware\HandlePersistentCity;
use App\Http\Middleware\ProviderAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the reverse proxy / load balancer in front of the app so
        // $request->ip() resolves the real client (IP-keyed rate limiters) and
        // $request->secure() detects HTTPS terminated at the edge.
        // TRUSTED_PROXIES defaults to loopback for the common Nginx/Apache + PHP-FPM
        // single-host VPS setup; set it to the proxy CIDR (or '*' if unavoidable).
        $trustedProxies = env('TRUSTED_PROXIES', '127.0.0.1');
        $middleware->trustProxies(
            at: $trustedProxies === '*' ? '*' : array_map('trim', explode(',', $trustedProxies)),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
            HandlePersistentCity::class,
        ]);
        $middleware->alias([
            'account.locked' => EnsureAccountNotLocked::class,
            'user.active' => EnsureUserIsActive::class,
            'user.not_suspended' => EnsureUserNotSuspended::class,
            'admin' => EnsureAdminRole::class,
            'provider' => EnsureProviderRole::class,
            'provider.authenticate' => ProviderAuthenticate::class,
            'provider.has_profile' => EnsureProviderHasProfile::class,
            'provider.active_subscription' => EnsureProviderHasActiveSubscription::class,
            'review.eligible' => EnsureReviewEligible::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            // Handle authentication errors - redirect to appropriate login
            if ($e instanceof AuthenticationException) {
                if ($request->is('provider', 'provider/*')) {
                    return redirect()->route('filament.provider.auth.login');
                }

                $adminPath = env('FILAMENT_PATH', 'cp/admin');
                if ($request->is($adminPath, $adminPath.'/*')) {
                    return redirect()->route('filament.admin.auth.login');
                }

                return redirect()->route('login');
            }

            // Never expose public web from admin/provider panel errors
            if ($request->is('cp/*')) {
                // Let HTTP client exceptions (4xx/3xx) pass through - don't override them
                if ($e instanceof HttpException) {
                    // Let Filament/Laravel handle 4xx status codes normally (403, 404, etc.)
                    if ($e->getStatusCode() < 500) {
                        return null;
                    }
                }

                // Panel errors: simple error page without public navigation
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'An error occurred.',
                    ], 500);
                }

                // Return minimal error page for panel (5xx errors only)
                return response(view('errors.panel', [
                    'exception' => $e,
                ]), 500);
            }
        });
    })->create();
