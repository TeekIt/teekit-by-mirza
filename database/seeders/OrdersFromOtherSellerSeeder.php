<?php

namespace Database\Seeders;

use App\Models\OrdersFromOtherSeller;
use Illuminate\Database\Seeder;

class OrdersFromOtherSellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrdersFromOtherSeller::factory()->count(5)->create();
    }
}
