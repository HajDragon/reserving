<?php

use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
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
        'status' => 'confirmed',
    ]);

    Reservation::factory()->create([
        'user_id' => $otherUser->id,
        'product_id' => $otherUserProduct->id,
        'status' => 'confirmed',
    ]);

    $response = $this
        ->actingAs($currentUser)
        ->get(route('reservations.index'));

    $response
        ->assertOk()
        ->assertSeeText('Current User Laptop')
        ->assertDontSeeText('Other User Projector');
});
