<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AttractionController;
use App\Http\Controllers\FlightController;
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
    
Route::get('/attractions', [AttractionController::class, 'index'])->name('attractions.index');
Route::get('/flights', [FlightController::class, 'index'])->name('flights.index');

// ---------- Cart ----------
// Open to guests: browsing and gathering a cart needs no account. The
// cart lives in the session, so it survives signing in and is still
// there on the other side.
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::delete('/cart/{lineId}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo');

// ---------- Checkout ----------
// Signed in only. Every order belongs to a user, so paying — and reading
// back the confirmation, which carries an email and billing address —
// both require an account.
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/confirmation/{reference}', [CheckoutController::class, 'confirmation'])
        ->name('checkout.confirmation');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
 
    Route::get('/register', [AuthController::class, 'show'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});
 
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});