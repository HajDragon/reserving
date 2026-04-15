<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('availability service calculates remaining capacity from overlapping reservations', function () {
    $product = Product::factory()->create([
        'quantity' => 5,
    ]);

    $startTime = Carbon::parse('2026-04-20 10:00:00');
    $endTime = Carbon::parse('2026-04-20 12:00:00');

    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 2,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => $startTime->copy()->addMinutes(15),
        'end_time' => $endTime->copy()->addMinutes(15),
        'status' => ReservationStatus::Pending,
        'reserved_quantity' => 1,
    ]);

    Reservation::factory()->returned()->create([
        'product_id' => $product->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'reserved_quantity' => 1,
    ]);

    $availabilityService = app(AvailabilityService::class);

    expect($availabilityService->remainingCapacity($product, $startTime, $endTime))->toBe(2)
        ->and($availabilityService->checkAvailability($product, $startTime, $endTime, 2))->toBeTrue()
        ->and($availabilityService->checkAvailability($product, $startTime, $endTime, 3))->toBeFalse();
});

test('availability service syncs product activity from remaining capacity', function () {
    $product = Product::factory()->create([
        'quantity' => 2,
        'is_active' => true,
    ]);

    $startTime = Carbon::parse('2026-04-22 09:00:00');
    $endTime = Carbon::parse('2026-04-22 11:00:00');

    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $availabilityService = app(AvailabilityService::class);
    $availabilityService->syncProductAvailability($product, $startTime, $endTime);

    expect($product->refresh()->is_active)->toBeFalse();

    Reservation::query()
        ->where('product_id', $product->id)
        ->first()
        ?->update([
            'status' => ReservationStatus::Returned,
        ]);

    $availabilityService->syncProductAvailability($product, $startTime, $endTime);

    expect($product->refresh()->is_active)->toBeTrue();
});
