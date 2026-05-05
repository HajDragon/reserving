<?php

use App\Livewire\Pages\Admin\ProductIndex as AdminProductIndex;
use App\Livewire\Pages\ProductIndex;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('user product livewire page appends products when load more is called', function () {
    $user = User::factory()->create();

    Collection::times(20, function (int $index) {
        Product::factory()->create([
            'name' => sprintf('Camera %02d', $index),
            'asset_tag' => sprintf('CAM-%03d', $index),
            'is_active' => true,
            'available_quantity' => 5,
        ]);
    });

    $this->actingAs($user);

    Livewire::test(ProductIndex::class)
        ->assertCount('products', 9)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertCount('products', 18)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertCount('products', 20)
        ->assertSet('hasMore', false);
});

test('user product livewire page resets loaded products when search changes', function () {
    $user = User::factory()->create();

    Product::factory()->create([
        'name' => 'Cinema Camera Kit',
        'asset_tag' => 'CAM-100',
        'is_active' => true,
        'available_quantity' => 5,
    ]);

    Collection::times(15, function (int $index) {
        Product::factory()->create([
            'name' => sprintf('General Product %02d', $index),
            'asset_tag' => sprintf('GEN-%03d', $index),
            'is_active' => true,
            'available_quantity' => 5,
        ]);
    });

    $this->actingAs($user);

    Livewire::test(ProductIndex::class)
        ->assertCount('products', 9)
        ->call('loadMore')
        ->assertCount('products', 16)
        ->set('search', 'Cinema')
        ->assertCount('products', 1)
        ->assertSet('products.0.name', 'Cinema Camera Kit')
        ->assertSet('hasMore', false);
});

test('admin product livewire page appends products when load more is called', function () {
    $admin = User::factory()->admin()->create();

    Collection::times(16, function (int $index) {
        Product::factory()->create([
            'name' => sprintf('Managed Product %02d', $index),
            'asset_tag' => sprintf('MNG-%03d', $index),
            'is_active' => true,
            'quantity' => 5,
        ]);
    });

    $this->actingAs($admin);

    Livewire::test(AdminProductIndex::class)
        ->assertCount('products', 12)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertCount('products', 16)
        ->assertSet('hasMore', false);
});
