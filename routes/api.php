<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatControllerV2;
use App\Http\Controllers\Api\ChatControllerV3;
use App\Http\Controllers\Api\ProfileSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/profiles/search', [ProfileSearchController::class, 'search'])
    ->middleware('throttle:search')
    ->name('api.profiles.search');

// Chatbot API routes (V1 - Legacy)
Route::prefix('chat')->middleware('chatbot.rate-limit')->group(function (): void {
    Route::get('/init', [ChatController::class, 'init'])->name('api.chat.init');
    Route::post('/message', [ChatController::class, 'message'])->name('api.chat.message');
    Route::post('/reset', [ChatController::class, 'reset'])->name('api.chat.reset');
});

// Chatbot API routes (V2 - Intent-driven)
Route::prefix('chat/v2')->middleware('chatbot.rate-limit')->group(function (): void {
    Route::get('/init', [ChatControllerV2::class, 'init'])->name('api.chat.v2.init');
    Route::post('/message', [ChatControllerV2::class, 'message'])->name('api.chat.v2.message');
    Route::post('/reset', [ChatControllerV2::class, 'reset'])->name('api.chat.v2.reset');
});

// Chatbot API routes (V3 - Conversational stateful)
Route::prefix('chat/v3')->middleware('chatbot.rate-limit')->group(function (): void {
    Route::get('/init', [ChatControllerV3::class, 'init'])->name('api.chat.v3.init');
    Route::post('/message', [ChatControllerV3::class, 'message'])->name('api.chat.v3.message');
    Route::post('/reset', [ChatControllerV3::class, 'reset'])->name('api.chat.v3.reset');
});
