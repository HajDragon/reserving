<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('reconciliation command updates drifted product inventory cache', function () {
    // Arrange
    $product = Product::factory()->create([
        'quantity' => 5,
        'available_quantity' => 5,
        'is_active' => true,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 2,
    ]);

    Product::query()->whereKey($product->id)->update([
        'available_quantity' => 5,
        'is_active' => true,
    ]);

    // Act
    $this->artisan('app:reconcile-product-inventory')
        ->assertSuccessful();

    // Assert
    expect($product->fresh()->available_quantity)->toBe(3)
        ->and($product->fresh()->is_active)->toBeTrue();
});

test('reconciliation command dry run reports drift but does not persist changes', function () {
    // Arrange
    $product = Product::factory()->create([
        'quantity' => 1,
        'available_quantity' => 1,
        'is_active' => true,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    Product::query()->whereKey($product->id)->update([
        'available_quantity' => 1,
        'is_active' => true,
    ]);

    // Assert
    expect($product->fresh()->available_quantity)->toBe(1)
        ->and($product->fresh()->is_active)->toBeTrue();

    // Act
    $this->artisan('app:reconcile-product-inventory', ['--dry-run' => true])
        ->assertSuccessful();

    // Assert
    expect($product->fresh()->available_quantity)->toBe(1)
        ->and($product->fresh()->is_active)->toBeTrue();
});
