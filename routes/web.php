<?php

use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\ReservationLogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', ])->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('home');
    Route::get('dashboard', [ProductController::class, 'index'])->name('dashboard');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    Route::resource('carts', CartController::class)->only(['index']);
    Route::post('carts/items', [CartController::class, 'store'])->name('carts.items.store');
    Route::patch('carts/items/{cartItem}', [CartController::class, 'update'])->name('carts.items.update');
    Route::delete('carts/items/{cartItem}', [CartController::class, 'destroy'])->name('carts.items.destroy');
    Route::post('carts/checkout', [CartController::class, 'checkout'])->name('carts.checkout');

    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::post('reservations/{reservation}/confirm-returned', [ReservationController::class, 'confirmReturned'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservations.confirm-returned');
    Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservations.update-status');
    Route::post('reservation-orders/{reservationOrder}/confirm-returned', [ReservationController::class, 'confirmOrderReturned'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservation-orders.confirm-returned');

    Route::get('reserving', [ReservingController::class, 'index'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reserving.index');

    Route::middleware('can:access-reserving-dashboard')->prefix('cms')->name('cms.')->group(function () {
        Route::resource('products', ProductManagementController::class);
        Route::get('reservation-logs', [ReservationLogController::class, 'index'])->name('reservation-logs.index');
    });
});

require __DIR__.'/settings.php';
