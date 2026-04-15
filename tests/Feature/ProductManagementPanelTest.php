<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('non admin cannot access product cms management routes', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->get(route('cms.products.index'))->assertForbidden();
    $this->actingAs($user)->get(route('cms.products.create'))->assertForbidden();
    $this->actingAs($user)->get(route('cms.products.show', $product))->assertForbidden();
    $this->actingAs($user)->get(route('cms.products.edit', $product))->assertForbidden();
});

test('admin can create view edit and delete product from cms', function () {
    $admin = User::factory()->admin()->create();

    $createPayload = [
        'asset_tag' => 'ASSET-ADMIN-01',
        'name' => 'CMS Product',
        'description' => 'Managed by CMS',
        'type' => 'camera',
        'quantity' => 5,
        'is_active' => 1,
        'photo_path' => 'https://example.com/cms-product.jpg',
    ];

    $this->actingAs($admin)
        ->post(route('cms.products.store'), $createPayload)
        ->assertRedirect(route('cms.products.index'));

    $product = Product::query()->where('asset_tag', 'ASSET-ADMIN-01')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('cms.products.show', $product))
        ->assertOk()
        ->assertSeeText('CMS Product')
        ->assertSeeText('ASSET-ADMIN-01');

    $this->actingAs($admin)
        ->put(route('cms.products.update', $product), [
            'asset_tag' => 'ASSET-ADMIN-01',
            'name' => 'CMS Product Updated',
            'description' => 'Managed by CMS updated',
            'type' => 'camera',
            'quantity' => 8,
            'is_active' => 0,
            'photo_path' => 'https://example.com/cms-product-updated.jpg',
        ])
        ->assertRedirect(route('cms.products.show', $product));

    $product->refresh();

    expect($product->name)->toBe('CMS Product Updated')
        ->and($product->quantity)->toBe(8)
        ->and($product->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('cms.products.destroy', $product))
        ->assertRedirect(route('cms.products.index'));

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse();
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('admin can upload product photo from cms', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('cms.products.store'), [
            'asset_tag' => 'ASSET-UPLOAD-01',
            'name' => 'Uploaded Photo Product',
            'description' => 'Managed by CMS with file upload',
            'type' => 'camera',
            'quantity' => 3,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->create('camera.jpg', 120, 'image/jpeg'),
        ])
        ->assertRedirect(route('cms.products.index'));

    $product = Product::query()->where('asset_tag', 'ASSET-UPLOAD-01')->firstOrFail();

    expect($product->photo_path)->toStartWith('/storage/products/');

    $storedPath = substr($product->photo_path, strlen('/storage/'));

    Storage::disk('public')->assertExists($storedPath);
});
