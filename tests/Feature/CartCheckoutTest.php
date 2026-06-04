<?php

use App\Enums\ReservationStatus;
use App\Mail\ReservationOrderSubmittedToAdminMail;
use App\Mail\ReservationPendingReviewMail;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
});

test('checkout creates reservation order from cart items and clears cart', function () {
    $user = User::factory()->create();
    User::factory()->admin()->count(2)->create();

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
        ->and($reservation->status)->toBe(ReservationStatus::Pending)
        ->and($reservation->reservation_order_id)->not->toBeNull();

    Mail::assertQueued(ReservationPendingReviewMail::class, function (ReservationPendingReviewMail $mail) use ($user): bool {
        return $mail->hasTo($user->email);
    });

    Mail::assertQueued(ReservationOrderSubmittedToAdminMail::class, 2);
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

test('checkout prevents double reservation on overlapping requests', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $product = Product::factory()->create(['quantity' => 1]);

    $start = Carbon::now()->addDays(4)->startOfHour();
    $end = $start->copy()->addHours(2);

    $firstCart = $firstUser->cart()->create();
    $secondCart = $secondUser->cart()->create();

    CartItem::factory()->create([
        'cart_id' => $firstCart->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'requested_quantity' => 1,
    ]);

    $secondUserCartItem = CartItem::factory()->create([
        'cart_id' => $secondCart->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'requested_quantity' => 1,
    ]);

    $this->actingAs($firstUser)->postJson(route('carts.checkout'))->assertCreated();

    $secondResponse = $this->actingAs($secondUser)->postJson(route('carts.checkout'));

    $secondResponse
        ->assertUnprocessable()
        ->assertJsonValidationErrors("items.{$secondUserCartItem->id}");

    expect(ReservationOrder::query()->count())->toBe(1)
        ->and(Reservation::query()->count())->toBe(1)
        ->and($product->refresh()->available_quantity)->toBe(0)
        ->and(CartItem::query()->count())->toBe(1);
});

test('checkout keeps cached product inventory aligned with active reservations', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'quantity' => 4,
        'available_quantity' => 4,
        'is_active' => true,
    ]);

    $cart = $user->cart()->create();

    $start = Carbon::now()->addDays(5)->startOfHour();
    $end = $start->copy()->addHours(2);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => $end,
        'requested_quantity' => 3,
    ]);

    $this->actingAs($user)->postJson(route('carts.checkout'))->assertCreated();

    $activeReservedQuantity = Reservation::query()
        ->where('product_id', $product->id)
        ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
        ->sum('reserved_quantity');

    $refreshedProduct = $product->refresh();
    $expectedAvailableQuantity = max($refreshedProduct->quantity - (int) $activeReservedQuantity, 0);

    expect($refreshedProduct->available_quantity)->toBe($expectedAvailableQuantity)
        ->and($refreshedProduct->is_active)->toBe($expectedAvailableQuantity > 0);
});

test('checkout reconciles all touched products in the order', function () {
    $user = User::factory()->create();

    $productA = Product::factory()->create([
        'quantity' => 5,
        'available_quantity' => 5,
        'is_active' => true,
    ]);

    $productB = Product::factory()->create([
        'quantity' => 3,
        'available_quantity' => 3,
        'is_active' => true,
    ]);

    $cart = $user->cart()->create();

    $startA = Carbon::now()->addDays(6)->startOfHour();
    $endA = $startA->copy()->addHours(2);
    $startB = Carbon::now()->addDays(7)->startOfHour();
    $endB = $startB->copy()->addHours(2);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $productA->id,
        'start_time' => $startA,
        'end_time' => $endA,
        'requested_quantity' => 2,
    ]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $productB->id,
        'start_time' => $startB,
        'end_time' => $endB,
        'requested_quantity' => 1,
    ]);

    $this->actingAs($user)->postJson(route('carts.checkout'))->assertCreated();

    $productAReserved = Reservation::query()
        ->where('product_id', $productA->id)
        ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
        ->sum('reserved_quantity');

    $productBReserved = Reservation::query()
        ->where('product_id', $productB->id)
        ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
        ->sum('reserved_quantity');

    $refreshedProductA = $productA->refresh();
    $refreshedProductB = $productB->refresh();

    expect($refreshedProductA->available_quantity)->toBe(max($refreshedProductA->quantity - (int) $productAReserved, 0))
        ->and($refreshedProductA->is_active)->toBe($refreshedProductA->available_quantity > 0)
        ->and($refreshedProductB->available_quantity)->toBe(max($refreshedProductB->quantity - (int) $productBReserved, 0))
        ->and($refreshedProductB->is_active)->toBe($refreshedProductB->available_quantity > 0);
});
