<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Step 1 — Authorization
|--------------------------------------------------------------------------
| Only admins (can:access-reserving-dashboard) may manage products.
*/

test('unauthenticated guest cannot access cms product routes', function () {
    $product = Product::factory()->create();

    $this->get(route('cms.products.index'))->assertRedirect();
    $this->get(route('cms.products.create'))->assertRedirect();
    $this->post(route('cms.products.store'))->assertRedirect();
    $this->get(route('cms.products.show', $product))->assertRedirect();
    $this->get(route('cms.products.edit', $product))->assertRedirect();
});

test('non-admin user is forbidden on all cms product routes', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user);

    $this->get(route('cms.products.index'))->assertForbidden();
    $this->get(route('cms.products.create'))->assertForbidden();
    $this->post(route('cms.products.store'))->assertForbidden();
    $this->get(route('cms.products.show', $product))->assertForbidden();
    $this->get(route('cms.products.edit', $product))->assertForbidden();
    $this->put(route('cms.products.update', $product))->assertForbidden();
    $this->delete(route('cms.products.destroy', $product))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Step 2 — Read (Index & Show)
|--------------------------------------------------------------------------
*/

test('admin can view product index page', function () {
    Product::factory()->count(3)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('cms.products.index'))
        ->assertOk()
        ->assertSeeText('Product CMS Management');
});

test('admin can view a single product', function () {
    $product = Product::factory()->create(['name' => 'Test Laptop']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('cms.products.show', $product))
        ->assertOk()
        ->assertSeeText('Test Laptop');
});

/*
|--------------------------------------------------------------------------
| Step 3 — Create (Validation)
|--------------------------------------------------------------------------
*/

test('store validates required fields', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [])->assertSessionHasErrors([
        'asset_tag',
        'name',
        'category_id',
        'quantity',
    ]);
});

test('store validates asset_tag is unique', function () {
    Product::factory()->create(['asset_tag' => 'ASSET-DUP-01']);
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-DUP-01',
        'name' => 'Duplicate Tag',
        'category_id' => $category->id,
        'quantity' => 1,
    ])->assertSessionHasErrors('asset_tag');
});

test('store validates category_id exists', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-NEW-01',
        'name' => 'Bad Category',
        'category_id' => 99999,
        'quantity' => 1,
    ])->assertSessionHasErrors('category_id');
});

test('store validates quantity is at least 1', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-QTY-01',
        'name' => 'Zero Qty',
        'category_id' => $category->id,
        'quantity' => 0,
    ])->assertSessionHasErrors('quantity');
});

test('store validates photo is image type and within size limit', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-PHOTO-01',
        'name' => 'Bad Photo',
        'category_id' => $category->id,
        'quantity' => 1,
        'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('photo');
});

test('store treats empty external_link as null', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-NO-LINK',
        'name' => 'No Link Product',
        'category_id' => $category->id,
        'quantity' => 1,
        'external_link' => '',
    ])->assertRedirect(route('cms.products.index'));

    $product = Product::where('asset_tag', 'ASSET-NO-LINK')->firstOrFail();

    expect($product->external_link)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Step 4 — Create (Success)
|--------------------------------------------------------------------------
*/

test('admin can create product with valid data', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-CRUD-01',
        'name' => 'Created Product',
        'description' => 'A product created via test',
        'category_id' => $category->id,
        'quantity' => 5,
        'is_active' => 1,
    ])->assertRedirect(route('cms.products.index'));

    $product = Product::where('asset_tag', 'ASSET-CRUD-01')->firstOrFail();

    expect($product->name)->toBe('Created Product')
        ->and($product->quantity)->toBe(5)
        ->and($product->is_active)->toBeTrue()
        ->and($product->category_id)->toBe($category->id);
});

test('admin can create product with photo', function () {
    Storage::fake('public');
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-PHOTO-OK',
        'name' => 'Photo Product',
        'category_id' => $category->id,
        'quantity' => 2,
        'photo' => UploadedFile::fake()->create('photo.jpg', 200, 'image/jpeg'),
    ])->assertRedirect(route('cms.products.index'));

    $product = Product::where('asset_tag', 'ASSET-PHOTO-OK')->firstOrFail();
    $media = $product->getFirstMedia('photo');

    expect($media)->not->toBeNull();
    Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
});

test('admin can create product with external_link', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-LINK-OK',
        'name' => 'Linked Product',
        'category_id' => $category->id,
        'quantity' => 1,
        'external_link' => 'https://example.com',
    ])->assertRedirect(route('cms.products.index'));

    $product = Product::where('asset_tag', 'ASSET-LINK-OK')->firstOrFail();

    expect($product->external_link)->toBe('https://example.com');
});

test('store prepends https to bare domain in external_link', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.products.store'), [
        'asset_tag' => 'ASSET-LINK-AUTO',
        'name' => 'Auto Link',
        'category_id' => $category->id,
        'quantity' => 1,
        'external_link' => 'example.com',
    ])->assertRedirect(route('cms.products.index'));

    $product = Product::where('asset_tag', 'ASSET-LINK-AUTO')->firstOrFail();

    expect($product->external_link)->toBe('https://example.com');
});

/*
|--------------------------------------------------------------------------
| Step 5 — Update (Validation)
|--------------------------------------------------------------------------
*/

test('update validates required fields', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('cms.products.update', $product), [])->assertSessionHasErrors([
        'asset_tag',
        'name',
        'category_id',
        'quantity',
    ]);
});

test('update validates asset_tag is unique excluding current product', function () {
    Product::factory()->create(['asset_tag' => 'ASSET-EXISTING']);
    $product = Product::factory()->create(['asset_tag' => 'ASSET-MYSELF']);

    $this->actingAs(User::factory()->admin()->create());

    // Saving own tag is allowed
    $this->put(route('cms.products.update', $product), [
        'asset_tag' => 'ASSET-MYSELF',
        'name' => $product->name,
        'category_id' => $product->category_id,
        'quantity' => $product->quantity,
    ])->assertRedirect();

    // Another product's tag is rejected
    $this->put(route('cms.products.update', $product), [
        'asset_tag' => 'ASSET-EXISTING',
        'name' => $product->name,
        'category_id' => $product->category_id,
        'quantity' => $product->quantity,
    ])->assertSessionHasErrors('asset_tag');
});

/*
|--------------------------------------------------------------------------
| Step 6 — Update (Success)
|--------------------------------------------------------------------------
*/

test('admin can update product fields', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Old Name',
        'quantity' => 3,
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('cms.products.update', $product), [
        'asset_tag' => $product->asset_tag,
        'name' => 'New Name',
        'description' => 'Updated desc',
        'category_id' => $category->id,
        'quantity' => 10,
        'is_active' => 0,
    ])->assertRedirect(route('cms.products.show', $product));

    $product->refresh();

    expect($product->name)->toBe('New Name')
        ->and($product->quantity)->toBe(10)
        ->and($product->is_active)->toBeFalse();
});

test('admin can replace product photo', function () {
    Storage::fake('public');
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('cms.products.update', $product), [
        'asset_tag' => $product->asset_tag,
        'name' => $product->name,
        'category_id' => Category::factory()->create()->id,
        'quantity' => $product->quantity,
        'photo' => UploadedFile::fake()->create('new-photo.jpg', 200, 'image/jpeg'),
    ])->assertRedirect(route('cms.products.show', $product));

    $media = $product->fresh()->getFirstMedia('photo');

    expect($media)->not->toBeNull();
    Storage::disk('public')->assertExists($media->id.'/'.$media->file_name);
});

/*
|--------------------------------------------------------------------------
| Step 7 — Delete
|--------------------------------------------------------------------------
*/

test('admin can soft delete a product', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->admin()->create());

    $this->delete(route('cms.products.destroy', $product))
        ->assertRedirect(route('cms.products.index'));

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('soft deleted product still exists in database', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->admin()->create());
    $this->delete(route('cms.products.destroy', $product));

    expect(Product::withTrashed()->whereKey($product->id)->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Step 8 — Category Store
|--------------------------------------------------------------------------
*/

test('admin can create a new category', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.categories.store'), ['name' => 'Monitors'])
        ->assertRedirect();

    Category::where('name', 'Monitors')->firstOrFail();
});

test('category name must be unique', function () {
    Category::factory()->create(['name' => 'Laptops']);

    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.categories.store'), ['name' => 'Laptops'])
        ->assertSessionHasErrors('name');
});

test('category name is required', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('cms.categories.store'))->assertSessionHasErrors('name');
});
