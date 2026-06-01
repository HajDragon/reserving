<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class productSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Product::factory(60)->create();

    }
}
