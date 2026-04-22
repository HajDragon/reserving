<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access manage items page for an order', function () {
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    $response
        ->assertOk()
        ->assertViewHas('order', $order)
        ->assertViewHas('reservations');
});

test('non admin cannot access manage items page', function () {
    $user = User::factory()->create();
    $order = ReservationOrder::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('reservation-orders.manage-items', $order));

    $response->assertForbidden();
});

test('manage items page displays all items in order', function () {
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $products = Product::factory(3)->create();

    $reservations = $products->map(function ($product) use ($order) {
        return Reservation::factory()->create([
            'reservation_order_id' => $order->id,
            'product_id' => $product->id,
            'status' => ReservationStatus::Reserved,
        ]);
    });

    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    $response->assertOk();

    $viewReservations = $response->viewData('reservations');
    expect($viewReservations->count())->toBe(3);
    expect($viewReservations->pluck('id')->sort()->toArray())
        ->toBe($reservations->pluck('id')->sort()->toArray());
});

test('admin can update individual item status on manage items page', function () {
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create();

    $reservation = Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => 'still_waiting_for_return',
        ]);

    $response->assertOk();

    expect($reservation->refresh()->status)->toBe(ReservationStatus::StillWaitingForReturn);
});

test('manage items page shows correct status dropdown options based on current status', function () {
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    $response->assertOk()
        ->assertSee('Still Waiting for Return')
        ->assertSee('Returned');
});
