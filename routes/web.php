<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('carts', [CartController::class, 'index'])->name('carts.index');
    Route::post('carts/items', [CartController::class, 'store'])->name('carts.items.store');
    Route::patch('carts/items/{cartItem}', [CartController::class, 'update'])->name('carts.items.update');
    Route::delete('carts/items/{cartItem}', [CartController::class, 'destroy'])->name('carts.items.destroy');
    Route::post('carts/checkout', [CartController::class, 'checkout'])->name('carts.checkout');
    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');

    Route::softDeletableResources([
        'products' => ProductController::class,
    ]);
});

require __DIR__.'/settings.php';
