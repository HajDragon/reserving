<?php

use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('product index supports filtering and sorting for authenticated users', function () {
    Product::factory()->create([
        'name' => 'Laptop Pro',
        'asset_tag' => 'ASSET-1000AA',
    ]);

    Product::factory()->create([
        'name' => 'Projector Lite',
        'asset_tag' => 'ASSET-2000BB',
    ]);

    Sanctum::actingAs(User::factory()->create(), ['*']);

    $response = $this->getJson('/api/products?filter[name]=Laptop&sort=name');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Laptop Pro')
        ->assertJsonPath('data.0.asset_tag', 'ASSET-1000AA')
        ->assertJsonMissingPath('data.1');
});

test('product api is protected by sanctum', function () {
    $this->getJson('/api/products')->assertUnauthorized();
});

test('product store returns api resource payload', function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $category = \App\Models\Category::factory()->create();

    $response = $this->postJson('/api/products', [
        'asset_tag' => 'ASSET-9999ZZ',
        'name' => 'Camera Prime',
        'description' => '4K camera',
        'category_id' => $category->id,
        'quantity' => 10,
        'available_quantity' => 10,
        'is_active' => true,
        'photo_path' => 'https://example.com/camera.jpg',
        'external_link' => 'https://example.com/camera',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.asset_tag', 'ASSET-9999ZZ')
        ->assertJsonPath('data.name', 'Camera Prime');

    $this->assertDatabaseHas('products', [
        'asset_tag' => 'ASSET-9999ZZ',
        'name' => 'Camera Prime',
    ]);
});
