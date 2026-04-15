<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
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
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->modify('+2 hours'),
            'status' => fake()->randomElement(['confirmed', 'active', 'completed', 'cancelled']),
        ];
    }
}
