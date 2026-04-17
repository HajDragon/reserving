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
            'type' => fake()->randomElement(['laptop', 'projector', 'tablet', 'camera']),
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'is_active' => true,
            'photo_path' => "https://picsum.photos/640/480?random={$imageId}",
        ];
    }
}
