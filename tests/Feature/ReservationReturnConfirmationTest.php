<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can confirm single reservation as returned', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create([
        'quantity' => 1,
        'is_active' => false,
    ]);

    $reservation = Reservation::factory()->create([
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response
        ->assertOk()
        ->assertJsonPath('reservation.status', ReservationStatus::Returned->value);

    $reservation->refresh();

    expect($reservation->status)->toBe(ReservationStatus::Returned)
        ->and($reservation->returned_at)->not->toBeNull()
        ->and($reservation->returned_by)->toBe($admin->id)
        ->and($product->refresh()->is_active)->toBeTrue();
});

test('non admin cannot confirm single reservation as returned', function () {
    $user = User::factory()->create();

    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response->assertForbidden();
});

test('confirm single return fails for non reserved reservation', function () {
    $admin = User::factory()->admin()->create();

    $reservation = Reservation::factory()->returned()->create();

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservations.confirm-returned', $reservation));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reservation');
});

test('admin can confirm all reservations in an order as returned', function () {
    $admin = User::factory()->admin()->create();
    $orderOwner = User::factory()->create();

    $productA = Product::factory()->create(['quantity' => 2, 'is_active' => false]);
    $productB = Product::factory()->create(['quantity' => 2, 'is_active' => false]);

    $reservationOrder = ReservationOrder::factory()->create([
        'user_id' => $orderOwner->id,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $productA->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'product_id' => $productB->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response->assertOk();

    expect(
        Reservation::query()
            ->where('reservation_order_id', $reservationOrder->id)
            ->where('status', ReservationStatus::Returned)
            ->count()
    )->toBe(2)
        ->and($productA->refresh()->is_active)->toBeTrue()
        ->and($productB->refresh()->is_active)->toBeTrue();
});

test('non admin cannot confirm order return', function () {
    $user = User::factory()->create();
    $reservationOrder = ReservationOrder::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response->assertForbidden();
});

test('confirm order return fails when an order has non reserved reservation', function () {
    $admin = User::factory()->admin()->create();
    $reservationOrder = ReservationOrder::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $reservationOrder->id,
        'status' => ReservationStatus::Returned,
    ]);

    $response = $this
        ->actingAs($admin)
        ->postJson(route('reservation-orders.confirm-returned', $reservationOrder));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('order');
});
