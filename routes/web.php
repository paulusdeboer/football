<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RatingController;

// Redirect root URL to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // index, create, store, show, edit, update, destroy
    Route::resource('games', GameController::class);
    Route::get('/games/{game}/enter-result', [GameController::class, 'enterResult'])->name('games.enter-result');
    Route::post('/games/{game}/results', [GameController::class, 'storeResult'])->name('games.store-result');

    // index, create, store, show, edit, update, destroy
    Route::resource('players', PlayerController::class);
});

// Signed route for players to rate others (without needing to be logged in)
Route::get('games/{game}/rate/{player}', [RatingController::class, 'showForm'])
    ->name('players.rate')
    ->middleware('signed');

// Store player ratings (requires authentication)
Route::post('games/{game}/players/{player}/rate', [RatingController::class, 'store'])
    ->name('players.rate.store')
    ->middleware('auth');

// Authentication routes
Auth::routes();

// Home page route after login
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
