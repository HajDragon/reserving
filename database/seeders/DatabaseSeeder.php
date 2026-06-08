<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Cameras'],
            ['name' => 'Laptops'],
            ['name' => 'Projectors'],
            ['name' => 'Microphones'],
            ['name' => 'Tablets'],
            ['name' => 'Speakers'],
            ['name' => 'Cables'],
            ['name' => 'Accessories'],
        ])->map(fn($category) => \App\Models\Category::firstOrCreate($category));

        $this->call([
            productSeeder::class,
        ]);
        
        // Ensure all products have one of the real categories
        \App\Models\Product::whereNull('category_id')->get()->each(function ($product) use ($categories) {
            $product->update(['category_id' => $categories->random()->id]);
        });

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }
}
