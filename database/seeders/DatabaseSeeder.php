<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            QtySeeder::class,
            ReferralCodeRelationSeeder::class,
            DriverSeeder::class,
            DriverDocumentsSeeder::class,
            OrdersFromOtherSellerSeeder::class,
            OrderItemsSeeder::class,
            GophrDeliverySeeder::class,
        ]);
    }
}
