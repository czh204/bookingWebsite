<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');

// Throttled: each prompt costs an API call, and the widget is open to guests.
Route::post('/chat', ChatController::class)
    ->middleware('throttle:20,1')
    ->name('chat.send');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
 
    Route::get('/register', [AuthController::class, 'show'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});
 
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});