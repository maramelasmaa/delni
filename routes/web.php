<?php

use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\IconController;
use App\Http\Controllers\ProviderRootController;
use App\Http\Middleware\EnsureAccountNotLocked;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserNotSuspended;
use Illuminate\Support\Facades\Route;

Route::get('/icon/{icon}', IconController::class)->name('icon.show');

Route::get('/provider', ProviderRootController::class)->name('provider.root');

Route::get('/favicon.ico', FaviconController::class);

Route::middleware(['auth', EnsureAccountNotLocked::class, EnsureUserIsActive::class, EnsureUserNotSuspended::class])
    ->group(function (): void {
        Route::redirect('/dashboard', '/provider/dashboard')->name('dashboard');
    });

Route::get('/onboarding/{token}', [OnboardingController::class, 'showSetPasswordForm'])
    ->middleware('throttle:onboarding.show')
    ->name('onboarding.show');
Route::post('/onboarding/set-password', [OnboardingController::class, 'setPassword'])
    ->middleware('throttle:onboarding.set-password')
    ->name('onboarding.set-password');

if (app()->environment('local')) {
    Route::get('/onboarding-test/{token}', fn (string $token) => view('onboarding-link', [
        'onboardingUrl' => route('onboarding.show', $token),
    ]))->name('onboarding.test');
}
