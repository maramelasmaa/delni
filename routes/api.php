<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProfileSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/profiles/search', [ProfileSearchController::class, 'search'])
    ->middleware('throttle:search')
    ->name('api.profiles.search');

// Chatbot API routes
Route::prefix('chat')->middleware('chatbot.rate-limit')->group(function (): void {
    Route::get('/init', [ChatController::class, 'init'])->name('api.chat.init');
    Route::post('/message', [ChatController::class, 'message'])->name('api.chat.message');
    Route::post('/reset', [ChatController::class, 'reset'])->name('api.chat.reset');
});
