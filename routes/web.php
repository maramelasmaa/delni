<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FrontendController;
use App\Http\Controllers\Public\ReviewController;
use App\Models\Icon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Icon serving route
Route::get('/icon/{icon}', function (Icon $icon) {
    $path = Storage::disk('icons')->path($icon->file_path);
    $mimeType = $icon->format === 'svg' ? 'image/svg+xml' : "image/{$icon->format}";

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline',
    ]);
})->name('icon.show');

Route::get('/', [FrontendController::class, 'home'])->name('home');

// Provider panel root redirect - must come before Filament panels
Route::get('/provider', function () {
    // Valid authenticated provider - redirect to dashboard
    if (auth()->check() && auth()->user()->hasRole('provider') && auth()->user()->is_active && ! auth()->user()->is_suspended) {
        return redirect('/provider/dashboard');
    }

    // Unauthenticated - redirect to login
    if (! auth()->check()) {
        return redirect('/provider/login');
    }

    // Authenticated but doesn't meet requirements (wrong role, suspended, inactive)
    abort(403, 'Unauthorized to access provider panel');
})->name('provider.root');

// Favicon route
Route::get('/favicon.ico', fn () => response()->file(public_path('images/logo.jpg'), ['Content-Type' => 'image/jpeg']));

Route::get('/search', [FrontendController::class, 'search'])->name('public.search');
Route::get('/top-rated', [FrontendController::class, 'topRated'])->name('public.top-rated');
Route::get('/categories', [FrontendController::class, 'categories'])->name('public.categories');
Route::get('/category/{category:slug}', [FrontendController::class, 'category'])->name('public.category');
Route::get('/subcategory/{subcategory:slug}', [FrontendController::class, 'subcategory'])->name('public.subcategory');
Route::get('/city/{city:slug}', [FrontendController::class, 'city'])->name('public.city');
Route::get('/providers/{profile:slug}', [FrontendController::class, 'provider'])->name('public.provider');
Route::middleware([
    'auth',
    'account.locked',
    'user.active',
    'user.not_suspended',
])->group(function (): void {
    Route::post('/providers/{profile:slug}/review', [ReviewController::class, 'store'])
        ->middleware(['password.changed', 'review.eligible', 'throttle:reviews.create'])
        ->name('review.store');

    Route::post('/reviews/{review}/flag', [ReviewController::class, 'flag'])
        ->middleware('throttle:reviews.flag')
        ->name('reviews.flag');
});
Route::get('/locale/{locale}', [FrontendController::class, 'switchLocale'])->name('locale.switch');

// Legal pages
Route::view('/privacy', 'public.legal.privacy')->name('privacy');
Route::view('/terms', 'public.legal.terms')->name('terms');
Route::view('/disclaimer', 'public.legal.disclaimer')->name('disclaimer');

// Contact page
Route::get('/contact', [ContactController::class, 'show'])->name('contact');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:register');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
        ->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:password.request')
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:password.reset')
        ->name('password.update');
});

Route::get('/onboarding/{token}', [OnboardingController::class, 'showSetPasswordForm'])
    ->name('onboarding.show');
Route::post('/onboarding/set-password', [OnboardingController::class, 'setPassword'])
    ->middleware('throttle:onboarding.set-password')
    ->name('onboarding.set-password');

// Debug: Display onboarding link — local environment only
if (app()->environment('local')) {
    Route::get('/onboarding-test/{token}', fn (string $token) => view('onboarding-link', [
        'onboardingUrl' => route('onboarding.show', $token),
    ]))->name('onboarding.test');
}

// Authenticated routes
Route::middleware([
    'auth',
    'account.locked',
    'user.active',
    'user.not_suspended',
])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Account edit for regular users (providers manage account info in Filament)
    Route::get('/account/edit', [AuthController::class, 'showAccountEditForm'])->name('account.edit');
    Route::post('/account/update', [AuthController::class, 'updateAccount'])->name('account.update');

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});
