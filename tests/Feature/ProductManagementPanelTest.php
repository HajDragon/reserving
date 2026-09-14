<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('non admin cannot access product cms management routes', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create();

    // Act
    $this->actingAs($user)->get(route('cms.products.index'))->assertForbidden();
    // Assert
    $this->actingAs($user)->get(route('cms.products.create'))->assertForbidden();
    $this->actingAs($user)->get(route('cms.products.show', $product))->assertForbidden();
    $this->actingAs($user)->get(route('cms.products.edit', $product))->assertForbidden();
});

test('admin can create view edit and delete product from cms', function () {
    // Arrange
    $admin = User::factory()->admin()->create();

    $category = Category::factory()->create();

    $createPayload = [
        'category_id' => $category->id,
        'asset_tag' => 'ASSET-ADMIN-01',
        'name' => 'CMS Product',
        'description' => 'Managed by CMS',
        'quantity' => 5,
        'is_active' => 1,
    ];

    // Act
    $this->actingAs($admin)
        ->post(route('cms.products.store'), $createPayload)
    // Assert
        ->assertRedirect(route('cms.products.index'));

    // Arrange
    $product = Product::query()->where('asset_tag', 'ASSET-ADMIN-01')->firstOrFail();

    // Act
    $this->actingAs($admin)
        ->get(route('cms.products.show', $product))
    // Assert
        ->assertOk()
        ->assertSeeText('CMS Product')
        ->assertSeeText('ASSET-ADMIN-01');

    // Act
    $this->actingAs($admin)
        ->put(route('cms.products.update', $product), [
            'category_id' => $category->id,
            'asset_tag' => 'ASSET-ADMIN-01',
            'name' => 'CMS Product Updated',
            'description' => 'Managed by CMS updated',
            'quantity' => 8,
            'is_active' => 0,
        ])
    // Assert
        ->assertRedirect(route('cms.products.show', $product));

    $product->refresh();

    expect($product->name)->toBe('CMS Product Updated')
        ->and($product->quantity)->toBe(8)
        ->and($product->is_active)->toBeFalse();

    // Act
    $this->actingAs($admin)
        ->delete(route('cms.products.destroy', $product))
    // Assert
        ->assertRedirect(route('cms.products.index'));

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse();
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('admin can upload product photo from cms', function () {
    // Arrange
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    // Act
    $this->actingAs($admin)
        ->post(route('cms.products.store'), [
            'asset_tag' => 'ASSET-UPLOAD-01',
            'name' => 'Uploaded Photo Product',
            'description' => 'Managed by CMS with file upload',
            'category_id' => $category->id,
            'quantity' => 3,
            'is_active' => 1,
            'photo' => UploadedFile::fake()->create('camera.jpg', 120, 'image/jpeg'),
        ])
    // Assert
        ->assertRedirect(route('cms.products.index'));

    $product = Product::query()->where('asset_tag', 'ASSET-UPLOAD-01')->firstOrFail();

    expect($product->photo_path)->not->toBeNull();

    // Spatie media library stores in numeric folders
    $media = $product->getFirstMedia('photo');
    Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
});
