<?php

namespace Database\Factories;

use App\Models\Category;
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
        $quantity = fake()->numberBetween(1, 10);

        return [
            'asset_tag' => strtoupper(fake()->unique()->bothify('ASSET-####??')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'category_id' => null,
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'is_active' => true,
            'photo_path' => null,
            'external_link' => null,
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'available_quantity' => 0,
        ]);
    }

    /**
     * Add a random placeholder image link to the photo_path column.
     */
    public function withImage(): static
    {
        return $this->state(function (array $attributes) {
            $imageId = fake()->numberBetween(1, 1000);
            return [
                'photo_path' => "https://picsum.photos/640/480?random={$imageId}",
            ];
        });
    }
}
