<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/*
 * Unit test: AvailabilityService calculatedAvailableQuantity().
 * Verifies the core inventory math that drives the product index page
 * and the checkout availability check.
 */

test('calculatedAvailableQuantity returns full quantity when no active reservations', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 5]);

    // Act
    $service = app(AvailabilityService::class);

    // Assert
    expect($service->calculatedAvailableQuantity($product))->toBe(5);
});

test('calculatedAvailableQuantity subtracts reserved and pending quantities', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 10]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 3,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Pending,
        'reserved_quantity' => 2,
    ]);

    $service = app(AvailabilityService::class);

    expect($service->calculatedAvailableQuantity($product))->toBe(5);
});

test('calculatedAvailableQuantity ignores returned and cancelled reservations', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 5]);

    Reservation::factory()->returned()->create([
        'product_id' => $product->id,
        'reserved_quantity' => 3,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Cancelled,
        'reserved_quantity' => 2,
    ]);

    // Act
    $service = app(AvailabilityService::class);

    // Assert
    expect($service->calculatedAvailableQuantity($product))->toBe(5);
});

test('calculatedAvailableQuantity never returns negative', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 2]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 5,
    ]);

    // Act
    $service = app(AvailabilityService::class);

    // Assert
    expect($service->calculatedAvailableQuantity($product))->toBe(0);
});

test('reconcileProducts updates available_quantity and is_active on product', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 3]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 3,
    ]);

    // Act
    $service = app(AvailabilityService::class);
    $service->reconcileProducts([$product]);

    // Assert
    $product->refresh();

    expect($product->available_quantity)->toBe(0);
    expect($product->is_active)->toBeFalse();
});

test('remainingCapacity only counts overlapping time windows', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 3]);

    // This reservation overlaps the query window
    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => Carbon::parse('2026-06-01 10:00'),
        'end_time' => Carbon::parse('2026-06-01 12:00'),
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    // This reservation does NOT overlap (ends before query starts)
    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => Carbon::parse('2026-06-01 06:00'),
        'end_time' => Carbon::parse('2026-06-01 08:00'),
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 2,
    ]);

    // Act
    $service = app(AvailabilityService::class);

    // Assert
    expect($service->remainingCapacity($product, '2026-06-01 09:00', '2026-06-01 11:00'))->toBe(2);
});

test('checkAvailability returns false when requested quantity exceeds capacity', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 2]);

    // Assert
    $service = app(AvailabilityService::class);

    expect($service->checkAvailability($product, now()->addDay(), now()->addDay()->addHours(2), 3))->toBeFalse();
});

test('checkAvailability returns false for quantity less than 1', function () {
    // Arrange
    $product = Product::factory()->create(['quantity' => 5]);

    // Assert
    $service = app(AvailabilityService::class);

    expect($service->checkAvailability($product, now()->addDay(), now()->addDay()->addHours(2), 0))->toBeFalse();
});
