<?php

use App\Http\Controllers\GamePlayerRatingController;
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
    Route::patch('/players/{id}/restore', [PlayerController::class, 'restore'])->name('players.restore');

    // index, create, store, show, edit, update, destroy
    Route::resource('ratings', RatingController::class);

    // index, create, store, show, edit, update, destroy
    Route::resource('game_player_ratings', GamePlayerRatingController::class);
});

// Signed route for players to rate others
Route::get('games/{game}/rate/{player}', [RatingController::class, 'showForm'])
    ->name('players.rate')
    ->middleware('signed');

// Signed route for players that finished rating others
Route::get('games/{game}/rate/{player}/confirm', [RatingController::class, 'showConfirmation'])
    ->name('ratings.confirm')
    ->middleware('signed');

// Store player ratings
Route::post('games/{game}/players/{player}/rate', [RatingController::class, 'store'])
    ->name('ratings.store')
    ->middleware('signed');

// Authentication routes
Auth::routes();

// Home page route after login
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
