<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('products')->middleware(CheckAbilities::class.':products.read')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('{product}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::prefix('products')->middleware(CheckAbilities::class.':products.write')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::match(['put', 'patch'], '{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
});
