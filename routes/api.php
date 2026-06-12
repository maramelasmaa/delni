<?php

use App\Http\Controllers\Api\ProfileSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/profiles/search', [ProfileSearchController::class, 'search'])
    ->middleware('throttle:search')
    ->name('api.profiles.search');
