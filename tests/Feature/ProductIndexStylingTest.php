<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

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
    ]);

    // Add a fake media item to ensure the image is rendered
    $product->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('photo');

    $response = $this
        ->actingAs($user)
        ->get(route('products.index'));

    $response
        ->assertOk()
        ->assertSeeText($product->name)
        ->assertSeeText(strtoupper($product->type))
        ->assertSeeText('Qty: '.$product->quantity)
        ->assertSeeText('No description available for this product.')
        ->assertSeeText('Add')
        ->assertDontSeeText('Start time')
        ->assertDontSeeText('End time')
        ->assertDontSeeText('Quantity')
        ->assertDontSeeText('Extra wishes')
        ->assertSee('rounded-4xl')
        ->assertSee('loading="lazy"', false)
        ->assertDontSee('shadow-[0_20px_60px_rgba(15,23,42,0.14)]', false);
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
        ->assertDontSeeText('Add');
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
        ->assertSee('wire:model.live.debounce.400ms="search"', false);
});

test('products index renders first infinite-scroll chunk for search results', function () {
    $user = User::factory()->create();

    Collection::times(20, function (int $index) {
        Product::factory()->create([
            'name' => sprintf('Camera %02d', $index),
            'asset_tag' => sprintf('CAM-%03d', $index),
            'is_active' => true,
        ]);
    });

    $response = $this
        ->actingAs($user)
        ->get(route('products.index', ['search' => 'Camera']));

    $response
        ->assertOk()
        ->assertSee('wire:model.live.debounce.400ms="search"', false)
        ->assertSeeText('Camera 01')
        ->assertSeeText('Camera 09')
        ->assertDontSeeText('Camera 10');
});
