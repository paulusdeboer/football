<?php

use App\Http\Controllers\GamePlayerRatingController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PlayerController;
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
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.resend');
    Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Home page route after login
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
