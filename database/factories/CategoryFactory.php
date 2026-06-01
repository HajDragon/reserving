<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Camera',
            'Laptop',
            'Projector',
            'Microphone',
            'Tablet',
            'Speaker',
            'Cables',
            'Accessories',
        ]) . ' ' . $this->faker->unique()->word() . ' ' . $this->faker->numberBetween(1, 10000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
