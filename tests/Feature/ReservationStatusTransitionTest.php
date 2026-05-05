<?php

use App\Enums\AdminReservationStatus;
use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

test('admin can transition reservation from reserved to still waiting for return', function () {
    $admin = User::factory()->admin()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => AdminReservationStatus::StillWaitingForReturn->value,
        ]);

    $response->assertOk();

    $updatedReservation = $reservation->refresh();
    expect($updatedReservation->status)->toBe(ReservationStatus::StillWaitingForReturn)
        ->and($updatedReservation->reviewed_by)->toBe($admin->id)
        ->and($updatedReservation->reviewed_at)->not->toBeNull();
});

test('admin can transition reservation from still waiting for return to returned', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'quantity' => 1,
        'is_active' => false,
    ]);

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::StillWaitingForReturn,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => AdminReservationStatus::Returned->value,
        ]);

    $response->assertOk();

    $updatedReservation = $reservation->refresh();
    expect($updatedReservation->status)->toBe(ReservationStatus::Returned)
        ->and($updatedReservation->returned_by)->toBe($admin->id)
        ->and($updatedReservation->returned_at)->not->toBeNull();
});

test('admin cannot transition pending to still waiting for return', function () {
    $admin = User::factory()->admin()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Pending,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => AdminReservationStatus::StillWaitingForReturn->value,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('non admin cannot update reservation status', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($user)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => AdminReservationStatus::StillWaitingForReturn->value,
        ]);

    $response->assertForbidden();
});

test('status transition validation works correctly', function () {
    $status = ReservationStatus::Reserved;

    expect($status->canTransitionTo(ReservationStatus::StillWaitingForReturn))->toBeTrue()
        ->and($status->canTransitionTo(ReservationStatus::Returned))->toBeTrue()
        ->and($status->canTransitionTo(ReservationStatus::Cancelled))->toBeTrue()
        ->and($status->canTransitionTo(ReservationStatus::Pending))->toBeFalse();

    $waitingStatus = ReservationStatus::StillWaitingForReturn;

    expect($waitingStatus->canTransitionTo(ReservationStatus::Returned))->toBeTrue()
        ->and($waitingStatus->canTransitionTo(ReservationStatus::Cancelled))->toBeTrue()
        ->and($waitingStatus->canTransitionTo(ReservationStatus::Reserved))->toBeFalse();
});

test('admin status enum mapping works correctly', function () {
    expect(AdminReservationStatus::StillWaitingForReturn->toReservationStatus())
        ->toBe(ReservationStatus::StillWaitingForReturn);

    expect(AdminReservationStatus::fromReservationStatus(ReservationStatus::StillWaitingForReturn))
        ->toBe(AdminReservationStatus::StillWaitingForReturn);
});
