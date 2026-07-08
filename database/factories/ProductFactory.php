<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageId = fake()->numberBetween(1, 1000);
        $quantity = fake()->numberBetween(1, 20);

        return [

            'asset_tag' => strtoupper(fake()->bothify('ASSET-####??')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'category_id' => null,
            'quantity' => $quantity,
            'is_active' => true,
        ];
    }

    public function configure()
    {
        return $this
            ->afterMaking(function (Product $product) {
                // Sync available_quantity with quantity when not explicitly provided.
                $product->available_quantity = $product->available_quantity ?? $product->quantity;
            })
            ->afterCreating(function (Product $product) {
                // To avoid long seeding times and test failures, we can mock or skip downloading
                // $product->addMediaFromUrl("https://picsum.photos/640/480?random={$imageId}")->toMediaCollection('photo');
            });
    }
}
