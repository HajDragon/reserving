<?php

use App\Livewire\CartItemForm;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createCartItemForUser(?User $user = null): CartItem
{
    $user ??= User::factory()->create();
    $product = Product::factory()->create(['quantity' => 5]);
    $start = Carbon::now()->addDay()->startOfHour();

    return CartItem::factory()->create([
        'cart_id' => $user->cart()->create()->id,
        'product_id' => $product->id,
        'start_time' => $start,
        'end_time' => (clone $start)->addHours(2),
        'requested_quantity' => 1,
    ]);
}

test('cart item form mounts with correct values', function () {
    $cartItem = createCartItemForUser();

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->assertSet('start_time', $cartItem->start_time->format('Y-m-d\TH:i'))
        ->assertSet('end_time', $cartItem->end_time->format('Y-m-d\TH:i'))
        ->assertSet('requested_quantity', 1);
});

test('validation fails when end time is before start time', function () {
    $cartItem = createCartItemForUser();
    $start = $cartItem->start_time->format('Y-m-d\TH:i');
    $end = $cartItem->start_time->subHour()->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $start)
        ->set('end_time', $end)
        ->call('updateEndTime')
        ->assertHasErrors(['end_time' => 'after']);
});

test('validation fails when end time equals start time', function () {
    $cartItem = createCartItemForUser();
    $same = $cartItem->start_time->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $same)
        ->set('end_time', $same)
        ->call('updateEndTime')
        ->assertHasErrors(['end_time' => 'after']);
});

test('custom validation message is returned for end time before start time', function () {
    $cartItem = createCartItemForUser();
    $start = $cartItem->start_time->format('Y-m-d\TH:i');
    $end = $cartItem->start_time->subHour()->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $start)
        ->set('end_time', $end)
        ->call('updateEndTime')
        ->assertHasErrors(['end_time' => 'after'])
        ->assertSee('The end time must be after the start time.');
});

test('valid update saves to database and dispatches cart-updated', function () {
    $cartItem = createCartItemForUser();
    $newStart = $cartItem->start_time->addDays(3)->format('Y-m-d\TH:i');
    $newEnd = $cartItem->start_time->addDays(3)->addHours(3)->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $newStart)
        ->set('end_time', $newEnd)
        ->set('requested_quantity', 2)
        ->call('updateEndTime')
        ->assertHasNoErrors()
        ->assertSet('updateMessage', 'Cart item updated.')
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-item-validity-changed');

    $cartItem->refresh();
    expect($cartItem->requested_quantity)->toBe(2);
});

test('valid update dispatches cart-item-validity-changed with valid true', function () {
    $cartItem = createCartItemForUser();
    $newStart = $cartItem->start_time->addDays(3)->format('Y-m-d\TH:i');
    $newEnd = $cartItem->start_time->addDays(3)->addHours(3)->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $newStart)
        ->set('end_time', $newEnd)
        ->set('requested_quantity', 2)
        ->call('updateEndTime')
        ->assertDispatched('cart-item-validity-changed', fn (string $name, array $params) => $params['valid'] === true);
});

test('invalid dates do not persist to database', function () {
    $cartItem = createCartItemForUser();
    $originalEnd = $cartItem->end_time->copy();

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $cartItem->start_time->format('Y-m-d\TH:i'))
        ->set('end_time', $cartItem->start_time->subHour()->format('Y-m-d\TH:i'))
        ->call('updateEndTime');

    $cartItem->refresh();
    expect($cartItem->end_time->format('Y-m-d\TH:i'))->toBe($originalEnd->format('Y-m-d\TH:i'));
});

test('resetMessages clears previous messages and validation errors', function () {
    $cartItem = createCartItemForUser();
    $badEnd = $cartItem->start_time->subHour()->format('Y-m-d\TH:i');

    Livewire::test(CartItemForm::class, ['cartItem' => $cartItem])
        ->set('start_time', $cartItem->start_time->format('Y-m-d\TH:i'))
        ->set('end_time', $badEnd)
        ->call('updateEndTime')
        ->assertHasErrors(['end_time'])
        ->set('end_time', $cartItem->end_time->format('Y-m-d\TH:i'))
        ->call('updateEndTime')
        ->assertHasNoErrors();
});
