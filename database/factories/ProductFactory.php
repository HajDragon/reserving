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
        return [
            'asset_tag' => strtoupper(fake()->bothify('ASSET-####??')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(['laptop', 'projector', 'tablet', 'camera']),
            'quantity' => fake()->numberBetween(1, 20),
            'is_active' => true,
            'photo_path' => fake()->optional()->imageUrl(),
        ];
    }
}
