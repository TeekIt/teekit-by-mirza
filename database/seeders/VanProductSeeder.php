<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VanProduct;

class VanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VanProduct::factory()->count(1000)->create();
    }
}