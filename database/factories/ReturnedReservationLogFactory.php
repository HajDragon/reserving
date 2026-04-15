<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReturnedReservationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnedReservationLog>
 */
class ReturnedReservationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $returnedAt = fake()->dateTimeBetween('-2 weeks', 'now');

        return [
            'reservation_id' => Reservation::factory(),
            'reservation_order_id' => null,
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'product_name' => fake()->words(3, true),
            'quantity' => fake()->numberBetween(1, 4),
            'returned_at' => $returnedAt,
            'reservation_start_time' => (clone $returnedAt)->modify('-2 hours'),
            'reservation_end_time' => $returnedAt,
            'extra_wishes' => fake()->optional(0.7)->sentence(),
        ];
    }
}
