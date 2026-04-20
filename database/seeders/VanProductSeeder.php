<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VanProduct;

class VanProductSeeder extends Seeder
{
    /**
     * Seed the van_products table with sample data
     */
    public function run(): void
    {
        VanProduct::factory()->count(20)->create();
    }
}