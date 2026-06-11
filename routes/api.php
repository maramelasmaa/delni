<?php

use App\Http\Controllers\Api\ProfileSearchController;
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/profiles/search', [ProfileSearchController::class, 'search'])
    ->middleware('throttle:search')
    ->name('api.profiles.search');

Route::prefix('chat')->name('api.chat.')->group(function (): void {
    Route::get('/init', [ChatController::class, 'init'])->name('init');
    Route::post('/message', [ChatController::class, 'message'])->name('message');
    Route::post('/reset', [ChatController::class, 'reset'])->name('reset');
});
