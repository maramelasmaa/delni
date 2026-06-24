<?php

use App\Http\Middleware\EnsureAccountNotLocked;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureProviderHasProfile;
use App\Http\Middleware\EnsureProviderRole;
use App\Http\Middleware\EnsureReviewEligible;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserNotSuspended;
use App\Http\Middleware\ForceApiArabicLocale;
use App\Http\Middleware\ProviderAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
        ]);

        $middleware->appendToGroup('api', ForceApiArabicLocale::class);
        $middleware->alias([
            'account.locked' => EnsureAccountNotLocked::class,
            'user.active' => EnsureUserIsActive::class,
            'user.not_suspended' => EnsureUserNotSuspended::class,
            'admin' => EnsureAdminRole::class,
            'provider' => EnsureProviderRole::class,
            'provider.authenticate' => ProviderAuthenticate::class,
            'provider.has_profile' => EnsureProviderHasProfile::class,
            'review.eligible' => EnsureReviewEligible::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في البيانات المدخلة.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالقيام بهذا الإجراء.',
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'العنصر غير موجود.',
                ], 404);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof AuthenticationException) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'يرجى تسجيل الدخول أولاً.',
                    ], 401);
                }

                if ($request->is('provider', 'provider/*')) {
                    return redirect()->route('filament.provider.auth.login');
                }

                $adminPath = trim((string) config('app.admin_path', 'cp/admin'), '/');
                if ($request->is($adminPath, $adminPath.'/*')) {
                    return redirect()->route('filament.admin.auth.login');
                }

                return redirect()->route('filament.provider.auth.login');
            }

            if ($request->is('cp/*')) {
                if ($e instanceof HttpException && $e->getStatusCode() < 500) {
                    return null;
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'An error occurred.',
                    ], 500);
                }

                return response(view('errors.panel', [
                    'exception' => $e,
                ]), 500);
            }
        });
    })->create();
