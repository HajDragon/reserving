<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * Unit test: Reservation model transitionTo() method.
 * Tests the model-level guard that delegates to ReservationStatus::canTransitionTo().
 */

test('transitionTo changes status when transition is valid', function () {
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Pending,
    ]);

    $result = $reservation->transitionTo(ReservationStatus::Reserved);

    expect($result)->toBeTrue();
    expect($reservation->status)->toBe(ReservationStatus::Reserved);
});

test('transitionTo returns false and does not change status when transition is invalid', function () {
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Pending,
    ]);

    $result = $reservation->transitionTo(ReservationStatus::Returned);

    expect($result)->toBeFalse();
    expect($reservation->status)->toBe(ReservationStatus::Pending);
});

test('transitionTo works for the full lifecycle from pending to returned', function () {
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Pending,
    ]);

    // Pending → Reserved
    expect($reservation->transitionTo(ReservationStatus::Reserved))->toBeTrue();
    expect($reservation->status)->toBe(ReservationStatus::Reserved);

    // Reserved → StillWaitingForReturn
    expect($reservation->transitionTo(ReservationStatus::StillWaitingForReturn))->toBeTrue();
    expect($reservation->status)->toBe(ReservationStatus::StillWaitingForReturn);

    // StillWaitingForReturn → Returned
    expect($reservation->transitionTo(ReservationStatus::Returned))->toBeTrue();
    expect($reservation->status)->toBe(ReservationStatus::Returned);

    // Returned is terminal
    expect($reservation->transitionTo(ReservationStatus::Cancelled))->toBeFalse();
});

test('transitionTo cannot move from cancelled to any status', function () {
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Cancelled,
    ]);

    expect($reservation->transitionTo(ReservationStatus::Pending))->toBeFalse();
    expect($reservation->transitionTo(ReservationStatus::Reserved))->toBeFalse();
    expect($reservation->transitionTo(ReservationStatus::Returned))->toBeFalse();
});
