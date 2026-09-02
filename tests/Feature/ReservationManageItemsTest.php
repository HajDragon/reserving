<?php

use App\Enums\ReservationStatus;
use App\Mail\ReservationRemovalRequestedMail;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\ReservationRemovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('admin can access manage items page for an order', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    // Act
    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    // Assert
    $response
        ->assertOk()
        ->assertViewHas('order', $order)
        ->assertViewHas('reservations');
});

test('non admin cannot access manage items page', function () {
    // Arrange
    $user = User::factory()->create();
    $order = ReservationOrder::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->get(route('reservation-orders.manage-items', $order));

    // Assert
    $response->assertForbidden();
});

test('manage items page displays all items in order', function () {
    // Arrange
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

    // Act
    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    // Assert
    $response->assertOk();

    $viewReservations = $response->viewData('reservations');
    expect($viewReservations->count())->toBe(3);
    expect($viewReservations->pluck('id')->sort()->values()->toArray())
        ->toBe($reservations->pluck('id')->sort()->values()->toArray());
});

test('admin can update individual item status on manage items page', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create(['quantity' => 5, 'available_quantity' => 5]);

    $reservation = Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    // Act
    $response = $this
        ->actingAs($admin)
        ->patchJson(route('reservations.update-status', $reservation), [
            'status' => 'still_waiting_for_return',
        ]);

    // Assert
    $response->assertOk();

    expect($reservation->refresh()->status)->toBe(ReservationStatus::StillWaitingForReturn);
});

test('user can edit a pending reservation', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 10]);
    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Pending,
    ]);

    // Act
    $this->actingAs($user)
        ->patch(route('reservations.update', $reservation), [
            'start_time' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'end_time' => now()->addDays(3)->addHours(2)->format('Y-m-d\TH:i'),
            'reserved_quantity' => 2,
            'extra_wishes' => 'Please prepare the item',
        ])
    // Assert
        ->assertRedirect();

    $reservation->refresh();

    expect($reservation->status)->toBe(ReservationStatus::Pending)
        ->and($reservation->reserved_quantity)->toBe(2)
        ->and($reservation->extra_wishes)->toBe('Please prepare the item');
});

test('manage items page shows correct status dropdown options based on current status', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $order = ReservationOrder::factory()->create();
    $product = Product::factory()->create();

    Reservation::factory()->create([
        'reservation_order_id' => $order->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    // Act
    $response = $this
        ->actingAs($admin)
        ->get(route('reservation-orders.manage-items', $order));

    // Assert
    $response->assertOk()
        ->assertSee('Still Waiting for Return')
        ->assertSee('Returned');
});

test('user can request removal for a reserved reservation and admins receive mail', function () {
    // Arrange
    Mail::fake();

    $user = User::factory()->create();
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    // Act
    $this->actingAs($user)
        ->post(route('reservations.request-removal', $reservation), [
            'reason' => 'Need to cancel this order',
        ])
    // Assert
        ->assertRedirect();

    expect($reservation->refresh()->status)->toBe(ReservationStatus::RemovalRequest);
    expect(ReservationRemovalRequest::query()->count())->toBe(1);

    Mail::assertQueued(ReservationRemovalRequestedMail::class, 2);
});

test('admin can approve a removal request and the reservation is cancelled', function () {
    // Arrange
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 3, 'available_quantity' => 3]);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::RemovalRequest,
        'reserved_quantity' => 1,
    ]);

    $removalRequest = ReservationRemovalRequest::query()->create([
        'reservation_id' => $reservation->id,
        'user_id' => $user->id,
        'reason' => 'No longer needed',
        'status' => ReservationStatus::RemovalRequest->value,
    ]);

    // Act
    $this->actingAs($admin)
        ->patch(route('reservation-removal-requests.update-status', $removalRequest), [
            'status' => 'approved',
        ])
    // Assert
        ->assertRedirect();

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Cancelled)
        ->and($product->refresh()->available_quantity)->toBe(3)
        ->and($removalRequest->refresh()->status)->toBe('approved');
});
