<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FavoriteController;
use App\Http\Controllers\Public\FrontendController;
use App\Http\Controllers\Public\ReviewController;
use App\Http\Controllers\Public\SettingsController;
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
Route::get('/top-rated/in/{city:slug}', [FrontendController::class, 'topRatedInCity'])
    ->withoutScopedBindings()
    ->name('public.top-rated.city');
Route::get('/categories', [FrontendController::class, 'categories'])->name('public.categories');
Route::get('/category/{category:slug}', [FrontendController::class, 'category'])->name('public.category');
Route::get('/category/{category:slug}/in/{city:slug}', [FrontendController::class, 'categoryInCity'])
    ->withoutScopedBindings()
    ->name('public.category.city');
Route::get('/subcategory/{subcategory:slug}', [FrontendController::class, 'subcategory'])->name('public.subcategory');
Route::get('/subcategory/{subcategory:slug}/in/{city:slug}', [FrontendController::class, 'subcategoryInCity'])
    ->withoutScopedBindings()
    ->name('public.subcategory.city');
Route::get('/city/{city:slug}', [FrontendController::class, 'city'])->name('public.city');
Route::get('/providers/{profile:slug}', [FrontendController::class, 'provider'])->name('public.provider');
Route::middleware([
    'auth',
    'account.locked',
    'user.active',
    'user.not_suspended',
])->group(function (): void {
    Route::post('/providers/{profile:slug}/review', [ReviewController::class, 'store'])
        ->middleware(['review.eligible', 'throttle:reviews.create'])
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

    Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
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

    Route::redirect('/dashboard', '/settings')->name('dashboard');
    Route::delete('/account', [SettingsController::class, 'destroy'])->name('account.destroy');
});

Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
Route::view('/about', 'public.about')->name('about');

// Favorites — index is public (shows prompt for guests), toggle requires auth
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
Route::middleware([
    'auth',
    'account.locked',
    'user.active',
    'user.not_suspended',
])->group(function (): void {
    Route::post('/favorites/{profile}', [FavoriteController::class, 'toggle'])
        ->middleware('throttle:60,1')
        ->name('favorites.toggle');
});
