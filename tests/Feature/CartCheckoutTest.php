<?php

use App\Enums\ReservationStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('checkout creates reservation order from cart items and clears cart', function () {
    $user = User::factory()->create();

    $productA = Product::factory()->create(['quantity' => 3]);
    $productB = Product::factory()->create(['quantity' => 2]);

    $cart = $user->cart()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $productA->id,
        'start_time' => Carbon::now()->addDays(2)->startOfHour(),
        'end_time' => Carbon::now()->addDays(2)->startOfHour()->addHours(2),
        'requested_quantity' => 2,
        'extra_wishes' => 'Lens required',
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $productB->id,
        'start_time' => Carbon::now()->addDays(3)->startOfHour(),
        'end_time' => Carbon::now()->addDays(3)->startOfHour()->addHours(2),
        'requested_quantity' => 1,
        'extra_wishes' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('carts.checkout'));

    $response
        ->assertCreated()
        ->assertJsonPath('reservation_order.user_id', $user->id);

    expect(ReservationOrder::query()->count())->toBe(1)
        ->and(Reservation::query()->count())->toBe(2)
        ->and(CartItem::query()->count())->toBe(0);

    $reservation = Reservation::query()->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->status)->toBe(ReservationStatus::Reserved)
        ->and($reservation->reservation_order_id)->not->toBeNull();
});

test('checkout fails for an empty cart', function () {
    $user = User::factory()->create();
    $user->cart()->create();

    $response = $this
        ->actingAs($user)
        ->postJson(route('carts.checkout'));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cart');

    expect(ReservationOrder::query()->count())->toBe(0)
        ->and(Reservation::query()->count())->toBe(0);
});

test('checkout rolls back when capacity is no longer available', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 1]);

    $cart = $user->cart()->create();

    $start = Carbon::now()->addDays(2)->startOfHour();
    $end = $start->copy()->addHours(2);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'requested_quantity' => 1,
    ]);

    Reservation::factory()->create([
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('carts.checkout'));

    $response->assertUnprocessable();

    expect(ReservationOrder::query()->count())->toBe(0)
        ->and(Reservation::query()->where('user_id', $user->id)->count())->toBe(0)
        ->and(CartItem::query()->count())->toBe(1);
});
