<?php

use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('authenticated user can create a confirmed reservation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $start = Carbon::now()->addDay()->startOfHour();
    $end = (clone $start)->addHours(2);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('reservation.status', 'confirmed')
        ->assertJsonPath('reservation.product_id', $product->id);

    expect(Reservation::count())->toBe(1)
        ->and(Reservation::first()->status)->toBe('confirmed');
});

test('reservation fails when overlapping a confirmed reservation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $existingStart = Carbon::now()->addDays(2)->startOfHour();
    $existingEnd = (clone $existingStart)->addHours(2);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'start_time' => $existingStart,
        'end_time' => $existingEnd,
        'status' => 'confirmed',
    ]);

    $overlapStart = (clone $existingStart)->addHour();
    $overlapEnd = (clone $existingEnd)->addHour();

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => $overlapStart->toDateTimeString(),
            'end_time' => $overlapEnd->toDateTimeString(),
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('start_time');

    expect(Reservation::count())->toBe(1);
});

test('reservation can overlap a cancelled reservation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $existingStart = Carbon::now()->addDays(3)->startOfHour();
    $existingEnd = (clone $existingStart)->addHours(2);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'start_time' => $existingStart,
        'end_time' => $existingEnd,
        'status' => 'cancelled',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => (clone $existingStart)->addMinutes(30)->toDateTimeString(),
            'end_time' => (clone $existingEnd)->subMinutes(30)->toDateTimeString(),
        ]);

    $response->assertCreated();

    expect(Reservation::count())->toBe(2);
});

test('reservation validation requires a future start and end after start', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $start = Carbon::now()->subHour();
    $end = (clone $start)->subMinutes(30);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.store'), [
            'product_id' => $product->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_time', 'end_time']);
});
