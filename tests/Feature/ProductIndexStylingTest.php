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
        ->assertSee('card-snake-border')
        ->assertSee('rounded-sm')
        ->assertSee('bg-neutral-600');
});
