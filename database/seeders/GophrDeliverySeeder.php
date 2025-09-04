<?php

namespace Database\Seeders;

use App\Models\GophrDelivery;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GophrDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GophrDelivery::factory()->count(10)->create();
    }
}
