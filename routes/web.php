<?php

use App\Http\Controllers\Admin\ApiTokenManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\ReservationLogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservingController;
use App\Livewire\Pages\Admin\ProductIndex as AdminProductIndex;
use App\Livewire\Pages\ProductIndex;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', ProductIndex::class)->name('home');
    Route::get('dashboard', ProductIndex::class)->name('dashboard');


    Route::get('test', [testController::class, 'index'])->name('test.index');
    Route::get('products', ProductIndex::class)->name('products.index');

    Route::resource('carts', CartController::class)->only(['index']);
    Route::post('carts/items', [CartController::class, 'store'])->name('carts.items.store');
    Route::patch('carts/items/{cartItem}', [CartController::class, 'update'])->name('carts.items.update');
    Route::delete('carts/items/{cartItem}', [CartController::class, 'destroy'])->name('carts.items.destroy');
    Route::post('carts/checkout', [CartController::class, 'checkout'])->name('carts.checkout');

    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::patch('reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    Route::post('reservations/{reservation}/request-removal', [ReservationController::class, 'requestRemoval'])->name('reservations.request-removal');
    Route::post('reservations/{reservation}/confirm-returned', [ReservationController::class, 'confirmReturned'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservations.confirm-returned');
    Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservations.update-status');
    Route::post('reservation-orders/{reservationOrder}/confirm-returned', [ReservationController::class, 'confirmOrderReturned'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservation-orders.confirm-returned');
    Route::get('reservation-orders/{reservationOrder}/manage-items', [ReservingController::class, 'manageItems'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservation-orders.manage-items');

    Route::get('reserving-admin', [ReservingController::class, 'index'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reserving.index');

    Route::middleware('can:access-reserving-dashboard')->prefix('cms')->name('cms.')->group(function () {
        Route::get('products', AdminProductIndex::class)->name('products.index');
        Route::resource('products', ProductManagementController::class)->except(['index']);
        Route::post('categories', [ProductManagementController::class, 'storeCategory'])->name('categories.store');
        Route::delete('categories/{category}', [ProductManagementController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('reservation-logs', [ReservationLogController::class, 'index'])->name('reservation-logs.index');
        Route::get('api-tokens', [ApiTokenManagementController::class, 'index'])->name('api-tokens.index');
        Route::post('api-tokens', [ApiTokenManagementController::class, 'store'])->name('api-tokens.store');
        Route::delete('api-tokens/{token}', [ApiTokenManagementController::class, 'destroy'])->name('api-tokens.destroy');
    });

    Route::patch('reservation-removal-requests/{removalRequest}/status', [ReservingController::class, 'updateRemovalRequestStatus'])
        ->middleware('can:access-reserving-dashboard')
        ->name('reservation-removal-requests.update-status');
});

require __DIR__.'/settings.php';
