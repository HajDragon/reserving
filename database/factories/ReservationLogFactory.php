<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\ReservationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationLog>
 */
class ReservationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-2 weeks', '-1 day');

        return [
            'reservation_id' => null,
            'reservation_order_id' => null,
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'reserved_quantity' => fake()->numberBetween(1, 4),
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->modify('+2 hours'),
            'extra_wishes' => fake()->optional(0.6)->sentence(),
            'status' => ReservationStatus::Returned,
            'returned_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'returned_by' => User::factory(),
        ];
    }
}
