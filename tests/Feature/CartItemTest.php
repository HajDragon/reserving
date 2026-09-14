<?php

use App\Enums\ReservationStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('guest is redirected when storing a cart item', function () {
    // Arrange
    $product = Product::factory()->create();

    // Act
    $response = $this->post(route('carts.items.store'), [
        'product_id' => $product->id,
    ]);

    // Assert
    $response->assertRedirect(route('login'));
});

test('authenticated user can add a cart item', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 3,
    ]);

    // Act
    $response = $this
        ->actingAs($user)
        ->postJson(route('carts.items.store'), [
            'product_id' => $product->id,
        ]);

    // Assert
    $response
        ->assertCreated()
        ->assertJsonPath('cart_item.product_id', $product->id)
        ->assertJsonPath('cart_item.requested_quantity', 1);

    $cartItem = CartItem::query()->first();

    expect($cartItem)->not->toBeNull()
        ->and($cartItem->product_id)->toBe($product->id)
        ->and($cartItem->requested_quantity)->toBe(1)
        ->and($cartItem->extra_wishes)->toBeNull();
});

test('browser form add to cart redirects back with success message', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 3,
    ]);

    // Act
    $response = $this
        ->actingAs($user)
        ->post(route('carts.items.store'), [
            'product_id' => $product->id,
        ]);

    // Assert
    $response
        ->assertRedirect()
        ->assertSessionHas('status', 'Item added to cart successfully.');

    $cartItem = CartItem::query()->first();

    expect($cartItem)->not->toBeNull()
        ->and($cartItem->product_id)->toBe($product->id)
        ->and($cartItem->requested_quantity)->toBe(1);
});

test('cart item update fails when requested quantity exceeds product quantity', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 1,
    ]);

    $cartItem = CartItem::factory()->create([
        'cart_id' => $user->cart()->create()->id,
        'product_id' => $product->id,
        'requested_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    $start = Carbon::now()->addDay()->startOfHour();
    $end = (clone $start)->addHours(2);

    // Act
    $response = $this
        ->actingAs($user)
        ->patchJson(route('carts.items.update', $cartItem), [
            'product_id' => $product->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'requested_quantity' => 2,
        ]);

    // Assert
    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('requested_quantity');
});

test('cart item update fails when the window is already fully reserved', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 1,
    ]);

    $cartItem = CartItem::factory()->create([
        'cart_id' => $user->cart()->create()->id,
        'product_id' => $product->id,
        'requested_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    $start = Carbon::now()->addDays(2)->startOfHour();
    $end = (clone $start)->addHours(2);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'status' => ReservationStatus::Reserved,
        'reserved_quantity' => 1,
    ]);

    // Act
    $response = $this
        ->actingAs($user)
        ->patchJson(route('carts.items.update', $cartItem), [
            'product_id' => $product->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'requested_quantity' => 1,
        ]);

    // Assert
    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('requested_quantity');
});

test('authenticated user can update and remove their cart item', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 3,
    ]);

    $cartItem = CartItem::factory()->create([
        'cart_id' => $user->cart()->create()->id,
        'product_id' => $product->id,
        'requested_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    $updatedStart = Carbon::now()->addDays(3)->startOfHour();
    $updatedEnd = (clone $updatedStart)->addHours(2);

    // Act
    $updateResponse = $this
        ->actingAs($user)
        ->patchJson(route('carts.items.update', $cartItem), [
            'product_id' => $product->id,
            'start_time' => $updatedStart->toDateTimeString(),
            'end_time' => $updatedEnd->toDateTimeString(),
            'requested_quantity' => 2,
            'extra_wishes' => 'Updated wishes',
        ]);

    // Assert
    $updateResponse
        ->assertOk()
        ->assertJsonPath('cart_item.requested_quantity', 2)
        ->assertJsonPath('cart_item.extra_wishes', 'Updated wishes');

    // Act
    $deleteResponse = $this
        ->actingAs($user)
        ->deleteJson(route('carts.items.destroy', $cartItem));

    // Assert
    $deleteResponse->assertOk();

    expect(CartItem::count())->toBe(0);
});

test('browser form cart update and delete redirect back to cart page', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 4,
    ]);

    $cartItem = CartItem::factory()->create([
        'cart_id' => $user->cart()->create()->id,
        'product_id' => $product->id,
        'requested_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    $updatedStart = Carbon::now()->addDays(4)->startOfHour();
    $updatedEnd = (clone $updatedStart)->addHours(3);

    // Act
    $updateResponse = $this
        ->actingAs($user)
        ->patch(route('carts.items.update', $cartItem), [
            'product_id' => $product->id,
            'start_time' => $updatedStart->toDateTimeString(),
            'end_time' => $updatedEnd->toDateTimeString(),
            'requested_quantity' => 2,
            'extra_wishes' => 'Please include charger',
        ]);

    // Assert
    $updateResponse
        ->assertRedirect(route('carts.index'))
        ->assertSessionHas('status', 'Cart item updated successfully.');

    $cartItem->refresh();

    expect($cartItem->requested_quantity)->toBe(2)
        ->and($cartItem->extra_wishes)->toBe('Please include charger');

    // Act
    $deleteResponse = $this
        ->actingAs($user)
        ->delete(route('carts.items.destroy', $cartItem));

    // Assert
    $deleteResponse
        ->assertRedirect(route('carts.index'))
        ->assertSessionHas('status', 'Cart item removed successfully.');

    expect(CartItem::query()->whereKey($cartItem->id)->exists())->toBeFalse();
});

test('user cannot update or delete another users cart item', function () {
    // Arrange
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 3,
    ]);

    $ownerCart = $owner->cart()->create();

    $cartItem = CartItem::factory()->create([
        'cart_id' => $ownerCart->id,
        'product_id' => $product->id,
        'requested_quantity' => 1,
        'start_time' => Carbon::now()->addDay()->startOfHour(),
        'end_time' => Carbon::now()->addDay()->startOfHour()->addHours(2),
    ]);

    $start = Carbon::now()->addDays(2)->startOfHour();
    $end = (clone $start)->addHours(2);

    // Act
    $updateResponse = $this
        ->actingAs($intruder)
        ->patchJson(route('carts.items.update', $cartItem), [
            'product_id' => $product->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'requested_quantity' => 1,
        ]);

    // Assert
    $updateResponse->assertNotFound();

    // Act
    $deleteResponse = $this
        ->actingAs($intruder)
        ->deleteJson(route('carts.items.destroy', $cartItem));

    // Assert
    $deleteResponse->assertNotFound();

    expect($cartItem->fresh())->not->toBeNull();
});
