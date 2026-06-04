<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use App\Http\Middleware\CheckSanctumAbilityOrSession;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('products')->middleware(CheckSanctumAbilityOrSession::class.':products.read')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('api.products.index');
        Route::get('{product}', [ProductController::class, 'show'])->name('api.products.show');
    });

    Route::prefix('products')->middleware(CheckSanctumAbilityOrSession::class.':products.write')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('api.products.store');
        Route::match(['put', 'patch'], '{product}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    });
});
