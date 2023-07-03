<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // check if product are already present in database
        if (Product::count() > 0) {
            return;
        }

        // create 10 products
        Product::factory()->count(10)->create();
    }
}
