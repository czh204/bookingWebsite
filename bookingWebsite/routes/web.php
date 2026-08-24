<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');

// Throttled: each prompt costs an API call, and the widget is open to guests.
Route::post('/chat', [ChatController::class, '__invoke'])
    ->middleware('throttle:20,1')
    ->name('chat.send');

// Replays the session's conversation so the widget survives navigation.
// Read-only and DB-only, so it needs a looser limit than the prompt route
// it fires once per page load.
Route::get('/chat/history', [ChatController::class, 'history'])
    ->middleware('throttle:60,1')
    ->name('chat.history');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
 
    Route::get('/register', [AuthController::class, 'show'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});
 
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});