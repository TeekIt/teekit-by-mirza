<?php

namespace Database\Seeders;

use App\Models\GophrDelivery;
use Illuminate\Database\Seeder;

class GophrDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GophrDelivery::factory()->count(10)->create();
    }
}
