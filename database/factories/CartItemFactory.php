<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('+1 day', '+1 week');

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->modify('+2 hours'),
            'requested_quantity' => fake()->numberBetween(1, 3),
            'extra_wishes' => fake()->optional(0.7)->sentence(),
        ];
    }
}

