<?php

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected when accessing reservations index', function () {
    $response = $this->get(route('reservations.index'));

    $response->assertRedirect(route('login'));
});

test('user sees only their own reservations on index page', function () {
    $currentUser = User::factory()->create();
    $otherUser = User::factory()->create();

    $currentUserProduct = Product::factory()->create([
        'name' => 'Current User Laptop',
    ]);

    $otherUserProduct = Product::factory()->create([
        'name' => 'Other User Projector',
    ]);

    Reservation::factory()->create([
        'user_id' => $currentUser->id,
        'product_id' => $currentUserProduct->id,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 3,
        'extra_wishes' => 'Include charger',
    ]);

    Reservation::factory()->create([
        'user_id' => $otherUser->id,
        'product_id' => $otherUserProduct->id,
        'status' => ReservationStatus::Reserved,
    ]);

    $response = $this
        ->actingAs($currentUser)
        ->get(route('reservations.index'));

    $response
        ->assertOk()
        ->assertSeeText('Current User Laptop')
        ->assertSeeText('3')
        ->assertSeeText('Include charger')
        ->assertDontSeeText('Other User Projector');
});

test('user sees returned date and status for returned reservation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Returned Camera',
    ]);

    $returnedAt = Carbon::parse('2026-04-15 13:45:00');

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Returned,
        'reserved_quantity' => 2,
        'extra_wishes' => 'Add tripod',
        'returned_at' => $returnedAt,
        'returned_by' => User::factory()->create()->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('reservations.index'));

    $response
        ->assertOk()
        ->assertSeeText('Returned Camera')
        ->assertSeeText('Returned')
        ->assertSeeText('2026-04-15 13:45')
        ->assertSeeText('Add tripod');
});

test('user sees color coded statuses in my reservations list', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Pending,
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Reserved,
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => ReservationStatus::Cancelled,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('reservations.index'));

    $response
        ->assertOk()
        ->assertSee('bg-yellow-100', false)
        ->assertSee('bg-green-100', false)
        ->assertSee('bg-red-100', false);
});
