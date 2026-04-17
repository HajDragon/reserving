<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected when accessing products index', function () {
    $response = $this->get(route('products.index'));

    $response->assertRedirect(route('login'));
});

test('products index renders the styled product card content', function () {
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'name' => 'Studio Headphones',
        'type' => 'camera',
        'quantity' => 7,
        'available_quantity' => 7,
        'description' => null,
        'photo_path' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response
        ->assertOk()
        ->assertSeeText($product->name)
        ->assertSeeText(strtoupper($product->type))
        ->assertSeeText('Qty: '.$product->quantity)
        ->assertSeeText('No description available for this product.')
        ->assertSeeText('No image available')
        ->assertSeeText('Add to Cart')
        ->assertDontSeeText('Start time')
        ->assertDontSeeText('End time')
        ->assertDontSeeText('Quantity')
        ->assertDontSeeText('Extra wishes')
        ->assertSee('card-snake-border')
        ->assertSee('rounded-sm')
        ->assertSee('bg-neutral-600');
});

test('products index sorts unavailable products to the end', function () {
    $user = User::factory()->create();

    Product::factory()->create([
        'name' => 'Active Product',
        'is_active' => true,
        'available_quantity' => 3,
    ]);

    Product::factory()->create([
        'name' => 'Inactive Product',
        'is_active' => false,
        'available_quantity' => 0,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response
        ->assertOk()
        ->assertSeeInOrder(['Active Product', 'Inactive Product']);
});

test('products index shows unavailable label and disables add to cart when product is unavailable', function () {
    $user = User::factory()->create();

    Product::factory()->create([
        'name' => 'Unavailable Camera',
        'quantity' => 2,
        'available_quantity' => 0,
        'is_active' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response
        ->assertOk()
        ->assertSeeText('Unavailable Camera')
        ->assertSeeText('Qty: 0')
        ->assertSeeText('Unavailable')
        ->assertDontSeeText('Add to Cart');
});

test('products index can filter products by search query', function () {
    $user = User::factory()->create();

    Product::factory()->create([
        'name' => 'Cinema Camera Kit',
        'asset_tag' => 'CAM-100',
    ]);

    Product::factory()->create([
        'name' => 'Audio Recorder',
        'asset_tag' => 'AUD-200',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('products.index', ['search' => 'Cinema']));

    $response
        ->assertOk()
        ->assertSeeText('Cinema Camera Kit')
        ->assertDontSeeText('Audio Recorder')
        ->assertSee('name="search"', false)
        ->assertSee('value="Cinema"', false);
});
