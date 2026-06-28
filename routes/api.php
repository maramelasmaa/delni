<?php

declare(strict_types=1);

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileSearchController;
use App\Http\Controllers\Api\V1\ProviderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', HealthController::class)->name('api.health');

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    // Public Marketplace Endpoints
    Route::get('/home', [HomeController::class, 'home'])->middleware('throttle:api.home')->name('api.home');
    Route::get('/cities', [HomeController::class, 'cities'])->name('api.cities');
    Route::get('/provider-types', [HomeController::class, 'providerTypes'])->name('api.provider-types');
    Route::get('/contact', [HomeController::class, 'contact'])->name('api.contact');

    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('api.categories.show');
    Route::get('/subcategories/{subcategory:slug}', [CategoryController::class, 'subcategory'])->name('api.subcategories.show');

    Route::get('/search/suggestions', [ProfileSearchController::class, 'suggestions'])
        ->middleware('throttle:api.suggestions')
        ->name('api.search.suggestions');

    Route::get('/search', [ProfileSearchController::class, 'search'])
        ->middleware('throttle:search')
        ->name('api.search');

    Route::get('/providers/{profile:slug}', [ProviderController::class, 'show'])->middleware('throttle:api.provider-detail')->name('api.providers.show');
    Route::get('/top-rated', [ProviderController::class, 'topRated'])->middleware('throttle:api.top-rated')->name('api.top-rated');
    Route::get('/providers/{profile:slug}/reviews', [ProviderController::class, 'reviews'])->name('api.providers.reviews');

    // Authentication Endpoints
    Route::prefix('auth')->name('api.auth.')->group(function (): void {
        Route::post('register', [ApiAuthController::class, 'register'])
            ->middleware('throttle:api.register')
            ->name('register');

        Route::post('login', [ApiAuthController::class, 'login'])
            ->middleware('throttle:api.login')
            ->name('login');

        Route::post('forgot-password', [ApiAuthController::class, 'forgotPassword'])
            ->middleware('throttle:api.forgot-password')
            ->name('forgot-password');

        Route::post('reset-password', [ApiAuthController::class, 'resetPassword'])
            ->middleware('throttle:api.reset-password')
            ->name('reset-password');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [ApiAuthController::class, 'me'])->name('me');
            Route::patch('profile', [ApiAuthController::class, 'updateProfile'])
                ->middleware('throttle:30,1')
                ->name('profile.update');
            Route::post('change-password', [ApiAuthController::class, 'changePassword'])
                ->middleware('throttle:api.change-password')
                ->name('change-password');
            Route::post('logout', [ApiAuthController::class, 'logout'])->name('logout');
            Route::delete('account', [ApiAuthController::class, 'deleteAccount'])->name('delete-account');
        });
    });

    // Protected Marketplace Actions
    Route::middleware([
        'auth:sanctum',
        'account.locked',
        'user.active',
        'user.not_suspended',
    ])->group(function (): void {
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
        Route::post('/favorites/{providerSlug}', [FavoriteController::class, 'store'])->name('api.favorites.store');
        Route::delete('/favorites/{providerSlug}', [FavoriteController::class, 'destroy'])->name('api.favorites.destroy');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');

        Route::post('/providers/{profile:slug}/reviews', [ProviderController::class, 'storeReview'])
            ->middleware(['review.eligible', 'throttle:reviews.create'])
            ->name('api.providers.reviews.store');

        Route::post('/reviews/{review}/flag', [ProviderController::class, 'flagReview'])
            ->middleware('throttle:reviews.flag')
            ->name('api.reviews.flag');
    });
});
